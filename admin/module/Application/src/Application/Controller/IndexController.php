<?php
namespace Application\Controller;

use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Push\Util\UserUtil;

/**
 * Class IndexController
 * @package Application\Controller
 * @property \Zend\View\Model\ViewModel $viewModel
 */
class IndexController extends AbstractActionController
{
    protected $userUtil;
    protected $viewModel;

    public function __construct(ControllerManager $controllerManager)
    {
        $this->viewModel = new ViewModel();
    }
    
    
    public function onDispatch(MvcEvent $e)
    {
        $this->userUtil = new UserUtil();
        return parent::onDispatch($e);
    }
    
    public function indexAction()
    {
        if($this->userUtil->isSignOn()){
            return $this->forward()->dispatch('Push\Controller\Index');
        }
        
        return $this->redirect()->toUrl('/push/user/sign-in');
    }
    
}
