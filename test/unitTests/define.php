<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once 'vendor/autoload.php';

$id = 1;
$_SERVER['CONFIG_DIR'] = "/var/www/html/MaarchParapheur/config/";

class httpRequestCustom
{
    public static function addContentInBody($aArgs, $request)
    {
        $json = json_encode($aArgs);
               
        $stream = fopen('php://memory', 'r+');
        fputs($stream, $json);
        rewind($stream);
        $httpStream = new \Slim\Http\Stream($stream);
        $request = $request->withBody($httpStream);
        $request = $request->withHeader('Content-Type', 'application/json');

        return $request;
    }
}
