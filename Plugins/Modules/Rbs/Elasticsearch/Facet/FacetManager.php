<?php
namespace Rbs\Elasticsearch\Facet;


/**
 * @name \Rbs\Elasticsearch\Facet\FacetManager
 */
class FacetManager implements \Zend\EventManager\EventsCapableInterface
{
	use \Change\Events\EventsCapableTrait, \Change\Services\DefaultServicesTrait {
		\Change\Events\EventsCapableTrait::attachEvents as defaultAttachEvents;
	}

	const EVENT_MANAGER_IDENTIFIER = 'Rbs_Elasticsearch_FacetManager';

	/**
	 * @var \Change\Collection\CollectionManager
	 */
	protected $collectionManager;

	/**
	 * @var array
	 */
	protected $collections = array();

	/**
	 * @return \Change\Events\SharedEventManager
	 */
	public function getSharedEventManager()
	{
		if ($this->sharedEventManager === null)
		{
			$this->sharedEventManager = $this->getApplication()->getSharedEventManager();
		}
		return $this->sharedEventManager;
	}

	/**
	 * @return null|string|string[]
	 */
	protected function getEventManagerIdentifier()
	{
		return static::EVENT_MANAGER_IDENTIFIER;
	}

	/**
	 * @return string[]
	 */
	protected function getListenerAggregateClassNames()
	{
		if ($this->applicationServices)
		{
			$config = $this->applicationServices->getApplication()->getConfiguration();
			return $config->getEntry('Rbs/Elasticsearch/Events/FacetManager', array());
		}
		return array();
	}

	/**
	 * @param \Zend\EventManager\EventManager $eventManager
	 */
	protected function attachEvents(\Zend\EventManager\EventManager $eventManager)
	{
		$this->defaultAttachEvents($eventManager);
		$eventManager->attach('getIndexerValue', array($this, 'onDefaultGetIndexerValue'), 5);
		$eventManager->attach('getFacetMapping', array($this, 'onDefaultGetFacetMapping'), 5);

	}

	/**
	 * @param \Change\Collection\CollectionManager $collectionManager
	 * @return $this
	 */
	public function setCollectionManager($collectionManager)
	{
		$this->collectionManager = $collectionManager;
		return $this;
	}

	/**
	 * @throws \RuntimeException
	 * @return \Change\Collection\CollectionManager
	 */
	public function getCollectionManager()
	{
		if ($this->collectionManager === null)
		{
			throw new \RuntimeException('CollectionManager not set.', 999999);
		}
		return $this->collectionManager;
	}

	/**
	 * @param string $collectionCode
	 * @return \Change\Collection\CollectionInterface|null
	 */
	protected function getCollectionByCode($collectionCode)
	{
		if (!$collectionCode)
		{
			return null;
		}
		if (!array_key_exists($collectionCode, $this->collections))
		{
			$this->collections[$collectionCode] = $this->getCollectionManager()->getCollection($collectionCode);
		}
		return $this->collections[$collectionCode];
	}

	/**
	 * @param \Rbs\Elasticsearch\Index\IndexDefinitionInterface $indexDefinition
	 * @param \Change\Documents\AbstractDocument $document
	 * @return array<fieldName => value>
	 */
	public function getIndexerValues(\Rbs\Elasticsearch\Index\IndexDefinitionInterface $indexDefinition, \Change\Documents\AbstractDocument $document)
	{
		$values = array();
		if ($document instanceof \Change\Documents\AbstractDocument)
		{
			foreach($indexDefinition->getFacetsDefinition() as $facet)
			{
				if ($facet instanceof FacetDefinitionInterface)
				{
					$value = $this->getIndexerValue($facet, $indexDefinition, $document, $values);
					if (is_array($value))
					{
						$values = array_merge($values, $value);
					}
				}
			}
		}
		return $values;
	}

	/**
	 * @param \Rbs\Elasticsearch\Index\IndexDefinitionInterface $indexDefinition
	 */
	public function getIndexMapping(\Rbs\Elasticsearch\Index\IndexDefinitionInterface $indexDefinition)
	{
		$indexMapping = array();
		foreach($indexDefinition->getFacetsDefinition() as $facet)
		{
			if ($facet instanceof FacetDefinitionInterface)
			{
				$value = $this->getFacetMapping($facet, $indexDefinition, $indexMapping);
				if (is_array($value))
				{
					$indexMapping = array_merge($indexMapping, $value);
				}
			}
		}
		return $indexMapping;
	}

	/**
	 * @param \Rbs\Elasticsearch\Facet\FacetDefinitionInterface $facet
	 * @param \Rbs\Elasticsearch\Index\IndexDefinitionInterface $indexDefinition
	 * @param array $indexMapping
	 * @return array|null
	 */
	public function getFacetMapping(FacetDefinitionInterface $facet, $indexDefinition, $indexMapping)
	{
		$parameters = array('facet' => $facet, 'indexDefinition' => $indexDefinition, 'indexMapping' => $indexMapping);
		$event = new \Zend\EventManager\Event('getFacetMapping', $this, $parameters);
		$this->getEventManager()->trigger($event);
		$value = $event->getParam('mapping');
		return is_array($value) ? $value : null;
	}


	/**
	 * @param \Zend\EventManager\Event $event
	 */
	public function onDefaultGetFacetMapping(\Zend\EventManager\Event $event)
	{
		/* @var $facetManager FacetManager */
		$mapping = null;
		$facet = $event->getParam('facet');
		$indexMapping = $event->getParam('indexMapping');

		$indexDefinition = $event->getParam('indexDefinition');
		if ($indexDefinition instanceof \Rbs\Elasticsearch\Documents\StoreIndex &&
			$facet instanceof \Rbs\Elasticsearch\Documents\Facet)
		{
			$fieldName = $facet->getFieldName();
			//only one indexation by fieldName
			if (array_key_exists($fieldName, $indexMapping))
			{
				return;
			}

			switch ($facet->getValuesExtractorName())
			{
				case 'Price':
					$mapping = array($fieldName => array('type' => 'double'),
						'price_id' => array('type' => 'long'));
					break;
				case 'SkuThreshold':
					$mapping = array($fieldName => array('type' => 'string', 'index' => 'not_analyzed'),
						$fieldName . '_level' => array('type' => 'long'), 'sku_id' => array('type' => 'long'));
					break;
				case 'Attribute':
					$attribute = $facet->getAttributeIdInstance();
					if ($attribute)
					{
						$type = $attribute->getValueType();
						if ($type === \Rbs\Catalog\Documents\Attribute::TYPE_PROPERTY)
						{
							$property = $attribute->getModelProperty();
							if ($property)
							{
								$type = $property->getType();
							}
						}
						switch ($type)
						{
							case \Rbs\Catalog\Documents\Attribute::TYPE_BOOLEAN:
							case \Change\Documents\Property::TYPE_BOOLEAN:
								$mapping = array($fieldName => array('type' => 'boolean'));
								break;
							case \Rbs\Catalog\Documents\Attribute::TYPE_DATETIME:
							case \Change\Documents\Property::TYPE_DATE:
							case \Change\Documents\Property::TYPE_DATETIME:
								$mapping = array($fieldName => array('type' => 'date'));
								break;
							case \Rbs\Catalog\Documents\Attribute::TYPE_FLOAT:
							case \Change\Documents\Property::TYPE_FLOAT:
							case \Change\Documents\Property::TYPE_DECIMAL:
								$mapping = array($fieldName => array('type' => 'double'));
								break;
							case \Rbs\Catalog\Documents\Attribute::TYPE_INTEGER:
							case \Rbs\Catalog\Documents\Attribute::TYPE_DOCUMENT:
							case \Rbs\Catalog\Documents\Attribute::TYPE_DOCUMENTARRAY:
							case \Change\Documents\Property::TYPE_INTEGER:
							case \Change\Documents\Property::TYPE_DOCUMENTID:
							case \Change\Documents\Property::TYPE_DOCUMENT:
							case \Change\Documents\Property::TYPE_DOCUMENTARRAY:
								$mapping = array($fieldName => array('type' => 'long'));
								break;
							default:
								$mapping = array($fieldName => array('type' => 'string', 'index' => 'not_analyzed'));
								break;
						}
					}
			}
		}

		if (is_array($mapping))
		{
			$event->setParam('mapping', $mapping);
		}
	}

	/**
	 * @param FacetDefinitionInterface $facet
	 * @param \Rbs\Elasticsearch\Index\IndexDefinitionInterface $indexDefinition
	 * @param \Change\Documents\AbstractDocument $document
	 * @param array $values
	 * @return array|null
	 */
	public function getIndexerValue($facet, $indexDefinition, $document, $values)
	{
		$parameters = array('facet' => $facet, 'document' => $document,
			'indexDefinition' => $indexDefinition, 'values' => $values);
		$event = new \Zend\EventManager\Event('getIndexerValue', $this, $parameters);
		$this->getEventManager()->trigger($event);
		$value = $event->getParam('value');
		return is_array($value) ? $value : null;

	}

	/**
	 * @param \Zend\EventManager\Event $event
	 */
	public function onDefaultGetIndexerValue(\Zend\EventManager\Event $event)
	{
		/* @var $facetManager FacetManager */
		$value = null;
		$facet = $event->getParam('facet');
		$document = $event->getParam('document');
		$values = $event->getParam('values');

		$indexDefinition = $event->getParam('indexDefinition');
		if ($indexDefinition instanceof \Rbs\Elasticsearch\Documents\StoreIndex &&
			$facet instanceof \Rbs\Elasticsearch\Documents\Facet &&
			$document instanceof \Rbs\Catalog\Documents\Product)
		{
			//only one indexation by fieldName
			if (array_key_exists($facet->getFieldName(), $values))
			{
				return;
			}

			switch ($facet->getValuesExtractorName())
			{
				case 'Price':
					$sku = $document->getSku();
					if ($sku)
					{
						$commerceServices = $indexDefinition->getCommerceServices();
						$price = $commerceServices->getPriceManager()->getPriceBySku($sku, $commerceServices->getWebStore());
						if ($price)
						{
							$value = array($facet->getFieldName() => $price->getValue(),
								'price_id' => $price->getId());
						}
					}
					break;
				case 'SkuThreshold':
					$sku = $document->getSku();
					$store = $indexDefinition->getCommerceServices()->getWebStore();
					if ($sku && $store)
					{
						$fieldName = $facet->getFieldName();
						$stockManager = $indexDefinition->getCommerceServices()->getStockManager();
						$level = $stockManager->getInventoryLevel($sku, $store);
						$threshold = $stockManager->getInventoryThreshold($sku, $store, $level);
						$value = array($fieldName => $threshold, 'sku_id' => $sku->getId(),
							$fieldName . '_level' => $level);
					}
					break;
				case 'Attribute':
					$attribute = $facet->getAttributeIdInstance();
					if ($attribute)
					{
						$fieldName = $facet->getFieldName();
						$attributeValue = $attribute->getValue($document);
						if ($attributeValue instanceof \Change\Documents\AbstractDocument)
						{
							$value = array($fieldName => $attributeValue->getId());
						}
						elseif ($attributeValue instanceof \Change\Documents\DocumentArrayProperty)
						{
							$value = array($fieldName => $attributeValue->getIds());
						}
						elseif ($attributeValue instanceof \DateTime)
						{
							$value = array($fieldName => $attributeValue->format(\DateTime::ISO8601));
						}
						elseif (is_string($attributeValue) || is_bool($attributeValue) || is_numeric($attributeValue))
						{
							$value = array($fieldName => $attributeValue);
						}
					}
			}
		}

		if (is_array($value))
		{
			$event->setParam('value', $value);
		}
	}


	/**
	 * @param \Rbs\Elasticsearch\Facet\FacetValue $facetValue
	 * @param \Rbs\Elasticsearch\Facet\FacetDefinitionInterface $facet
	 * @return \Rbs\Elasticsearch\Facet\FacetValue
	 */
	public function updateFacetValueTitle($facetValue, $facet)
	{
		if ($facet instanceof \Rbs\Elasticsearch\Documents\Facet)
		{
			$collection = $this->getCollectionByCode($facet->getCollectionCode());
			if ($collection)
			{
				$item = $collection->getItemByValue($facetValue->getValue());
				if ($item)
				{
					$facetValue->setValueTitle($item->getTitle());
				}
			}
			elseif (($attribute = $facet->getAttributeIdInstance()) !== null)
			{
				$attributeEngine = new \Rbs\Catalog\Std\AttributeEngine($this->getDocumentServices());
				$attributeEngine->setCollectionManager($this->getCollectionManager());
				$attrDef = $attributeEngine->buildAttributeDefinition($attribute);
				$attrType = $attrDef['type'];
				if ($attrType == \Rbs\Catalog\Documents\Attribute::TYPE_DOCUMENT || $attrType == \Rbs\Catalog\Documents\Attribute::TYPE_DOCUMENTARRAY)
				{
					$document = $attribute->getDocumentServices()->getDocumentManager()->getDocumentInstance($facetValue->getValue());
					if ($document)
					{
						$facetValue->setValueTitle($document->getDocumentModel()->getPropertyValue($document, 'title'));
					}
				}
			}
		}
		elseif ($facet instanceof ModelFacetDefinition)
		{
			$model = $this->getDocumentServices()->getModelManager()->getModelByName($facetValue->getValue());
			if ($model)
			{
				$facetValue->setValueTitle($this->getApplicationServices()->getI18nManager()->trans($model->getLabelKey()));
			}
		}
		return $facetValue;
	}
}