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

$GLOBALS['login'] = 'jjane';
//Authentication
/*$app->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) {
    $login = \SrcCore\controllers\AuthenticationController::authentication();

    if (!empty($login)) {
        $GLOBALS['login'] = $login;
        $response = $next($request, $response);
        return $response;
    } else {
        return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
    }
});*/


//Attachments
$app->get('/attachments/{id}', \Attachment\controllers\AttachmentController::class . ':getById');

//Documents
$app->get('/documents', \Document\controllers\DocumentController::class . ':get');
$app->get('/documents/{id}', \Document\controllers\DocumentController::class . ':getById');


////Users
//$app->get('/users', \User\controllers\UserController::class . ':get');
//$app->post('/users', \User\controllers\UserController::class . ':create');
//$app->get('/users/{id}/details', \User\controllers\UserController::class . ':getDetailledById');
//$app->put('/users/{id}', \User\controllers\UserController::class . ':update');
//$app->put('/users/{id}/password', \User\controllers\UserController::class . ':resetPassword');
//$app->get('/users/{userId}/status', \User\controllers\UserController::class . ':getStatusByUserId');
//$app->put('/users/{id}/status', \User\controllers\UserController::class . ':updateStatus');
//$app->delete('/users/{id}', \User\controllers\UserController::class . ':delete');
//$app->post('/users/{id}/groups', \User\controllers\UserController::class . ':addGroup');
//$app->put('/users/{id}/groups/{groupId}', \User\controllers\UserController::class . ':updateGroup');
//$app->delete('/users/{id}/groups/{groupId}', \User\controllers\UserController::class . ':deleteGroup');
//$app->post('/users/{id}/entities', \User\controllers\UserController::class . ':addEntity');
//$app->put('/users/{id}/entities/{entityId}', \User\controllers\UserController::class . ':updateEntity');
//$app->put('/users/{id}/entities/{entityId}/primaryEntity', \User\controllers\UserController::class . ':updatePrimaryEntity');
//$app->get('/users/{id}/entities/{entityId}', \User\controllers\UserController::class . ':isEntityDeletable');
//$app->delete('/users/{id}/entities/{entityId}', \User\controllers\UserController::class . ':deleteEntity');
//$app->post('/users/{id}/signatures', \User\controllers\UserController::class . ':addSignature');
//$app->get('/users/{id}/signatures/{signatureId}/content', \User\controllers\UserController::class . ':getImageContent');
//$app->put('/users/{id}/signatures/{signatureId}', \User\controllers\UserController::class . ':updateSignature');
//$app->delete('/users/{id}/signatures/{signatureId}', \User\controllers\UserController::class . ':deleteSignature');
//$app->post('/users/{id}/redirectedBaskets', \User\controllers\UserController::class . ':setRedirectedBaskets');
//$app->delete('/users/{id}/redirectedBaskets/{basketId}', \User\controllers\UserController::class . ':deleteRedirectedBaskets');
//$app->put('/users/{id}/baskets', \User\controllers\UserController::class . ':updateBasketsDisplay');

$app->run();
