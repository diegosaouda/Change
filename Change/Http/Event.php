<?php
namespace Change\Http;

use Change\Application\ApplicationServices;
use Change\Documents\DocumentServices;
use Change\Permissions\PermissionsManager;
use Change\Presentation\PresentationServices;
use Change\User\AuthenticationManager;

/**
 * @name \Change\Http\Event
 */
class Event extends \Zend\EventManager\Event
{
	const EVENT_REQUEST = 'http.request';
	const EVENT_ACTION = 'http.action';
	const EVENT_RESULT = 'http.result';
	const EVENT_RESPONSE = 'http.response';
	const EVENT_EXCEPTION = 'http.exception';
	const EVENT_AUTHENTICATE = 'http.authenticate';

	/**
	 * @var ApplicationServices
	 */
	protected $applicationServices;

	/**
	 * @var DocumentServices
	 */
	protected $documentServices;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var UrlManager
	 */
	protected $urlManager;

	/**
	 * @var Callable|null
	 */
	protected $authorization;

	/**
	 * @var Callable|null
	 */
	protected $action;

	/**
	 * @var Result
	 */
	protected $result;

	/**
	 * @var \Zend\Http\PhpEnvironment\Response
	 */
	protected $response;

	/**
	 * @var AuthenticationManager
	 */
	protected $authenticationManager;

	/**
	 * @var PermissionsManager
	 */
	protected $permissionsManager;


	/**
	 * @api
	 * @return Controller|null
	 */
	public function getController()
	{
		if ($this->getTarget() instanceof Controller)
		{
			return $this->getTarget();
		}
		return null;
	}

	/**
	 * @param ApplicationServices|null $applicationServices
	 */
	public function setApplicationServices(ApplicationServices $applicationServices = null)
	{
		$this->applicationServices = $applicationServices;
	}

	/**
	 * @api
	 * @return ApplicationServices|null
	 */
	public function getApplicationServices()
	{
		return $this->applicationServices;
	}

	/**
	 * @param DocumentServices|null $documentServices
	 */
	public function setDocumentServices(DocumentServices $documentServices = null)
	{
		$this->documentServices = $documentServices;
	}

	/**
	 * @api
	 * @return DocumentServices|null
	 */
	public function getDocumentServices()
	{
		return $this->documentServices;
	}

	/**
	 * @param \Change\Presentation\PresentationServices $presentationServices
	 */
	public function setPresentationServices(PresentationServices $presentationServices = null)
	{
		$this->setParam('presentationServices', $presentationServices);
	}

	/**
	 * @api
	 * @return \Change\Presentation\PresentationServices|null
	 */
	public function getPresentationServices()
	{
		return $this->getParam('presentationServices');
	}

	/**
	 * @param Request $request
	 */
	public function setRequest($request)
	{
		$this->request = $request;
	}

	/**
	 * @api
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param UrlManager $urlManager
	 */
	public function setUrlManager($urlManager)
	{
		$this->urlManager = $urlManager;
	}

	/**
	 * @api
	 * @return UrlManager
	 */
	public function getUrlManager()
	{
		return $this->urlManager;
	}

	/**
	 * @param AuthenticationManager $authenticationManager
	 */
	public function setAuthenticationManager(AuthenticationManager $authenticationManager)
	{
		$this->authenticationManager = $authenticationManager;
	}

	/**
	 * @api
	 * @return AuthenticationManager
	 */
	public function getAuthenticationManager()
	{
		return $this->authenticationManager;
	}

	/**
	 * @param PermissionsManager $permissionsManager
	 */
	public function setPermissionsManager(PermissionsManager $permissionsManager)
	{
		$this->permissionsManager = $permissionsManager;
	}

	/**
	 * @api
	 * @return PermissionsManager
	 */
	public function getPermissionsManager()
	{
		return $this->permissionsManager;
	}

	/**
	 * @api
	 * @param Callable|null $authorization
	 */
	public function setAuthorization($authorization)
	{
		$this->authorization = $authorization;
	}

	/**
	 * @api
	 * @return Callable|null
	 */
	public function getAuthorization()
	{
		return $this->authorization;
	}

	/**
	 * @api
	 * @param Callable|null $action
	 */
	public function setAction($action)
	{
		$this->action = $action;
	}

	/**
	 * @api
	 * @return Callable|null
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @api
	 * @param Result $result
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	/**
	 * @api
	 * @return Result
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * @api
	 * @param \Zend\Http\PhpEnvironment\Response|null $response
	 */
	public function setResponse($response)
	{
		$this->response = $response;
	}

	/**
	 * @api
	 * @return \Zend\Http\PhpEnvironment\Response|Null
	 */
	public function getResponse()
	{
		return $this->response;
	}
}