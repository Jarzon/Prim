<?php
namespace Prim;

use PDO;

class Controller implements ViewInterface
{
    /**
     * @var PDO $db
     * @var string $model
     * @var View $view
     */
    public $db;
    public $model;
    public $view;

    /**
     * Whenever controller is created, open a database connection too
     * @param ViewInterface $view
     */
    function __construct(ViewInterface $view)
    {
        if(DB_ENABLE) {
            $this->openDatabaseConnection(DB_TYPE, DB_HOST, DB_NAME, DB_CHARSET, DB_USER, DB_PASS);
        }

        $this->view = $view;
    }

    /**
     * Open a Database connection using PDO
     * @param string $type DBMS
     * @param string $host
     * @param string $name
     * @param string $charset
     * @param string $user
     * @param string $pass
     */
    public function openDatabaseConnection($type, $host, $name, $charset, $user, $pass)
    {
        // Set the fetch mode to object
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        // generate a database connection, using the PDO connector
        $this->db = new PDO($type . ':host=' . $host . ';dbname=' . $name . ';charset=' . $charset, $user, $pass, $options);
    }

    // View Methods shortcut
    function setTemplate($design) {
        $this->view->setTemplate($design);
    }

    function setLanguage($language) {
        $this->view->setLanguage($language);
    }

    function design($view)
    {
        $this->view->design($view);
    }

    function _getTranslation()
    {
        $this->view->_getTranslation();
    }

    function addVar($name, $var) {
        $this->view->addVar($name, $var);
    }

    function addVars($vars) {
        $this->view->addVars($vars);
    }
}