<?php
namespace Change\Documents\Generators;

/**
 * @name \Change\Documents\Generators\Compiler
 */
class Compiler
{
	/**
	 * \Change\Documents\Generators\Model[]
	 */
	protected $models = array();
	
	protected $modelNamesByExtendLevel = array();
	
	/**
	 * @var string
	 */
	protected $injection = array();
	
	/**
	 * @return \Change\Documents\Generators\Model
	 */
	public function getDefaultModel()
	{
		$doc = new \DOMDocument('1.0', 'utf-8');
		$doc->load(__DIR__ . '/Assets/document.xml');
		$model = new \Change\Documents\Generators\Model(null, null, null);
		$model->setXmlDocument($doc);
		return $model;
	}
	
	/**
	 * @param string $vendor
	 * @param string $moduleName
	 * @param string $documentName
	 * @param string $definitionPath
	 * @return \Change\Documents\Generators\Model
	 * @throws \Exception
	 */
	public function loadDocument($vendor, $moduleName, $documentName, $definitionPath)
	{
		$doc = new \DOMDocument('1.0', 'utf-8');
		if (is_readable($definitionPath) && $doc->load($definitionPath))
		{
			$model = new \Change\Documents\Generators\Model($vendor, $moduleName, $documentName);
			$model->setXmlDocument($doc);
			$this->addModel($model);
		}
		else
		{
			throw new \Exception('Unable to load document definition : ' . $definitionPath);
		}
		return $model;
	}
	
	/**
	 * @throws \Exception
	 */
	public function checkExtends()
	{
		$this->injection = array();
		
		foreach ($this->models as $model)
		{
			/* @var $model \Change\Documents\Generators\Model */
			$modelName = $model->getFullName();
			if ($model->getExtend())
			{
				$extendName = $this->cleanModelName($model->getExtend());
				if ($this->getModelByFullName($extendName) === null)
				{
					throw new \Exception('Document ' . $modelName . ' extend unknow ' . $extendName. ' document.');
				}
				
				if ($model->getInject())
				{
					if (isset($this->injection[$extendName]))
					{
						throw new \Exception('Duplicate Injection on ' . $modelName . ' for ' . $extendName. ' Already Injected by ' . $this->injection[$extendName]);
					}
					$this->injection[$extendName] = $modelName;
				}
			}
			elseif ($model->getInject())
			{
				throw new \Exception('Invalid Injection on ' . $modelName . ' document.');
			}
			else
			{
				$model->applyDefault($this->getDefaultModel());
			}
		}
		
		$this->modelNamesByExtendLevel = array();
		
		foreach ($this->models as $model)
		{
			/* @var $model \Change\Documents\Generators\Model */
			$ancestors = $this->getAncestors($model);
			$this->modelNamesByExtendLevel[count($ancestors)][] = $this->cleanModelName($model->getFullName()); 
		}
		
		ksort($this->modelNamesByExtendLevel);
	}
	
	/**
	 * 
	 */
	public function buildDependencies()
	{
		$this->checkExtends();
		foreach ($this->modelNamesByExtendLevel as $lvl => $modelNames)
		{
			foreach ($modelNames as $modelName)
			{
				$model = $this->getModelByFullName($modelName);			
				if ($model->getInject()) //Check Injection
				{
					if (count($this->getChildren($model)))
					{
						throw new \Exception('Injected Model ' . $modelName . ' has children.');
					}
				}
				$ancestors =  $this->getAncestors($model);
				$model->validate($ancestors);
				
				//Add Inverse Properties
				foreach ($model->getProperties() as $property)
				{
					/* @var $property \Change\Documents\Generators\Property */
					if ($property->getInverse())
					{
						$docType = $property->getDocumentType();
						$im = $this->getModelByFullName($docType);
						if (!$im)
						{
							throw new \Exception('Inverse Property on unknow Model ' . $docType . ' (' . $modelName . '::' . $property->getName() . ')');
						}
						$ip = new InverseProperty($property, $model);
						$im->addInverseProperty($ip);
					}
				}
			}
		}
	}
	
	/**
	 * @param string $name
	 * @return string
	 */
	public function cleanModelName($name)
	{
		return strtolower(str_replace(array('/', 'modules_'), array('_', 'Change_'), $name));
	}
	
	/**
	 * @param string $fullName
	 * @return \Change\Documents\Generators\Model|null
	 */
	public function getModelByFullName($fullName)
	{
		$name = $this->cleanModelName($fullName);
		return isset($this->models[$name]) ? $this->models[$name] : null;
	}
	
	/**
	 * @param \Change\Documents\Generators\Model $model
	 */
	public function addModel(\Change\Documents\Generators\Model $model)
	{
		$this->models[$this->cleanModelName($model->getFullName())] = $model;
	}
	
	/**
	 * @param \Change\Documents\Generators\Model $model
	 * @return \Change\Documents\Generators\Model|null
	 */
	public function getParent($model)
	{
		if ($model->getExtend())
		{
			return $this->getModelByFullName($model->getExtend());
		}
		return null;
	}
	
	/**
	 * @param \Change\Documents\Generators\Model $model
	 * @return \Change\Documents\Generators\Model[]
	 * @throws \Exception
	 */	
	public function getAncestors($model)
	{
		$childModelName = $model->getFullName();
		$result = array();
		while (($model = $this->getParent($model)) !== null)
		{
			$modelName = $model->getFullName();
			if (isset($this->injection[$modelName]))
			{
				$injectionName = $this->injection[$modelName];
				if ($childModelName != $injectionName)
				{
					$result[$injectionName] = $this->getModelByFullName($injectionName);
				}
			}
			if (isset($result[$modelName]))
			{
				throw new \Exception('Recursion on ' . $modelName . ' document.');
			}
			
			$result[$modelName] = $model;
		}
		return array_reverse($result, true);
	}
	
	/**
	 * @param \Change\Documents\Generators\Model $model
	 * @return \Change\Documents\Generators\Model[]
	 */
	public function getChildren($model)
	{
		$result = array();
		foreach ($this->models as $cm)
		{
			/* @var $cm \Change\Documents\Generators\Model */
			$cmp = $cm->getExtend() ? $this->getModelByFullName($cm->getExtend()) : null;
			if ($cmp === $model)
			{
				$result[$cm->getFullName()] = $cm;
			}
		}
		return $result;
	}
	
	/**
	 * @param \Change\Documents\Generators\Model $model
	 * @return \Change\Documents\Generators\Model[]
	 */
	public function getDescendants($model, $excludeInjected = false)
	{
		$result = array();
		foreach ($this->getChildren($model) as $name => $cm)
		{
			/* @var $cm \Change\Documents\Generators\Model */
			if ($excludeInjected && $cm->getInject())
			{
				continue;
			}
			$result[$name] = $cm;
			$dm = $this->getDescendants($cm);
			if (count($dm))
			{
				$result = array_merge($result, $dm);
			}
		}
		return $result;
	}
			
	
	public function saveModelsPHPCode()
	{
		foreach ($this->models as $model)
		{
			/* @var $model \Change\Documents\Generators\Model */
			$generator = new ModelClass();
			$generator->savePHPCode($this, $model);
			
			$generator = new AbstractDocumentClass();
			$generator->savePHPCode($this, $model);
			
			if ($model->getLocalized())
			{
				$generator = new DocumentI18nClass();
				$generator->savePHPCode($this, $model);
			}
		}
	}
}