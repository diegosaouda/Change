<?php
namespace Change\Presentation\Themes;

use Change\Presentation\Interfaces\Theme;
use Change\Presentation\PresentationServices;

class DefaultTheme implements Theme
{
	/**
	 * @var PresentationServices
	 */
	protected $presentationServices;

	/**
	 * @var DefaultPageTemplate
	 */
	protected $defaultPageTemplate;

	/**
	 * @var string
	 */
	protected $vendor;

	/**
	 * @var string
	 */
	protected $shortName;

	/**
	 * @param PresentationServices $presentationServices
	 */
	function __construct($presentationServices)
	{
		$this->presentationServices = $presentationServices;
		list($this->vendor, $this->shortName) = explode('_', $this->getName());
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return ThemeManager::DEFAULT_THEME_NAME;
	}

	/**
	 * @param ThemeManager $themeManager
	 */
	public function setThemeManager(ThemeManager $themeManager)
	{
		if ($this->presentationServices === null)
		{
			$this->presentationServices = $themeManager->getPresentationServices();
		}
	}

	/**
	 * @return null
	 */
	public function getParentTheme()
	{
		return null;
	}

	/**
	 * @return \Change\Workspace
	 */
	protected function getWorkspace()
	{
		return $this->presentationServices->getApplicationServices()->getApplication()->getWorkspace();
	}

	/**
	 * @param string $moduleName
	 * @param string $fileName
	 * @return string|null
	 */
	public function getBlockTemplatePath($moduleName, $fileName)
	{
		list ($vendor, $shortModuleName) = explode('_', $moduleName);
		$path = $this->getWorkspace()->pluginsThemesPath($this->vendor, $this->shortName, $vendor, $shortModuleName, 'Blocks', $fileName);
		return (file_exists($path)) ? $path : null;
	}

	/**
	 * @param string $name
	 * @return \Change\Presentation\Interfaces\PageTemplate
	 */
	public function getPageTemplate($name)
	{
		if (is_numeric($name))
		{
			$name = 'default';
		}
		return new DefaultPageTemplate($this, $name);
	}

	/**
	 * @param string $resourcePath
	 * @return \Change\Presentation\Interfaces\ThemeResource
	 */
	public function getResource($resourcePath)
	{
		$path = $this->getWorkspace()->pluginsThemesPath($this->vendor, $this->shortName, 'Assets', str_replace('/', DIRECTORY_SEPARATOR, $resourcePath));
		return new FileResource($path);
	}
}