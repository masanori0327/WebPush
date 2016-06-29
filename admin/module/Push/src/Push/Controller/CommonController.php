<?php
namespace Push\Controller;

use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

use Push\Util\UserUtil;

/**
 * Class CommonController
 * @package Push\Controller
 */
class CommonController extends AbstractActionController
{
    protected $userUtil;
    protected $viewModel;
    protected $viewVariables;
    protected $container;
    
    public function __construct(ControllerManager $controllerManager)
    {
        $this->container = new Container('Push');
        $this->viewModel = new ViewModel();
        $this->viewVariables = [];
    }
    
    public function onDispatch(MvcEvent $e)
    {
        $this->userUtil = new UserUtil();
        
        $controller = $this->params('controller');
        if($controller === 'Push\Controller\User'){
            return parent::onDispatch($e);
        }
        
        if(!$this->userUtil->isSignOn()){
            return $this->redirect()->toUrl('/push/user/sign-out');
        }
        
        $this->layout()->setVariable('user', $this->userUtil->get());
        $this->viewModel->setVariables(['user' => $this->userUtil->get(),
            'action' => $this->params('action')
        ]);
        
        $redirect = $this->initDispatch();
        if($redirect) return $redirect;
    
        return parent::onDispatch($e);
    }
    
    /**
     * 画面遷移中になにか表現したいときにつかおうかな
     */
    public function waitingAction()
    {
        $path = $this->params()->fromQuery('path', false);
        $query = $this->params()->fromQuery('query', false);
        $fragment = $this->params()->fromQuery('fragment', false);
    
        if($path === false){
            return $this->redirect()->toUrl('/');
        }

        return $this->viewModel->setVariables(array(
            'next' => array(
                'path' => $path,
                'query' => $query,
                'fragment' => $fragment,
            ),
        ));
    }
}
