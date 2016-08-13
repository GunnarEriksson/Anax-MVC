<?php

namespace Anax\DI;

/**
 * Anax extended class implementing Dependency Injection / Service Locator
 * of the services used by the framework, using lazy loading.
 */
class CDIFactory extends CDIFactoryDefault
{
    public function __construct()
    {
        parent::__construct();

        $this->set('form', '\Mos\HTMLForm\CForm');

        $this->set('CommentController', function () {
            $controller = new \Anax\Comment\CommentController();
            $controller->setDI($this);
            return $controller;
        });

        $this->setShared('db', function () {
            $db = new \Mos\Database\CDatabaseBasic();
            $db->setOptions(require ANAX_APP_PATH . 'config/database_mysql.php');
            $db->connect();
            return $db;
        });

        $this->set('UsersController', function () {
            $controller = new \Anax\Users\UsersController();
            $controller->setDI($this);
            return $controller;
        });

        $this->setShared('logger', function () {
            $logger = new \Toeswade\Log\Clog();
            return $logger;
        });
    }
}
