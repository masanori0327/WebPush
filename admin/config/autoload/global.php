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
            'client_id' => '20214841620-dbuut55uv1ln139v5pgsr2op1eeguv27.apps.googleusercontent.com',
            'client_secret' => 'n0t7-C-14E4E8ZBJfL0b4ruQ',
            'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'callback' => 'http://push2.jp/push/user/callback'
        ]
    ]
];
