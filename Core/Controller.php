<?php
namespace Prim\Core;

class Controller
{
    /**
     * @var null Database Connection
     */
    public $db = null;
    
    /**
     * @var Design
     */
    public $design = 'design';

    /**
     * @var null Model
     */
    public $model = null;

    /**
     * Whenever controller is created, open a database connection too and load "the model".
     */
    function __construct()
    {
        if(DB_ENABLE) {
            $this->openDatabaseConnection();
        }
    }

    function design($view, $t = null)
    {
        require APP . 'view/_templates/'.$this->design.'.php';
    }

    /**
     * A translation method
     */
    protected function _getTranslation($language = 'en')
    {
        $yaml = new \Symfony\Component\Yaml\Parser();

        //Check if we have a translation file for that language
        if (file_exists(APP . 'messages/'.$language.'.yaml')) {
            // TODO: Cache the file, in apc
            $messages =  $yaml->parse(file_get_contents(APP . 'messages/'.$language.'.yaml'));
        } else {
            $messages =  $yaml->parse(file_get_contents(APP . 'messages/en.yaml'));
        }

        return $messages;
    }

    /**
     * Open the database connection with the credentials from application/config/config.php
     */
    private function openDatabaseConnection()
    {
        // set the (optional) options of the PDO connection. in this case, we set the fetch mode to
        // "objects", which means all results will be objects, like this: $result->user_name !
        // For example, fetch mode FETCH_ASSOC would return results like this: $result["user_name] !
        // @see http://www.php.net/manual/en/pdostatement.fetch.php
        $options = array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING);

        // generate a database connection, using the PDO connector
        // @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
        $this->db = new \PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, $options);
    }
}