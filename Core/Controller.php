<?php
namespace Prim\Core;

class Controller
{
    public $db = null;
    public $design = '';
    public $model = null;

    /**
     * Whenever controller is created, open a database connection too
     */
    function __construct()
    {
        $this->design = 'design';

        if(DB_ENABLE) {
            $this->openDatabaseConnection();
        }
    }

    function design($view, $t = null)
    {
        require '../src/view/_templates/'.$this->design.'.php';
    }

    /**
     * A translation method
     */
    protected function _getTranslation($language = 'en')
    {
        $yaml = new \Symfony\Component\Yaml\Parser();
        $file = '../messages/'.$language.'.yaml';

        //Check if we have a translation file for that language
        if (file_exists($file)) {
            // TODO: Cache the file
            $messages =  $yaml->parse(file_get_contents($file));
        } else {
            $messages =  $yaml->parse(file_get_contents('../messages/en.yaml'));
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