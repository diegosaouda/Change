<?php
namespace Rbs\Timeline\Setup;

/**
 * @name \Rbs\Timeline\Setup\Install
 */
class Install
{

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 * @param \Change\Application $application
	 * @throws \RuntimeException
	 */
	public function executeApplication($plugin, $application)
	{
		/* @var $config \Change\Configuration\EditableConfiguration */
		$config = $application->getConfiguration();

		$config->addPersistentEntry('Change/Events/Rbs/Admin/Rbs_Timeline',
			'\\Rbs\\Timeline\\Admin\\Register');
		$config->addPersistentEntry('Change/Events/Http/Rest/Rbs_Timeline',
			'\\Rbs\\Timeline\\Http\\Rest\\ListenerAggregate');
	}

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 */
	public function finalize($plugin)
	{
		$plugin->setConfigurationEntry('locked', true);
	}
}