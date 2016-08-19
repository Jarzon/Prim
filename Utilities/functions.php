<?php
use Prim\Utilities\Helper;

function sanitize($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function PDO_debug($sql, $parameters)
{
    echo '[ PDO DEBUG ]: ' . Helper::debugPDO($sql, $parameters);
    exit();
}