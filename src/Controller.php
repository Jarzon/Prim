<?php
namespace Prim;

use \PDO;

class Controller implements ViewInterface
{
    /**
     * @var PDO $db
     * @var string $model
     * @var ViewInterface $view
     */
    public $db;
    public $model;
    public $view;

    /**
     * Whenever controller is created, open a database connection too
     */
    function __construct(ViewInterface $view)
    {
        if(DB_ENABLE) {
            $this->openDatabaseConnection(DB_TYPE, DB_HOST, DB_NAME, DB_CHARSET, DB_USER, DB_PASS);
        }

        $this->view = $view;

        $this->_getTranslation();
    }

    /**
     * Open a Database connection using PDO
     */
    public function openDatabaseConnection(string $type, string $host, string $name, string $charset, string $user, string $pass)
    {
        // Set the fetch mode to object
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        // generate a database connection, using the PDO connector
        $this->db = new PDO("$type:host=$host;dbname=$name;charset=$charset", $user, $pass, $options);
    }

    // View Methods shortcut
    function setTemplate(string $design) {
        $this->view->setTemplate($design);
    }

    function setLanguage(string $language) {
        $this->view->setLanguage($language);
    }

    function design(string $view)
    {
        $this->view->design($view);
    }

    function _getTranslation()
    {
        $this->view->_getTranslation();
    }

    function addVar(string $name, $var) {
        $this->view->addVar($name, $var);
    }

    function addVars(array $vars) {
        $this->view->addVars($vars);
    }
}