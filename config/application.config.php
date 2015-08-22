<?php
return array(
    'modules' => array(
        'Application',
    ),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        'module_paths' => array(
            './module',
            './vendor',
        ),
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
    ),
);
