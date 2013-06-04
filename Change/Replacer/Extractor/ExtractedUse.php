<?php
namespace Change\Replacer\Extractor;

/**
 * @name \Change\Replacer\Extractor\ExtractedUse
 */
class ExtractedUse
{
	/**
	 * use declaration
	 * 
	 * @var string
	 */
	protected $declaration;
	
	/**
	 * @return string
	 */
	public function getDeclaration()
	{
		return $this->declaration;
	}

	/**
	 * @param string $declaration
	 */
	public function setDeclaration($declaration)
	{
		$this->declaration = $declaration;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'use ' . $this->declaration . ';';
	}
}