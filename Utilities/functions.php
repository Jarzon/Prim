<?php

function sanitize($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}