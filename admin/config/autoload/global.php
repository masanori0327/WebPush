<?php
return [
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
        
    'db' => [
        'push' => [
            'driver'         => 'Pdo',
            'dsn'            => 'mysql:dbname=push;host=localhost',
            'driver_options' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                PDO::MYSQL_ATTR_LOCAL_INFILE => true,
            ],
            'username' => 'root',
            'password' => '',
        ],
    ],
        
    'api' => [
        'google' => [
            'client_id' => '',
            'client_secret' => '',
            'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'callback' => 'http://{domain}/push/user/callback'
        ]
    ]
];
