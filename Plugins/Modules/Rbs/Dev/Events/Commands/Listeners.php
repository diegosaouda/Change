<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Dev\Events\Commands;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Json\Json;

/**
 * @name \Rbs\Dev\Events\Commands\Listeners
 */
class Listeners implements ListenerAggregateInterface
{

	/**
	 * Attach one or more listeners
	 * Implementors may add an optional $priority argument; the EventManager
	 * implementation will pass this to the aggregate.
	 * @param EventManagerInterface $events
	 * @return void
	 */
	public function attach(EventManagerInterface $events)
	{
		$callback = function (\Change\Commands\Events\Event $event)
		{
			$commandConfigPath = __DIR__ . '/Assets/config.json';
			if (is_file($commandConfigPath))
			{
				return Json::decode(file_get_contents($commandConfigPath), Json::TYPE_ARRAY);
			}
		};
		$events->attach('config', $callback);

		$callback = function ($event)
		{
			$cmd = new \Rbs\Dev\Commands\InitializeModel();
			$cmd->execute($event);
		};
		$events->attach('rbs_dev:initialize-model', $callback);

		$callback = function ($event)
		{
			$cmd = new \Rbs\Dev\Commands\InitializePlugin();
			$cmd->execute($event);
		};
		$events->attach('rbs_dev:initialize-plugin', $callback);

		$callback = function ($event)
		{
			$cmd = new \Rbs\Dev\Commands\CreateCommand();
			$cmd->execute($event);
		};
		$events->attach('rbs_dev:create-command', $callback);

		$callback = function ($event)
		{
			$cmd = new \Rbs\Dev\Commands\AdminRoutes();
			$cmd->execute($event);
		};
		$events->attach('rbs_dev:admin-routes', $callback);
	}

	/**
	 * Detach all previously attached listeners
	 * @param EventManagerInterface $events
	 * @return void
	 */
	public function detach(EventManagerInterface $events)
	{
		// TODO: Implement detach() method.
	}
}