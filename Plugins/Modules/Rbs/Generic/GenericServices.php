<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Generic;

use Change\Application;
use Change\Services\ApplicationServices;

/**
 * @name \Rbs\Generic\GenericServices
 */
class GenericServices extends \Zend\Di\Di
{
	use \Change\Services\ServicesCapableTrait;

	/**
	 * @var \Change\Services\ApplicationServices
	 */
	protected $applicationServices;

	/**
	 * @param \Change\Services\ApplicationServices $applicationServices
	 * @return $this
	 */
	public function setApplicationServices(\Change\Services\ApplicationServices $applicationServices)
	{
		$this->applicationServices = $applicationServices;
		return $this;
	}

	/**
	 * @return \Change\Services\ApplicationServices
	 */
	protected function getApplicationServices()
	{
		return $this->applicationServices;
	}

	/**
	 * @return array<alias => className>
	 */
	protected function loadInjectionClasses()
	{
		$classes = $this->getApplication()->getConfiguration('Rbs/Generic/Services');
		return is_array($classes) ? $classes : array();
	}

	/**
	 * @param Application $application
	 * @param ApplicationServices $applicationServices
	 */
	public function __construct(Application $application, ApplicationServices $applicationServices)
	{
		$this->setApplication($application);
		$this->setApplicationServices($applicationServices);

		$definitionList = new \Zend\Di\DefinitionList(array());

		//SeoManager : Application, DocumentManager, TransactionManager
		$seoManagerClassName = $this->getInjectedClassName('SeoManager', 'Rbs\Seo\SeoManager');
		$classDefinition = $this->getClassDefinition($seoManagerClassName);
		$this->addApplicationClassDefinition($classDefinition);
		$classDefinition
			->addMethod('setTransactionManager', true)
				->addMethodParameter('setTransactionManager', 'transactionManager', array('required' => true))
			->addMethod('setDocumentManager', true)
				->addMethodParameter('setDocumentManager', 'documentManager', array('required' => true));
		$definitionList->addDefinition($classDefinition);

		//AvatarManager : Application
		$avatarManagerClassName = $this->getInjectedClassName('AvatarManager', 'Rbs\Media\Avatar\AvatarManager');
		$classDefinition = $this->getClassDefinition($avatarManagerClassName);
		$this->addApplicationClassDefinition($classDefinition);
		$definitionList->addDefinition($classDefinition);

		//FieldManager : Application, ConstraintsManager
		$fieldManagerClassName = $this->getInjectedClassName('FieldManager', 'Rbs\Simpleform\Field\FieldManager');
		$classDefinition = $this->getClassDefinition($fieldManagerClassName);
		$this->addApplicationClassDefinition($classDefinition);
		$classDefinition->addMethod('setConstraintsManager', true)
			->addMethodParameter('setConstraintsManager', 'constraintsManager', array('required' => true));
		$definitionList->addDefinition($classDefinition);

		//SecurityManager : Application
		$securityManagerClassName = $this->getInjectedClassName('SecurityManager', 'Rbs\Simpleform\Security\SecurityManager');
		$classDefinition = $this->getClassDefinition($securityManagerClassName);
		$this->addApplicationClassDefinition($classDefinition);
		$definitionList->addDefinition($classDefinition);

		//GeoManager : Application
		$geoManagerClassName = $this->getInjectedClassName('GeoManager', 'Rbs\Geo\GeoManager');
		$classDefinition = $this->getClassDefinition($geoManagerClassName);
		$this->addApplicationClassDefinition($classDefinition);
		$definitionList->addDefinition($classDefinition);

		//FacetManager : Application, DocumentManager, I18nManager, CollectionManager
		$facetManagerClassName = $this->getInjectedClassName('FacetManager', '\Rbs\Elasticsearch\Facet\FacetManager');
		$classDefinition = $this->getClassDefinition($facetManagerClassName);
		$this->addApplicationClassDefinition($classDefinition);
		$classDefinition
			->addMethod('setDocumentManager', true)
			->addMethodParameter('setDocumentManager', 'documentManager', array('required' => true))
			->addMethod('setI18nManager', true)
			->addMethodParameter('setI18nManager', 'i18nManager', array('required' => true))
			->addMethod('setCollectionManager', true)
			->addMethodParameter('setCollectionManager', 'collectionManager', array('required' => true));
		$definitionList->addDefinition($classDefinition);

		//IndexManager : FacetManager, Application, DocumentManager
		$indexManagerClassName = $this->getInjectedClassName('IndexManager', 'Rbs\Elasticsearch\Index\IndexManager');
		$classDefinition = $this->getClassDefinition($indexManagerClassName);
		$this->addApplicationClassDefinition($classDefinition);
		$classDefinition
			->addMethod('setFacetManager', true)
			->addMethodParameter('setFacetManager', 'facetManager', array('type' => 'FacetManager', 'required' => true))
			->addMethod('setDocumentManager', true)
			->addMethodParameter('setDocumentManager', 'documentManager', array('required' => true));
		$definitionList->addDefinition($classDefinition);

		//MailManager : Application, DocumentManager, JobManager
		$mailManagerClassName = $this->getInjectedClassName('MailManager', 'Rbs\Mail\MailManager');
		$classDefinition = $this->getClassDefinition($mailManagerClassName);
		$this->addApplicationClassDefinition($classDefinition);
		$classDefinition
			->addMethod('setJobManager', true)
			->addMethodParameter('setJobManager', 'jobManager', array('required' => true))
			->addMethod('setDocumentManager', true)
			->addMethodParameter('setDocumentManager', 'documentManager', array('required' => true));
		$definitionList->addDefinition($classDefinition);

		parent::__construct($definitionList);
		$im = $this->instanceManager();

		$transactionManager = function() use ($applicationServices) {return $applicationServices->getTransactionManager();};
		$documentManager = function() use ($applicationServices) {return $applicationServices->getDocumentManager();};
		$i18nManager = function() use ($applicationServices) {return $applicationServices->getI18nManager();};
		$collectionManager = function() use ($applicationServices) {return $applicationServices->getCollectionManager();};
		$jobManager = function() use ($applicationServices) {return $applicationServices->getJobManager();};

		$im->addAlias('SeoManager', $seoManagerClassName,
			array('application' => $application,
				'documentManager' => $documentManager, 'transactionManager' => $transactionManager));

		$im->addAlias('AvatarManager', $avatarManagerClassName,  array('application' => $application));

		$constraintsManager = function () use ($applicationServices)
		{
			return $applicationServices->getConstraintsManager();
		};
		$im->addAlias('FieldManager', $fieldManagerClassName,
			array('application' => $application, 'constraintsManager' => $constraintsManager
		));

		$im->addAlias('SecurityManager', $securityManagerClassName, array('application' => $application));

		$im->addAlias('GeoManager', $geoManagerClassName, array('application' => $application));

		$im->addAlias('FacetManager', $facetManagerClassName,
			array('application' => $application, 'documentManager' => $documentManager,
				'collectionManager' => $collectionManager, 'i18nManager' => $i18nManager));

		$im->addAlias('IndexManager', $indexManagerClassName,
			array('application' => $application, 'documentManager' => $documentManager));

		$im->addAlias('MailManager', $mailManagerClassName,
			array('application' => $application, 'documentManager' => $documentManager, 'jobManager' => $jobManager));
	}

	/**
	 * @api
	 * @return \Rbs\Seo\SeoManager
	 */
	public function getSeoManager()
	{
		return $this->get('SeoManager');
	}

	/**
	 * @api
	 * @return \Rbs\Media\Avatar\AvatarManager
	 */
	public function getAvatarManager()
	{
		return $this->get('AvatarManager');
	}

	/**
	 * @api
	 * @return \Rbs\Simpleform\Field\FieldManager
	 */
	public function getFieldManager()
	{
		return $this->get('FieldManager');
	}

	/**
	 * @api
	 * @return \Rbs\Simpleform\Security\SecurityManager
	 */
	public function getSecurityManager()
	{
		return $this->get('SecurityManager');
	}

	/**
	 * @api
	 * @return \Rbs\Elasticsearch\Index\IndexManager
	 */
	public function getIndexManager()
	{
		return $this->get('IndexManager');
	}

	/**
	 * @api
	 * @return \Rbs\Elasticsearch\Facet\FacetManager
	 */
	public function getFacetManager()
	{
		return $this->get('FacetManager');
	}

	/**
	 * @api
	 * @return \Rbs\Geo\GeoManager
	 */
	public function getGeoManager()
	{
		return $this->get('GeoManager');
	}

	/**
	 * @api
	 * @return \Rbs\Mail\MailManager
	 */
	public function getMailManager()
	{
		return $this->get('MailManager');
	}
}