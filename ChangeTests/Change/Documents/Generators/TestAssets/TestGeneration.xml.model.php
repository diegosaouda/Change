<?php
namespace Compilation\Change\Testing\Documents;
class GenerationModel extends \Change\Documents\AbstractModel
{

	protected function __construct()
	{
		parent::__construct();
	}

	protected function loadProperties()
	{
		parent::loadProperties();
		$this->m_properties['id'] = new \Change\Documents\Property('id', 'Integer');
		$this->m_properties['id']->setRequired(true);
		$this->m_properties['label'] = new \Change\Documents\Property('label', 'String');
		$this->m_properties['label']->setRequired(true)->setLocalized(true)->setConstraintArray(array('maxSize'=>array('max'=>255,),));
		$this->m_properties['author'] = new \Change\Documents\Property('author', 'String');
		$this->m_properties['author']->setIndexed('none')->setConstraintArray(array('maxSize'=>array('max'=>100,),));
		$this->m_properties['authorid'] = new \Change\Documents\Property('authorid', 'DocumentId');
		$this->m_properties['authorid']->setDocumentType('modules_users/user');
		$this->m_properties['creationdate'] = new \Change\Documents\Property('creationdate', 'DateTime');
		$this->m_properties['modificationdate'] = new \Change\Documents\Property('modificationdate', 'DateTime');
		$this->m_properties['publicationstatus'] = new \Change\Documents\Property('publicationstatus', 'String');
		$this->m_properties['publicationstatus']->setDefaultValue('DRAFT')->setLocalized(true)->setIndexed('none');
		$this->m_properties['lang'] = new \Change\Documents\Property('lang', 'String');
		$this->m_properties['lang']->setIndexed('none')->setConstraintArray(array('maxSize'=>array('max'=>2,),));
		$this->m_properties['modelversion'] = new \Change\Documents\Property('modelversion', 'String');
		$this->m_properties['modelversion']->setIndexed('none')->setConstraintArray(array('maxSize'=>array('max'=>20,),));
		$this->m_properties['documentversion'] = new \Change\Documents\Property('documentversion', 'Integer');
		$this->m_properties['documentversion']->setDefaultValue('0');
		$this->m_properties['startpublicationdate'] = new \Change\Documents\Property('startpublicationdate', 'DateTime');
		$this->m_properties['endpublicationdate'] = new \Change\Documents\Property('endpublicationdate', 'DateTime');
		$this->m_properties['metastring'] = new \Change\Documents\Property('metastring', 'Lob');
		$this->m_properties['bool1'] = new \Change\Documents\Property('bool1', 'Boolean');
		$this->m_properties['int1'] = new \Change\Documents\Property('int1', 'Integer');
		$this->m_properties['int1']->setRequired(true);
		$this->m_properties['float1'] = new \Change\Documents\Property('float1', 'Float');
		$this->m_properties['decimal1'] = new \Change\Documents\Property('decimal1', 'Decimal');
		$this->m_properties['date1'] = new \Change\Documents\Property('date1', 'Date');
		$this->m_properties['datetime1'] = new \Change\Documents\Property('datetime1', 'DateTime');
		$this->m_properties['string1'] = new \Change\Documents\Property('string1', 'String');
		$this->m_properties['string1']->setLocalized(true)->setConstraintArray(array('maxSize'=>array('max'=>50,),));
		$this->m_properties['longstring1'] = new \Change\Documents\Property('longstring1', 'LongString');
		$this->m_properties['xml1'] = new \Change\Documents\Property('xml1', 'XML');
		$this->m_properties['lob1'] = new \Change\Documents\Property('lob1', 'Lob');
		$this->m_properties['richtext1'] = new \Change\Documents\Property('richtext1', 'RichText');
		$this->m_properties['richtext1']->setLocalized(true);
		$this->m_properties['json1'] = new \Change\Documents\Property('json1', 'JSON');
		$this->m_properties['object1'] = new \Change\Documents\Property('object1', 'Object');
		$this->m_properties['documentid1'] = new \Change\Documents\Property('documentid1', 'DocumentId');
		$this->m_properties['document1'] = new \Change\Documents\Property('document1', 'Document');
		$this->m_properties['document1']->setDocumentType('change_testing_generation')->setCascadeDelete(true);
		$this->m_properties['documentarray1'] = new \Change\Documents\Property('documentarray1', 'DocumentArray');
		$this->m_properties['correctionid'] = new \Change\Documents\Property('correctionid', 'Integer');
		$this->m_properties['correctionid']->setLocalized(true);
		$this->m_properties['correctionofid'] = new \Change\Documents\Property('correctionofid', 'Integer');
		$this->m_properties['s18s'] = new \Change\Documents\Property('s18s', 'Lob');
	}

	protected function loadSerialisedProperties()
	{
		parent::loadSerialisedProperties();
		$this->m_serialisedproperties['bool2'] = new \Change\Documents\Property('bool2', 'Boolean');
		$this->m_serialisedproperties['int2'] = new \Change\Documents\Property('int2', 'Integer');
		$this->m_serialisedproperties['float2'] = new \Change\Documents\Property('float2', 'Float');
		$this->m_serialisedproperties['float2']->setRequired(true);
		$this->m_serialisedproperties['decimal2'] = new \Change\Documents\Property('decimal2', 'Decimal');
		$this->m_serialisedproperties['date2'] = new \Change\Documents\Property('date2', 'Date');
		$this->m_serialisedproperties['datetime2'] = new \Change\Documents\Property('datetime2', 'DateTime');
		$this->m_serialisedproperties['string2'] = new \Change\Documents\Property('string2', 'String');
		$this->m_serialisedproperties['string2']->setConstraintArray(array('minSize'=>array('min'=>'10',),'maxSize'=>array('min'=>'500',),));
		$this->m_serialisedproperties['longstring2'] = new \Change\Documents\Property('longstring2', 'LongString');
		$this->m_serialisedproperties['xml2'] = new \Change\Documents\Property('xml2', 'XML');
		$this->m_serialisedproperties['lob2'] = new \Change\Documents\Property('lob2', 'Lob');
		$this->m_serialisedproperties['richtext2'] = new \Change\Documents\Property('richtext2', 'RichText');
		$this->m_serialisedproperties['json2'] = new \Change\Documents\Property('json2', 'JSON');
		$this->m_serialisedproperties['object2'] = new \Change\Documents\Property('object2', 'Object');
		$this->m_serialisedproperties['documentid2'] = new \Change\Documents\Property('documentid2', 'DocumentId');
	}

	protected function loadInvertProperties()
	{
		parent::loadInvertProperties();
		$this->m_invertProperties['generation'] = new \Change\Documents\Property('generation', 'Document');
		$this->m_invertProperties['generation']->setDocumentType('change_testing_generation')->setRelationName('document1');
	}

	/**
	 * @return string
	 */
	public function getIcon()
	{
		return 'test';
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'change_testing_generation';
	}


	/**
	 * @return string
	 */
	public function getVendorName()
	{
		return 'change';
	}
			

	/**
	 * @return string
	 */
	public function getModuleName()
	{
		return 'testing';
	}

	/**
	 * @return string
	 */
	public function getDocumentName()
	{
		return 'generation';
	}

	/**
	 * @return boolean
	 */
	public function isLocalized()
	{
		return true;
	}

	/**
	 * @return boolean
	 */
	public function hasURL()
	{
		return true;
	}

	/**
	 * @return boolean
	 */
	public function useRewriteURL()
	{
		return true && $this->hasURL();
	}

	/**
	 * @return boolean
	 */
	public function isIndexable()
	{
		return true && $this->hasURL();
	}

	/**
	 * @return boolean
	 */
	public function isBackofficeIndexable()
	{
		return true;
	}

	/**
	 * @return boolean
	 */
	public function usePublicationDates()
	{
		return true;
	}

	/**
	 * @return string
	 */
	public function getDefaultStatus()
	{
		return 'DRAFT';
	}


	/**
	 * @return boolean
	 */
	public function useCorrection()
	{
		return CHANGE_USE_CORRECTION;
	}

	/**
	 * @return boolean
	 */
	public function hasWorkflow()
	{
		return CHANGE_USE_CORRECTION && CHANGE_USE_WORKFLOW;
	}
		
	/**
	 * @return string
	 */
	public function getWorkflowStartTask()
	{
		return 'start';
	}

	/**
	 * @return array
	 */
	public function getWorkflowParameters()
	{
		return array('p1' => 'TEST');
	}

}
