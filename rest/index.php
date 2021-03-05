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
file_put_contents('debug.txt', date('Y-m-d H:i:s') . " - index\n", FILE_APPEND);
date_default_timezone_set(\SrcCore\models\CoreConfigModel::getTimezone());

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true, 'determineRouteBeforeAppMiddleware' => true]]);

//Authentication
$app->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) {
    $configPath = \SrcCore\models\CoreConfigModel::getConfigPath();
    if (!is_file($configPath . '/config.xml')) {
        return $response->withStatus(400)->withJson(['errors' => 'Configuration file is missing']);
    }
    $route = $request->getAttribute('route');
    $currentMethod = empty($route) ? '' : $route->getMethods()[0];
    $currentRoute = empty($route) ? '' : $route->getPattern();

    file_put_contents('debug.txt', $currentRoute."\n", FILE_APPEND);
    if (in_array($currentMethod.$currentRoute, \SrcCore\controllers\AuthenticationController::ROUTES_WITHOUT_AUTHENTICATION)) {
        $response = $next($request, $response);
    } else {
        $authorizationHeaders = $request->getHeader('Authorization');
        $id = \SrcCore\controllers\AuthenticationController::authentication($authorizationHeaders);
        if (!empty($id)) {
            $GLOBALS['id'] = $id;
            if (!empty($currentRoute)) {
                $r = \SrcCore\controllers\AuthenticationController::isRouteAvailable(['userId' => $id, 'currentRoute' => $currentRoute]);
                if (!$r['isRouteAvailable']) {
                    return $response->withStatus(403)->withJson(['errors' => $r['errors']]);
                }
            }
            $response = $next($request, $response);
        } else {
            $response = $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }
    }

    return $response;
});

//Authentication
$app->get('/authenticationInformations', \SrcCore\controllers\AuthenticationController::class . ':getInformations');
$app->post('/authenticate', \SrcCore\controllers\AuthenticationController::class . ':authenticate');
$app->get('/authenticate/token', \SrcCore\controllers\AuthenticationController::class . ':getRefreshedToken');
$app->get('/authenticate/logout', \SrcCore\controllers\AuthenticationController::class . ':logout');

//Attachments
$app->get('/attachments/{id}', \Attachment\controllers\AttachmentController::class . ':getById');
$app->get('/attachments/{id}/thumbnails/{page}', \Attachment\controllers\AttachmentController::class . ':getThumbnailContent');

//AutoComplete
$app->get('/autocomplete/users', \SrcCore\controllers\AutoCompleteController::class . ':getUsers');

//Configurations
$app->get('/configurations', \Configuration\controllers\ConfigurationController::class . ':get');
$app->post('/configurations', \Configuration\controllers\ConfigurationController::class . ':create');
$app->get('/configurations/{id}', \Configuration\controllers\ConfigurationController::class . ':getById');
$app->patch('/configurations/{id}', \Configuration\controllers\ConfigurationController::class . ':update');
$app->delete('/configurations/{id}', \Configuration\controllers\ConfigurationController::class . ':delete');
$app->get('/configurations/{id}/connection', \Configuration\controllers\ConfigurationController::class . ':testConnection');

// CommitInformation
$app->get('/commitInformation', \SrcCore\controllers\AuthenticationController::class . ':getGitCommitInformation');

//Documents
$app->post('/documents', \Document\controllers\DocumentController::class . ':create');
$app->get('/documents', \Document\controllers\DocumentController::class . ':get');
$app->get('/documents/{id}', \Document\controllers\DocumentController::class . ':getById');
$app->get('/documents/{id}/content', \Document\controllers\DocumentController::class . ':getContent');
$app->get('/documents/{id}/proof', \History\controllers\HistoryController::class . ':getHistoryProofByDocumentId');
$app->get('/documents/{id}/history', \History\controllers\HistoryController::class . ':getByDocumentId');
$app->put('/documents/{id}/actions/{actionId}', \Document\controllers\DocumentController::class . ':setAction');
$app->get('/documents/{id}/workflow', \Workflow\controllers\WorkflowController::class . ':getByDocumentId');
$app->get('/documents/{id}/linkedMailing', \Document\controllers\DocumentController::class . ':getLinkedMailing');
$app->get('/documents/{id}/thumbnails/{page}', \Document\controllers\DocumentController::class . ':getThumbnailContent');
$app->put('/documents/{id}/workflows/interrupt', \Workflow\controllers\WorkflowController::class . ':interrupt');

//Emails
$app->post('/emails', \Email\controllers\EmailController::class . ':send');

//Groups
$app->post('/groups', \Group\controllers\GroupController::class . ':create');
$app->get('/groups', \Group\controllers\GroupController::class . ':get');
$app->get('/groups/{id}', \Group\controllers\GroupController::class . ':getById');
$app->delete('/groups/{id}', \Group\controllers\GroupController::class . ':delete');
$app->put('/groups/{id}', \Group\controllers\GroupController::class . ':update');
$app->put('/groups/{id}/privilege/{privilegeId}', \Group\controllers\GroupController::class . ':updateGroupPrivilege');
$app->put('/groups/{id}/users', \Group\controllers\GroupController::class . ':addUser');
$app->delete('/groups/{id}/users/{userId}', \Group\controllers\GroupController::class . ':removeUser');

//History
$app->post('/history', \History\controllers\HistoryController::class . ':get');
$app->get('/history/messageTypes', \History\controllers\HistoryController::class . ':getMessageTypes');

//Languages
$app->get('/languages', \SrcCore\controllers\LanguageController::class . ':getAvailableCoreLanguages');
$app->get('/languages/{lang}', \SrcCore\controllers\LanguageController::class . ':getByLang');
$app->put('/languages', \SrcCore\controllers\LanguageController::class . ':generateLang');

//PasswordRules
$app->get('/passwordRules', \SrcCore\controllers\PasswordController::class . ':get');
$app->put('/passwordRules', \SrcCore\controllers\PasswordController::class . ':updateRules');

//Users
$app->post('/users', \User\controllers\UserController::class . ':create');
$app->get('/users', \User\controllers\UserController::class . ':get');
$app->get('/users/{id}', \User\controllers\UserController::class . ':getById');
$app->put('/users/{id}', \User\controllers\UserController::class . ':update');
$app->delete('/users/{id}', \User\controllers\UserController::class . ':delete');
$app->get('/users/{id}/picture', \User\controllers\UserController::class . ':getPictureById');
$app->put('/users/{id}/picture', \User\controllers\UserController::class . ':updatePicture');
$app->get('/users/{id}/substitute', \User\controllers\UserController::class . ':getSubstituteById');
$app->put('/users/{id}/preferences', \User\controllers\UserController::class . ':updatePreferences');
$app->put('/users/{id}/substitute', \User\controllers\UserController::class . ':updateSubstitute');
$app->put('/users/{id}/password', \User\controllers\UserController::class . ':updatePassword');
$app->get('/users/{id}/history', \History\controllers\HistoryController::class . ':getByUserId');
$app->post('/password', \User\controllers\UserController::class . ':forgotPassword');
$app->put('/password', \User\controllers\UserController::class . ':updateForgottenPassword');

//Search
$app->post('/search/documents', \Search\controllers\SearchController::class . ':getDocuments');

//Signatures
$app->get('/users/{id}/signatures', \User\controllers\SignatureController::class . ':get');
$app->post('/users/{id}/signatures', \User\controllers\SignatureController::class . ':create');
$app->delete('/users/{id}/signatures/{signatureId}', \User\controllers\SignatureController::class . ':delete');
$app->put('/users/{id}/externalSignatures', \User\controllers\SignatureController::class . ':updateExternalSignatures');
$app->patch('/users/{id}/signatures/{signatureId}/substituted', \User\controllers\SignatureController::class . ':updateSubstituted');
$app->get('/signatureModes', \User\controllers\SignatureController::class . ':getSignatureModes');

//WorkflowTemplates
$app->post('/workflowTemplates', \Workflow\controllers\WorkflowTemplateController::class . ':create');
$app->get('/workflowTemplates', \Workflow\controllers\WorkflowTemplateController::class . ':get');
$app->get('/workflowTemplates/{id}', \Workflow\controllers\WorkflowTemplateController::class . ':getById');
$app->delete('/workflowTemplates/{id}', \Workflow\controllers\WorkflowTemplateController::class . ':delete');

$app->run();
