<?php
return array(
   'view_manager' => array(
       'template_path_stack' => array(
            dirname(__FILE__) . '/../view',
       ),
       'strategies' => array(
           'ViewJsonStrategy',
       ),
   ),
   'module_layouts' => array(
       'Push' => 'layout/push'
   ),

   'router' => array(
       'routes' => array(
            'push' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/push[/][:controller[/:action[/][/:tableKey0[/:tableKey1[/:tableKey2]]]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tableKey0' => '[a-z0-9]{20}',
                        'tableKey1' => '[a-z0-9]{20}',
                        'tableKey2' => '[a-z0-9]{20}',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Push\Controller',
                        'controller'    => 'Push\Controller\Index',
                        'action'        => 'index',
                    ),
                ),
            ),
       ),
   ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'tableOptimize' => array(
                    'options' => array(
                        'route'    => 'tb',
                        'defaults' => array(
                            'controller' => 'Push\Controller\Batch',
                            'action'     => 'tableOptimize'
                        ),
                    ),
                ),
                'sendPush' => array(
                    'options' => array(
                        'route'    => 'sp [--sendId=] [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'Push\Controller\Batch',
                            'action'     => 'sendPush'
                        ),
                    ),
                ),
                'rgeocode' => array(
                    'options' => array(
                        'route'    => 'rg',
                        'defaults' => array(
                            'controller' => 'Push\Controller\Batch',
                            'action'     => 'rgeocode'
                        ),
                    ),
                ),
            ),
        ),
    ),
        
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
    ),
    
    'view_manager' => array(
        'template_map' => array(
            'layout/push' => dirname(__FILE__) . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
