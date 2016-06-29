<?php
return array(
   'service_manager' => array(
       'abstract_factories' => array(
           'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
           'Zend\Log\LoggerAbstractServiceFactory',
       ),
   ),
   'controllers' => array(
       'invokables' => array(
       ),
   ),
   'view_manager' => array(
       'display_not_found_reason' => true,
       'display_exceptions'       => true,
       'doctype'                  => 'HTML5',
       'not_found_template'       => 'error/404',
       'exception_template'       => 'error/index',
       'template_map' => array(
           'layout/application'   => __DIR__ . '/../../Push/view/layout/layout.phtml',
           'error/404'            => __DIR__ . '/../view/error/404.phtml',
           'error/index'          => __DIR__ . '/../view/error/index.phtml',
       ),
       'template_path_stack' => array(
           __DIR__ . '/../view',
       ),
   ),
   'module_layouts' => array(
       'Application' => 'layout/application'
   ),
);