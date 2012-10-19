<?php
namespace ChangeTests\Documents\Generators;

/**
 * @name \ChangeTests\Documents\Generators\AbstractDocumentClassTest
 */
class AbstractDocumentClassTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @return \Change\Application
	 */
	protected function getApplication()
	{
		return \Change\Application::getInstance();
	}
	
	public function testAllType()
	{
		$compiler = new \Change\Documents\Generators\Compiler($this->getApplication());
		$definitionPath = __DIR__ . '/TestAssets/TestGeneration.xml';
		$model = $compiler->loadDocument('change', 'testing', 'generation', $definitionPath);
		$compiler->buildDependencies();
		
		$generator = new \Change\Documents\Generators\AbstractDocumentClass();
		$code = $generator->getPHPCode($compiler, $model);
		
		//file_put_contents($definitionPath . '.doc.php.expected', $code);
		$expected = file_get_contents($definitionPath . '.doc.php.expected');
				
		$this->assertEquals($expected, $code);
	}
}