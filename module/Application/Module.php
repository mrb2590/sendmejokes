<?php
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
use Application\Model\JokeCategory;
use Application\Model\JokeCategoriesTable;
use Application\Model\ViewUserCategory;
use Application\Model\ViewUserCategoriesTable;
use Application\Model\ViewJokeCategory;
use Application\Model\ViewJokeCategoriesTable;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
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

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
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
            ),
        );
    }
}
