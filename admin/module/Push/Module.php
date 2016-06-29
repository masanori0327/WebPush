<?php
namespace Push;

use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;

use Push\Controller\IndexController;
use Push\Controller\UserController;
use Push\Controller\PushController;
use Push\Controller\ApiController;
use Push\Controller\BatchController;

use Push\Model\Dao\DbAdapter;
use Push\Model\Dao\User;
use Push\Model\Dao\UserTable;
use Push\Model\Dao\PushLog;
use Push\Model\Dao\PushLogTable;
use Push\Model\Dao\SubscriberList;
use Push\Model\Dao\SubscriberListTable;
use Push\Model\Dao\ShowNotificationLog;
use Push\Model\Dao\ShowNotificationLogTable;
use Push\Model\Dao\ClickNotificationLog;
use Push\Model\Dao\ClickNotificationLogTable;
use Push\Model\Dao\ErrorNotificationLog;
use Push\Model\Dao\ErrorNotificationLogTable;

class Module
{
    public static $config;
    public static $dbAdapter;
    
    public function onBootstrap(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        
        // setting config
        self::$config = $serviceManager->get('Config');
        
        // DBアダプター
        self::$dbAdapter = (new DbAdapter())->createService($serviceManager);
        
        // セッション
        ini_set('session.gc_maxlifetime', 10800);
        $sessionManager = $serviceManager->get('Zend\Session\SessionManager');
        $sessionManager->start();
        
        $container = new Container(__NAMESPACE__);
        if (!isset($container->init)) {
            $sessionManager->regenerateId(true);
            $container->init = 1;
        }
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'Push\Controller\Index' => function($controllerManager){
                    return new IndexController($controllerManager);
                },
                'Push\Controller\User' => function($controllerManager){
                    return new UserController($controllerManager);
                },
                'Push\Controller\Push' => function($controllerManager){
                    return new PushController($controllerManager);
                },
                
                'Push\Controller\Api' => function($controllerManager){
                    return new ApiController($controllerManager);
                },
                /*
                'Push\Controller\Batch' => function($controllerManager){
                    return new BatchController($controllerManager);
                },
                */
            )
        );
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'Push\Model\Dao\UserTable' => function($sm){
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    $tableGateway = new TableGateway('user', self::$dbAdapter, null, $resultSetPrototype);
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'Push\Model\Dao\PushLogTable' => function($sm){
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new PushLog());
                    $tableGateway = new TableGateway('push_log', self::$dbAdapter, null, $resultSetPrototype);
                    $table = new PushLogTable($tableGateway);
                    return $table;
                },
                'Push\Model\Dao\SubscriberListTable' => function($sm){
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new SubscriberList());
                    $tableGateway = new TableGateway('subscriber_list', self::$dbAdapter, null, $resultSetPrototype);
                    $table = new SubscriberListTable($tableGateway);
                    return $table;
                },
                'Push\Model\Dao\ShowNotificationLogTable' => function($sm){
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ShowNotificationLog());
                    $tableGateway = new TableGateway('show_notification_log', self::$dbAdapter, null, $resultSetPrototype);
                    $table = new ShowNotificationLogTable($tableGateway);
                    return $table;
                },
                'Push\Model\Dao\ClickNotificationLogTable' => function($sm){
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ClickNotificationLog());
                    $tableGateway = new TableGateway('click_notification_log', self::$dbAdapter, null, $resultSetPrototype);
                    $table = new ClickNotificationLogTable($tableGateway);
                    return $table;
                },
                'Push\Model\Dao\ErrorNotificationLogTable' => function($sm){
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ErrorNotificationLog());
                    $tableGateway = new TableGateway('error_notification_log', self::$dbAdapter, null, $resultSetPrototype);
                    $table = new ErrorNotificationLogTable($tableGateway);
                    return $table;
                },
                
                'Zend\Session\SessionManager' => function($sm){
                    $config = $sm->get('Config');
                    $isHttps = function($SVR) {
                        // AWS ElasticLoadBalancer が元のURIスキーマを渡してくるヘッダ
                        $ORIGIN_SCHEME_HEADER = 'HTTP_X_FORWARDED_PROTO';
                    
                        if (isset($SVR[$ORIGIN_SCHEME_HEADER]) && strtolower($SVR[$ORIGIN_SCHEME_HEADER]) === 'https') return true;
                        if (isset($SVR['HTTPS']) && !empty($SVR['HTTPS'])) return true;
                        return false;
                    };
                
                    $sessionConfig = new SessionConfig();
                    $sessionConfig->setOptions(['name' => 'Push']);
                    $sessionConfig->setCookieSecure($isHttps($_SERVER));
                
                    $sessionStorage = new SessionArrayStorage();
                
                    $sessionSaveHandler  = new DbTableGateway(
                        new TableGateway('session', self::$dbAdapter),
                        new DbTableGatewayOptions()
                    );
                
                    $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
                
                    $chain = $sessionManager->getValidatorChain();
                    $chain->attach(
                        'session.validate',
                        array(new RemoteAddr(), 'isValid')
                    );
                    $chain->attach(
                        'session.validate',
                        array(new HttpUserAgent(), 'isValid')
                    );
                
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                }
            ]
        ];
    }
}
