<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Presentation\Blocks;

use Change\Documents\Property;

/**
 * @name \Change\Presentation\Blocks\ParameterInformation
 */
class ParameterInformation
{
	const TYPE_COLLECTION = 'Collection';
	const TYPE_DOCUMENTIDARRAY = 'DocumentIdArray';

	/**
	 * @var array
	 */
	protected $attributes;

	/**
	 * @param string $name
	 * @param string $type
	 * @param boolean $required
	 * @param mixed $defaultValue
	 */
	function __construct($name, $type = Property::TYPE_STRING, $required = false, $defaultValue = null)
	{
		$this->attributes['name'] = $name;
		$this->attributes['type'] = $type;
		$this->attributes['required'] = $required;
		$this->attributes['defaultValue']= $defaultValue;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->attributes['name'];
	}

	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->attributes['label'] = $label;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->attributes['label'];
	}

	/**
	 * @param mixed|null $defaultValue
	 * @return $this
	 */
	public function setDefaultValue($defaultValue)
	{
		$this->attributes['defaultValue'] = $defaultValue;
		return $this;
	}

	/**
	 * @param boolean $required
	 * @return $this
	 */
	public function setRequired($required)
	{
		$this->attributes['required'] = $required;
		return $this;
	}

	/**
	 * If string assume comma separated string or json array string (start with '[')
	 * @param string|string[] $allowedModelsNames
	 * @return $this
	 */
	public function setAllowedModelsNames($allowedModelsNames)
	{
		if (is_string($allowedModelsNames))
		{
			if ($allowedModelsNames[0] === '[')
			{
				$allowedModelsNames = json_decode($allowedModelsNames, true);
			}
			else
			{
				$allowedModelsNames = explode(',', $allowedModelsNames);
			}
		}

		if (is_array($allowedModelsNames))
		{
			$this->attributes['allowedModelsNames'] = $allowedModelsNames;
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->attributes;
	}

	/**
	 * @param $collectionCode
	 * @return $this
	 */
	public function setCollectionCode($collectionCode)
	{
		$this->attributes['collectionCode'] = $collectionCode;
		return $this;
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return array_key_exists($name, $this->attributes);
	}

}