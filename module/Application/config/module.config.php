<?php
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action'     => 'sign-up',
                    ),
                ),
            ),
            'application' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '[/:action[/]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Application',
                        'action'     => 'home',
                    ),
                ),
            ),
            'user' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/user[/[:action[/[:user_id[/]][:reset_pass_id[/]]]]]',
                    'constraints' => array(
                        'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'user_id'       => '[a-zA-Z0-9]{13}',
                        'reset_pass_id' => '[a-zA-Z0-9]{64}',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action'     => 'view',
                    ),
                ),
            ),
            'joke' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/jokes[/[:action[/[:category[/]][:joke_id[/]]]]]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'category' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'joke_id'  => '\d{8}',
                        'search' => '.*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Joke',
                        'action'     => 'view',
                    ),
                ),
            ),
            'search' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/search[/]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Joke',
                        'action'     => 'view',
                    ),
                ),
            ),
        ),
    ),
    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'sendmejokes',
                'remember_me_seconds' => 300,
                'use_cookies' => true,
                'cookie_httponly' => true,
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Application' => 'Application\Controller\ApplicationController',
            'Application\Controller\User'        => 'Application\Controller\UserController',
            'Application\Controller\Joke'        => 'Application\Controller\JokeController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => ($_SERVER['APPLICATION_ENV'] == 'development') ? true : false,
        'display_exceptions'       => ($_SERVER['APPLICATION_ENV'] == 'development') ? true : false,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'sidebar'                => 'Application\View\Helper\Sidebar',
            'updatePreferencesModal' => 'Application\View\Helper\UpdatePreferencesModal',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
