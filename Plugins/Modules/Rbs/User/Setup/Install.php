<?php
namespace Rbs\User\Setup;

/**
 * @name \Rbs\User\Setup\Install
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
		$config->addPersistentEntry('Change/Events/BlockManager/Rbs_User', '\\Rbs\\User\\Blocks\\ListenerAggregate');

		$config->addPersistentEntry('Change/Events/Rbs/Admin/Rbs_User', '\\Rbs\\User\\Admin\\Register');


		$config->addPersistentEntry('Change/Events/AuthenticationManager/Rbs_User', '\\Rbs\\User\\Events\\ListenerAggregate');
		$config->addPersistentEntry('Change/Events/Http/Web/Rbs_User', '\\Rbs\\User\\Web\\ListenerAggregate');
		$config->addPersistentEntry('Change/Events/Http/Rest/Rbs_User', '\\Rbs\\User\\Http\\Rest\\ListenerAggregate');

		$config->addPersistentEntry('Change/Events/ProfileManager/Rbs_User', '\\Rbs\\User\\Profile\\ListenerAggregate');
	}

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 * @param \Change\Application\ApplicationServices $applicationServices
	 * @param \Change\Documents\DocumentServices $documentServices
	 * @param \Change\Presentation\PresentationServices $presentationServices
	 * @throws \Exception
	 */
	public function executeServices($plugin, $applicationServices, $documentServices, $presentationServices)
	{
		$presentationServices->getThemeManager()->installPluginTemplates($plugin);

		$query = new \Change\Documents\Query\Query($documentServices, 'Rbs_User_User');
		$user = $query->andPredicates($query->eq('login', 'admin'))->getFirstDocument();
		if (!$user)
		{
			$transactionManager = $applicationServices->getTransactionManager();
			try
			{
				$transactionManager->begin();

				$groupModel = $documentServices->getModelManager()->getModelByName('Rbs_User_Group');

				/* @var $group \Rbs\User\Documents\Group */
				$group = $documentServices->getDocumentManager()->getNewDocumentInstanceByModel($groupModel);
				$group->setLabel('Backoffice');
				$group->setRealm('Rbs_Admin');
				$group->create();

				/* @var $group2 \Rbs\User\Documents\Group */
				$group2 = $documentServices->getDocumentManager()->getNewDocumentInstanceByModel($groupModel);
				$group2->setLabel('Site Web');
				$group2->setRealm('web');
				$group2->create();

				/* @var $user \Rbs\User\Documents\User */
				$user = $documentServices->getDocumentManager()->getNewDocumentInstanceByModelName('Rbs_User_User');
				$user->setLabel('Administrator');
				$user->setEmail('admin@temporary.fr');
				$user->setLogin('admin');
				$user->setPassword('admin');
				$user->setActive(true);
				$user->setGroups(array($group, $group2));
				$user->create();

				$transactionManager->commit();
			}
			catch (\Exception $e)
			{
				throw $transactionManager->rollBack($e);
			}
		}

		$pm = new \Change\Permissions\PermissionsManager();
		$pm->setApplicationServices($applicationServices);
		if (!$pm->hasRule($user->getId()))
		{
			$pm->addRule($user->getId());
		}
	}

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 */
	public function finalize($plugin)
	{
		$plugin->setConfigurationEntry('locked', true);
	}
}