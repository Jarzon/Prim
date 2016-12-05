<?php
namespace Prim;

use PDO;

class Controller
{
    public $db = null;
    public $design = 'design';
    public $language = 'en';
    public $model = null;
    public $messages = [];
    public $vars = [];

    /**
     * Whenever controller is created, open a database connection too
     */
    function __construct()
    {
        if(DB_ENABLE) {
            $this->openDatabaseConnection(DB_TYPE, DB_HOST, DB_NAME, DB_CHARSET, DB_USER, DB_PASS);
        }
    }

    /**
     * Set the default template
     * @param string $design
     */
    function setTemplate($design) {
        $this->design = $design;
    }

    /**
     * Set the language
     * @param string $language
     */
    function setLanguage($language) {
        $this->language = $language;
    }

    /**
     * Fetch the template design to show the view in
     * @param string $view
     */
    function design($view)
    {
        // Create the view vars
        if(!empty($this->vars)) extract($this->vars);

        $_ = function($message) {
            return $this->messages[$message];
        };

        require '../src/view/_templates/'.$this->design.'.php';
    }

    /**
     * Fetch a translation file and return an array that contain the messages
     */
    protected function _getTranslation()
    {
        $file = '../app/messages/'.$this->language.'.json';

        // Check if we have a translation file for that language
        if (!file_exists($file)) {
            $file = 'en';
        }

        // TODO: Cache the file
        $this->messages = json_decode(file_get_contents($file), true);
    }

    /**
     * Add a var for the view
     * @param string $name
     * @param mixed $var
     */
    function addVar($name, $var) {
        $this->vars[$name] = $var;
    }

    /**
     * Add a vars for the view
     * @param array $vars
     */
    function addVars($vars) {
        foreach($vars as $var) {
            $this->addVar($var[0], $var[1]);
        }
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
}