<?php
/**
 * SendMeJokes (http://www.sendmejokes.com/)
 *
 * @author    Mike Buonomo <mike@sendmjokes.com>
 * @link      https://github.com/mrb2590/sendmejokes
 */

namespace Application;

use Application\Model\Joke;
use Application\Model\JokeTable;
use Application\Model\User;
use Application\Model\UserTable;
use Application\Model\Category;
use Application\Model\CategoryTable;
use Application\Model\Vote;
use Application\Model\VoteTable;
use Application\Model\UserCategory;
use Application\Model\UserCategoriesTable;
use Application\Model\UserDay;
use Application\Model\UserDaysTable;
use Application\Model\UserExcludeCategory;
use Application\Model\UserExcludeCategoriesTable;
use Application\Model\JokeCategory;
use Application\Model\JokeCategoriesTable;
use Application\Model\ViewUserCategory;
use Application\Model\ViewUserCategoriesTable;
use Application\Model\ViewJokeCategory;
use Application\Model\ViewJokeCategoriesTable;
use Application\Model\UserSentJoke;
use Application\Model\UserSentJokesTable;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    /**
     * @param Zend\Mvc\MvcEvent
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $serviceManager      = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);    }

    /**
     * @param Zend\Mvc\MvcEvent
     */
    public function bootstrapSession($e)
    {
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\Session\SessionManager');
        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
             $session->regenerateId(true);
             $container->init = 1;
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Zend\Session\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            // class should be fetched from service manager since it will require constructor arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                        if (isset($session['validators'])) {
                            $chain = $sessionManager->getValidatorChain();
                            foreach ($session['validators'] as $validator) {
                                $validator = new $validator();
                                $chain->attach('session.validate', array($validator, 'isValid'));

                            }
                        }
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
                'Application\Model\UserTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\JokeTable' =>  function($sm) {
                    $tableGateway = $sm->get('JokeTableGateway');
                    $table = new JokeTable($tableGateway);
                    return $table;
                },
                'JokeTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Joke());
                    return new TableGateway('joke', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\CategoryTable' =>  function($sm) {
                    $tableGateway = $sm->get('CategoryTableGateway');
                    $table = new CategoryTable($tableGateway);
                    return $table;
                },
                'CategoryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Category());
                    return new TableGateway('category', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\UserCategoriesTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserCategoriesTableGateway');
                    $table = new UserCategoriesTable($tableGateway);
                    return $table;
                },
                'UserCategoriesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserCategory());
                    return new TableGateway('user_categories', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\UserDaysTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserDaysTableGateway');
                    $table = new UserDaysTable($tableGateway);
                    return $table;
                },
                'UserDaysTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserDay());
                    return new TableGateway('user_days', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\UserExcludeCategoriesTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserExcludeCategoriesTableGateway');
                    $table = new UserExcludeCategoriesTable($tableGateway);
                    return $table;
                },
                'UserExcludeCategoriesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserExcludeCategory());
                    return new TableGateway('user_exclude_categories', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\JokeCategoriesTable' =>  function($sm) {
                    $tableGateway = $sm->get('JokeCategoriesTableGateway');
                    $table = new JokeCategoriesTable($tableGateway);
                    return $table;
                },
                'JokeCategoriesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new JokeCategory());
                    return new TableGateway('joke_categories', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\ViewUserCategoriesTable' =>  function($sm) {
                    $tableGateway = $sm->get('ViewUserCategoriesTableGateway');
                    $table = new ViewUserCategoriesTable($tableGateway);
                    return $table;
                },
                'ViewUserCategoriesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ViewUserCategory());
                    return new TableGateway('view_user_categories', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\ViewJokeCategoriesTable' =>  function($sm) {
                    $tableGateway = $sm->get('ViewJokeCategoriesTableGateway');
                    $table = new ViewJokeCategoriesTable($tableGateway);
                    return $table;
                },
                'ViewJokeCategoriesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ViewJokeCategory());
                    return new TableGateway('view_joke_categories', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\VoteTable' =>  function($sm) {
                    $tableGateway = $sm->get('VoteTableGateway');
                    $table = new VoteTable($tableGateway);
                    return $table;
                },
                'VoteTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Vote());
                    return new TableGateway('vote', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\UserSentJokesTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserSentJokesTableGateway');
                    $table = new UserSentJokesTable($tableGateway);
                    return $table;
                },
                'UserSentJokesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserSentJoke());
                    return new TableGateway('user_sent_jokes', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
