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
    $login = \SrcCore\controllers\AuthenticationController::authentication();

    if (!empty($login)) {
        $GLOBALS['login'] = $login;
        $response = $next($request, $response);
        return $response;
    } else {
        return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
    }
});


//Documents
$app->get('/documents', \Document\controllers\DocumentController::class . ':get');


////Ressources
//$app->post('/res', \Resource\controllers\ResController::class . ':create');
//$app->post('/resExt', \Resource\controllers\ResController::class . ':createExt');
//$app->get('/res/{resId}/thumbnail', \Resource\controllers\ResController::class . ':getThumbnailContent');
//$app->put('/res/resource/status', \Resource\controllers\ResController::class . ':updateStatus');
//$app->post('/res/list', \Resource\controllers\ResController::class . ':getList');
//$app->get('/res/{resId}/lock', \Resource\controllers\ResController::class . ':isLock');
//$app->get('/res/{resId}/notes/count', \Resource\controllers\ResController::class . ':getNotesCountForCurrentUserById');
//$app->put('/res/externalInfos', \Resource\controllers\ResController::class . ':updateExternalInfos');
//$app->get('/categories', \Resource\controllers\ResController::class . ':getCategories');
//$app->get('/natures', \Resource\controllers\ResController::class . ':getNatures');
//$app->get('/resources/groups/{groupSerialId}/baskets/{basketId}', \Resource\controllers\ResController::class . ':getResourcesByBasket');
//
////Attachments
//$app->post('/attachments', \Attachment\controllers\AttachmentController::class . ':create');
//$app->get('/res/{resId}/attachments', \Attachment\controllers\AttachmentController::class . ':getAttachmentsListById');
//$app->get('/res/{resIdMaster}/attachments/{resId}/content', \Attachment\controllers\AttachmentController::class . ':getFileContent');
//$app->get('/res/{resIdMaster}/attachments/{resId}/thumbnail', \Attachment\controllers\AttachmentController::class . ':getThumbnailContent');
//
////SignatureBook
//$app->get('/{basketId}/signatureBook/resList', \SignatureBook\controllers\SignatureBookController::class . ':getResList');
//$app->get('/{basketId}/signatureBook/resList/details', \SignatureBook\controllers\SignatureBookController::class . ':getDetailledResList');
//$app->get('/groups/{groupId}/baskets/{basketId}/signatureBook/{resId}', \SignatureBook\controllers\SignatureBookController::class . ':getSignatureBook');
//$app->get('/signatureBook/{resId}/attachments', \SignatureBook\controllers\SignatureBookController::class . ':getAttachmentsById');
//$app->get('/signatureBook/{resId}/incomingMailAttachments', \SignatureBook\controllers\SignatureBookController::class . ':getIncomingMailAndAttachmentsById');
//$app->put('/signatureBook/{resId}/unsign', \SignatureBook\controllers\SignatureBookController::class . ':unsignFile');
//$app->put('/attachments/{id}/inSignatureBook', \Attachment\controllers\AttachmentController::class . ':setInSignatureBook');
//
//
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
//
////CurrentUser
//$app->get('/currentUser/profile', \User\controllers\UserController::class . ':getProfile');
//$app->put('/currentUser/profile', \User\controllers\UserController::class . ':updateProfile');
//$app->put('/currentUser/password', \User\controllers\UserController::class . ':updateCurrentUserPassword');
//$app->post('/currentUser/emailSignature', \User\controllers\UserController::class . ':createCurrentUserEmailSignature');
//$app->put('/currentUser/emailSignature/{id}', \User\controllers\UserController::class . ':updateCurrentUserEmailSignature');
//$app->delete('/currentUser/emailSignature/{id}', \User\controllers\UserController::class . ':deleteCurrentUserEmailSignature');
//$app->put('/currentUser/groups/{groupId}/baskets/{basketId}', \User\controllers\UserController::class . ':updateCurrentUserBasketPreferences');

$app->run();
