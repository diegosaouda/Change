(function ($) {

	"use strict";

	var	app = angular.module('RbsChange'),

		MAX_WIDTH  = 100,
		MAX_HEIGHT = 56;

	app.constant('rbsThumbnailSizes', {
		'xs' : '57x32',
		's'  : '100x56',
		'm'  : '177x100',
		'l'  : '267x150',
		'xl' : '356x200'
	});

	/**
	 * @example: <code><img rbs-storage-image="myMedia.path" thumbnail="xs"/></code>
	 */
	app.directive('rbsStorageImage', ['RbsChange.REST', 'rbsThumbnailSizes', function (REST, sizes)
	{
		return {
			restrict : 'A',
			scope : {
				rbsStorageImage : "="
			},

			link : function (scope, elm, attrs)
			{
				var	$el = $(elm),
					maxWidth = MAX_WIDTH, maxHeight = MAX_HEIGHT,
					dim;

				// Check if the directive is on an valid tag (<img/> only).
				if (!$el.is('img')) {
					throw new Error("Directive 'rbs-storage-image' must be used on <img/> elements only.");
				}

				scope.$watch('rbsStorageImage', function (value)
				{
					var width = parseInt(dim[0], 10);
					var height = parseInt(dim[1], 10);
					if (value) {
						if (/^\d+$/.test(value+'')) {
							REST.resource(parseInt(value, 10)).then(function (image) {
								elm.attr('src', image.META$.actions['resizeurl'].href + '?maxWidth=' + width + '&maxHeight=' + height);
								//elm.show();
							});
						}
						else if (angular.isObject(value) && value.META$ && value.META$.actions && value.META$.actions['resizeurl']) {
							elm.attr('src', value.META$.actions['resizeurl'].href + '?maxWidth=' + width + '&maxHeight=' + height);
							//elm.show();
						}
						else if (angular.isString(value)) {
							elm.attr('src', value + '?maxWidth=' + width + '&maxHeight=' + height);
							//elm.show();
						}
						else {
							elm.remove();
						}
					}
					else if (angular.isDefined()) {
						elm.remove();
					}
					else {
						elm.hide();
					}
				});

				if (attrs.thumbnail)
				{
					attrs.thumbnail = angular.lowercase(attrs.thumbnail);
					if (sizes.hasOwnProperty(attrs.thumbnail)) {
						attrs.thumbnail = sizes[attrs.thumbnail];
					}
					if (/^\d+x\d+$/.test(attrs.thumbnail)) {
						dim = attrs.thumbnail.split('x');
						maxWidth = parseInt(dim[0], 10);
						maxHeight = parseInt(dim[1], 10);
					}
					$el.css({
						'max-width'  : maxWidth+'px',
						'max-height' : maxHeight+'px'
					});
				}
				else {
					throw new Error("Attribute 'thumbnail' is required for Directive 'rbs-storage-image'.");
				}

			}
		};
	}]);

})(window.jQuery);