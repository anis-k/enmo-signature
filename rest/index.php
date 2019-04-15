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

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true, 'determineRouteBeforeAppMiddleware' => true]]);

//Authentication
$app->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) {
    $configPath = \SrcCore\models\CoreConfigModel::getConfigPath();
    if (!is_file($configPath . '/config.xml')) {
        return $response->withStatus(400)->withJson(['errors' => 'Configuration file is missing']);
    }
    $routesWithoutAuthentication = ['POST/log', 'POST/password', 'PUT/log/password'];
    $route = $request->getAttribute('route');
    $currentMethod = empty($route) ? '' : $route->getMethods()[0];
    $currentRoute = empty($route) ? '' : $route->getPattern();

    if (in_array($currentMethod.$currentRoute, $routesWithoutAuthentication)) {
        $response = $next($request, $response);
    } else {
        $id = \SrcCore\controllers\AuthenticationController::authentication();
        if (!empty($id)) {
            $GLOBALS['id'] = $id;
            $response = $next($request, $response);
        } else {
            \SrcCore\models\AuthenticationModel::deleteCookieAuth();
            $response = $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }
    }

    return $response;
});

//Authentication
$app->post('/log', \SrcCore\controllers\AuthenticationController::class . ':log');
$app->get('/logout', \SrcCore\controllers\AuthenticationController::class . ':logout');

//Actions
$app->get('/actions', \Action\controllers\ActionController::class . ':get');

//Attachments
$app->get('/attachments/{id}', \Attachment\controllers\AttachmentController::class . ':getById');

//Configurations
$app->put('/configurations/{identifier}', \Configuration\controllers\ConfigurationController::class . ':update');

//Documents
$app->post('/documents', \Document\controllers\DocumentController::class . ':create');
$app->get('/documents', \Document\controllers\DocumentController::class . ':get');
$app->get('/documents/{id}', \Document\controllers\DocumentController::class . ':getById');
$app->get('/documents/{id}/status', \Document\controllers\DocumentController::class . ':getStatusById');
$app->get('/documents/{id}/processedDocument', \Document\controllers\DocumentController::class . ':getProcessedDocumentById');
$app->get('/documents/{id}/history', \History\controllers\HistoryController::class . ':getByDocumentId');
$app->put('/documents/{id}/actions/{actionId}', \Document\controllers\DocumentController::class . ':setAction');

//Password Rules
$app->get('/passwordRules', \SrcCore\controllers\PasswordController::class . ':get');

//Users
$app->post('/users', \User\controllers\UserController::class . ':create');
$app->get('/users', \User\controllers\UserController::class . ':get');
$app->get('/users/{id}', \User\controllers\UserController::class . ':getById');
$app->put('/users/{id}', \User\controllers\UserController::class . ':update');
$app->put('/users/{id}/password', \User\controllers\UserController::class . ':updatePassword');
$app->get('/users/{id}/signatures', \User\controllers\UserController::class . ':getSignatures');
$app->post('/users/{id}/signatures', \User\controllers\UserController::class . ':createSignature');
$app->delete('/users/{id}/signatures/{signatureId}', \User\controllers\UserController::class . ':deleteSignature');

$app->post('/password', \User\controllers\UserController::class . ':forgotPassword');
$app->put('/password', \User\controllers\UserController::class . ':updateForgottenPassword');

$app->run();
