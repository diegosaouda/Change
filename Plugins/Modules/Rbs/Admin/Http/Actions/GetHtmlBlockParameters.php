<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Admin\Http\Actions;

use Change\Http\Event;
use Zend\Http\Response as HttpResponse;

/**
 * @name \Rbs\Admin\Http\Actions\GetHtmlBlockParameters
 */
class GetHtmlBlockParameters
{
	/**
	 * Use Required Event Params: vendor, shortModuleName, shortBlockName
	 * @param Event $event
	 */
	public function execute($event)
	{
		$result = new \Rbs\Admin\Http\Result\Renderer();
		$vendor = $event->getParam('vendor');
		$shortModuleName = $event->getParam('shortModuleName');
		$shortBlockName = $event->getParam('shortBlockName');

		$blockName = $vendor. '_' . $shortModuleName . '_' . $shortBlockName;
		$information = $event->getApplicationServices()->getBlockManager()->getBlockInformation($blockName);

		if ($information instanceof \Change\Presentation\Blocks\Information)
		{
			$plugin = $event->getApplicationServices()->getPluginManager()->getModule($vendor, $shortModuleName);
			if ($plugin && $plugin->isAvailable())
			{
				$workspace =  $event->getApplication()->getWorkspace();
				$filePath = $workspace->composePath($plugin->getAssetsPath(), 'Admin', 'Blocks', $shortBlockName . '.twig');
				if (!is_readable($filePath))
				{
					$filePath = $workspace->pluginsModulesPath('Rbs', 'Admin', 'Assets', 'block-parameters.twig');
				}

				$result->setHttpStatusCode(HttpResponse::STATUS_CODE_200);
				/* @var $manager \Rbs\Admin\Manager */
				$manager = $event->getParam('manager');
				$attributes = array('information' => $information);
				$renderer = function () use ($filePath, $manager, $attributes)
				{
					return $manager->renderTemplateFile($filePath, $attributes);
				};
				$result->setRenderer($renderer);
				$event->setResult($result);
				return;
			}
		}

		$result->setHttpStatusCode(HttpResponse::STATUS_CODE_404);
		$result->setRenderer(function ()
		{
			return null;
		});
		$event->setResult($result);
	}
}