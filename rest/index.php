<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Rest Routes File
* @author dev@maarch.org
*/

require '../vendor/autoload.php';

//Root application position
chdir('..');
date_default_timezone_set(\SrcCore\models\CoreConfigModel::getTimezone());

$language = \SrcCore\models\CoreConfigModel::getLanguage();
require_once("src/core/lang/lang-{$language}.php");


$app = new \Slim\App(['settings' => ['displayErrorDetails' => true, 'determineRouteBeforeAppMiddleware' => true]]);

//Authentication
$app->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) {
    $routesWithoutAuthentication = ['POST/log'];
    $route = $request->getAttribute('route');
    $currentMethod = empty($route) ? '' : $route->getMethods()[0];
    $currentRoute = empty($route) ? '' : $route->getPattern();

    if (in_array($currentMethod.$currentRoute, $routesWithoutAuthentication)) {
        $response = $next($request, $response);
    } else {
        $email = \SrcCore\controllers\AuthenticationController::authentication();
        if (!empty($email)) {
            $GLOBALS['email'] = $email;
            $response = $next($request, $response);
        } else {
            $response = $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }
    }

    return $response;
});

//Authentication
$app->post('/log', \SrcCore\controllers\AuthenticationController::class . ':log');

//Attachments
$app->get('/attachments/{id}', \Attachment\controllers\AttachmentController::class . ':getById');

//Documents
$app->get('/documents', \Document\controllers\DocumentController::class . ':get');
$app->get('/documents/{id}', \Document\controllers\DocumentController::class . ':getById');
$app->put('/documents/{id}/action', \Document\controllers\DocumentController::class . ':makeAction');

//Users
$app->get('/users', \User\controllers\UserController::class . ':get');
$app->get('/users/{id}/signatures', \User\controllers\UserController::class . ':getSignatures');
$app->post('/users/{id}/signatures', \User\controllers\UserController::class . ':createSignature');
$app->delete('/users/{id}/signatures/{signatureId}', \User\controllers\UserController::class . ':deleteSignature');

$app->run();
