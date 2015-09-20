<?php
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Application',
                        'action'     => 'coming-soon',
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
                    'route'    => '/user[/[:action[/[:user_id[/]]]]]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'user_id' => '\d{8}',
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
                    'route'    => '/jokes[/[:action[/[:category[/]]]]]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'category' => '\d{8}',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Joke',
                        'action'     => 'view',
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
