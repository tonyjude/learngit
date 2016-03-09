<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb
 */
abstract class Lamb_Controllor_Abstract
{
	/**
	 * @var string 
	 * 代表当前的controllor的名字
	 */
	public $C;
	
	/**
	 * @var string
	 * 代表当前action的名字
	 */
	public $A;
	/**
	 * @var Lamb_App_Request
	 */
	protected $mRequest;
	
	/**
	 * @var Lamb_App_Response
	 */
	protected $mResponse;
	
	/**
	 * @var Lamb_App
	 */
	protected $mApp;
	
	/**
	 * @var Lamb_App_Router_Interface
	 */
	protected $mRouter;
	
	/**
	 * @var Lamb_App_Dispatcher_Interface
	 */
	protected $mDispatcher;
	
	/**
	 * @var Lamb_View
	 */
	protected $mView;
	
	/**
	 * Construct the Lamb_Controllor_Abstract
	 *
	 * @param Lamb_App $app
	 */
	public function __construct(Lamb_App $app = null)
	{
		if (null === $app) {
			$app = Lamb_App::getGlobalApp();
		}
		$this->setOrGetApp($app)
			 ->setOrGetRequest($app->getRequest())
			 ->setOrGetResponse($app->getResponse())
			 ->setOrGetRouter($app->getRouter())
			 ->setOrGetDispatcher($app->getDispatcher())
			 ->setOrGetView($app->getView());
		$hash = spl_object_hash($this->mRouter);
		if (!defined('CALL_ROUTER')) {
			define('CALL_ROUTER', $hash);
		}
		Lamb_Utils::registerCallObject($this->mRouter);
		$this->C = $this->mDispatcher->setOrGetControllor();
		$this->A = $this->mDispatcher->setOrGetAction();		
	}
	
	/**
	 * @param Lamb_App $app
	 * @return Lamb_App | Lamb_Controllor_Abstract
	 */
	public function setOrGetApp(Lamb_App $app = null)
	{
		if (null === $app) {
			return $this->mApp;
		}
		$this->mApp = $app;
		return $this;
	}
	
	/**
	 * @param Lamb_App_Reuqest $request
	 * @return Lamb_App_Reuqest | Lamb_Controllor_Abstract
	 */
	public function setOrGetRequest(Lamb_App_Request $request = null)
	{
		if (null === $request) {
			return $this->mRequest;
		}
		$this->mRequest = $request;
		return $this;
	}
	
	/**
	 * @param Lamb_App_Response $response
	 * @return Lamb_App_Response | Lamb_Controllor_Abstract
	 */
	public function setOrGetResponse(Lamb_App_Response $response = null)
	{
		if (null === $response) {
			return $this->mResponse;
		}
		$this->mResponse = $response;
		return $this;
	}
	
	/**
	 * @param Lamb_App_Router_Interface $router
	 * @return Lamb_App_Router_Interface | Lamb_Controllor_Abstract
	 */
	public function setOrGetRouter(Lamb_App_Router_Interface $router = null)
	{
		if (null === $router) {
			return $this->mRouter;
		}
		$this->mRouter = $router;
		return $this;
	}
	
	/**
	 * @param Lamb_App_Dispatcher_Interface $dispatcher
	 * @return Lsmb_App_Dispatcher_Interface | Lamb_Controllor_Abstract
	 */
	public function setOrGetDispatcher(Lamb_App_Dispatcher_Interface $dispatcher = null)
	{
		if (null === $dispatcher) {
			return $this->mDispatcher;
		}
		$this->mDispatcher = $dispatcher;
		return $this;
	}
	
	/**
	 * @param Lamb_View $view
	 * @return Lamb_View | Lamb_Controllor_Abstract
	 */
	public function setOrGetView(Lamb_View $view = null)
	{
		if (null === $view) {
			return $this->mView;
		}
		$this->mView = $view;
		return $this;
	}
	
	abstract public function getControllorName();
}