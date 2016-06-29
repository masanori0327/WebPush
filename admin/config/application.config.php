<?php
return array(
    'modules' => array(
        'Application',
        'Push',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            dirname(__FILE__) . '/../module',
            dirname(__FILE__) . '/../vendor'
        ),
        'config_glob_paths' => array(
            dirname(__FILE__) . '/autoload/{,*.}{global,local}.php'
        )
    )
);
