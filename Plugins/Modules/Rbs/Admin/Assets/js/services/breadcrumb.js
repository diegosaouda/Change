(function () {
	"use strict";

	var app = angular.module('RbsChange');

	app.provider('RbsChange.Breadcrumb', function RbsChangeBreadcrumbProvider() {

		this.$get = [
			'$rootScope', '$document', '$location', '$q', 'RbsChange.ArrayUtils',
			'RbsChange.Utils', 'RbsChange.REST', 'RbsChange.i18n', '$route', 'RbsChange.Navigation',

			function ($rootScope, $document, $location, $q, ArrayUtils, Utils, REST, i18n, $route, Navigation) {

				var entriesArray = [],
					current = null,
					home = null;

				function goParent() {
					if (entriesArray.length) {
						var entry = entriesArray[entriesArray.length - 1];
						$location.url(entry.url());
					} else {
						$location.url(home.url());
					}
				}

				function getCurrentNodeId() {
					return $location.search().tn;
				}

				function currentEntry() {
					return current;
				}

				function homeEntry() {
					return home;
				}

				function pathEntries() {
					return entriesArray.length ? entriesArray : null;
				}


				function updateItems(event, currentRoute) {
					if (!home) {
						home = findRoute('/');
					}
					var currentPath = $location.path(),
						parts =  currentPath.split('/'), part,
						partialPath = '/',
						entry, redirectRoute;

					if (currentPath != '/') {
						current = findRoute(currentPath);
						if (current) {
							resolveLabel(current);
						}
					} else {
						current = null;
					}

					entriesArray.length = 0;
					for (var i = 0; i < parts.length; i++) {
						part = parts[i];
						if (part.length) {
							partialPath += part;
							entry = findRoute(partialPath);
							if (entry) {
								if ((!current || entry.route !== current.route)) {
									if (!entry.route.redirectTo || entry.label) {
										resolveLabel(entry);
										entriesArray.push(entry);
									}
								}
							}
							partialPath += '/';
						}
					}

					$rootScope.$broadcast('Change:UpdateBreadcrumb', entriesArray, current);

					updatePageTitle();

					$rootScope.$broadcast('Change:BreadcrumbUpdated');
				}

				function updatePageTitle() {
					var title = 'Rbs Change';
					angular.forEach(entriesArray, function(entry) {
						if (entry.label) {
							title += ' / ' + entry.label;
						}
					});
					if (current && current.label)
					{
						title += ' / ' + current.label;
					}

					$document[0].title = title;
				}

				function resolveLabel(entry) {
					var route = entry.route;
					if (route.ruleName === 'form' && entry.params.hasOwnProperty('id')) {
						REST.resources([entry.params.id]).then(function(collection) {
							if (collection.resources.length) {
								entry.label = collection.resources[0].label;
								updatePageTitle();
							}
						}, function () {entry.label = 'Not Found'})
					}
				}

				function findRoute(searchPath) {
					var params, entry = null;
					angular.forEach($route.routes, function(route) {
						if (!entry && (params = switchRouteMatcher(searchPath, route))) {
							entry = {label: null, route: route, path: searchPath, params: params,
								url : function() {return Navigation.addTargetContext(this.path.substring(1))}};
							if (route.redirectTo == (route.originalPath + '/')) {
								var redirectRoute = $route.routes[route.redirectTo];
								if (redirectRoute) {
									entry.route = route = redirectRoute;
									entry.path = searchPath + '/';
								}
							}

							if (route.hasOwnProperty('labelKey')) {
								entry.label = i18n.trans(route.labelKey);
							}
						}
					});
					return entry;
				}


				/**
				 * @param on {string} current url
				 * @param route {Object} route regexp to match the url against
				 * @return {?Object}
				 *
				 * @description
				 * Check if the route matches the current url.
				 *
				 * Inspired by match in
				 * visionmedia/express/lib/router/router.js.
				 */
				function switchRouteMatcher(on, route) {
					var keys = route.keys,
						params = {};

					if (!route.regexp) return null;

					var m = route.regexp.exec(on);
					if (!m) return null;

					for (var i = 1, len = m.length; i < len; ++i) {
						var key = keys[i - 1];

						var val = 'string' == typeof m[i]
							? decodeURIComponent(m[i])
							: m[i];

						if (key && val) {
							params[key.name] = val;
						}
					}
					return params;
				}

				$rootScope.$on('$routeChangeSuccess', updateItems);

				return  {
					goParent : goParent,
					getCurrentNodeId : getCurrentNodeId,
					homeEntry: homeEntry,
					pathEntries : pathEntries,
					currentEntry : currentEntry,
					getEntryByPath: function(searchPath) {
						if (angular.isString(searchPath) && searchPath.length) {
							var li = searchPath.length -1;
							if (searchPath === '/') {
								return findRoute(searchPath)
							} else if (searchPath.lastIndexOf('/') == li) {
								return findRoute(searchPath.substring(0, li));
							} else {
								return findRoute(searchPath);
							}
						}
						return null;
					},
					refreshPageTitle: updatePageTitle
				};
			}
		];
	});
})();