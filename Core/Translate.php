<?php
namespace Prim\Core;

class Translate
{
    /**
     * @var Array Translations
     */
    public $messages = [];

    /**
     * Inject the translations
     */
    function __construct($messages)
    {
        $this->messages = $messages;
    }

    /**
     * Translate
     */
    function _($message)
    {
        return $this->messages[$message];
    }
}