<?php

/**
 * Copyright Maarch since 2008 under license.
 * See LICENSE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Email Controller
 * @author dev@maarch.org
 */

namespace Email\controllers;

use Configuration\models\ConfigurationModel;
use Document\models\DocumentModel;
use Email\models\EmailModel;
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use PHPMailer\PHPMailer\PHPMailer;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\LanguageController;
use SrcCore\controllers\UrlController;
use SrcCore\models\AuthenticationModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;
use Workflow\models\WorkflowModel;

class EmailController
{
    public static function send(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_email_configuration'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        $isSent = EmailController::createEmail(['userId' => $GLOBALS['id'], 'data' => $body]);

        if (!empty($isSent['errors'])) {
            if (!empty($isSent['code'])) {
                return $response->withStatus($isSent['code'])->withJson(['errors' => $isSent['errors']]);
            }
            return $response->withJson(['isSent' => false, 'informations' => $isSent['errors']]);
        }

        return $response->withJson(['isSent' => true, 'informations' => 'success']);
    }

    public static function createEmail(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'data']);
        ValidatorModel::intVal($args, ['userId']);
        ValidatorModel::arrayType($args, ['data']);

        $check = EmailController::controlCreateEmail($args['data']);
        if (!empty($check['errors'])) {
            return ['errors' => $check['errors'], 'code' => $check['code']];
        }

        $id = EmailModel::create([
            'user_id'      => $args['userId'],
            'sender'       => $args['data']['sender'],
            'recipients'   => json_encode($args['data']['recipients']),
            'cc'           => empty($args['data']['cc']) ? '[]' : json_encode($args['data']['cc']),
            'cci'          => empty($args['data']['cci']) ? '[]' : json_encode($args['data']['cci']),
            'subject'      => empty($args['data']['subject']) ? null : $args['data']['subject'],
            'body'         => empty($args['data']['body']) ? null : $args['data']['body'],
            'document'     => null,
            'isHtml'       => $args['data']['isHtml'] ? 'true' : 'false',
            'status'       => 'WAITING'
        ]);

        $subject = empty($args['data']['subject']) ? '{emailNoSubject}' : $args['data']['subject'];
        HistoryController::add([
            'code'       => 'OK',
            'objectType' => 'emails',
            'objectId'   => $id,
            'type'       => 'CREATION',
            'message'    => "{emailAdded} : {$subject}"
        ]);

        if (!empty($args['data']['status']) && $args['data']['status'] == 'EXPRESS') {
            $isSent = EmailController::sendEmail(['emailId' => $id]);
            if (!empty($isSent['success'])) {
                EmailModel::update(['set' => ['status' => 'SENT', 'send_date' => 'CURRENT_TIMESTAMP'], 'where' => ['id = ?'], 'data' => [$id]]);
            } else {
                EmailModel::update(['set' => ['status' => 'ERROR'], 'where' => ['id = ?'], 'data' => [$id]]);
                HistoryController::add([
                    'code'        => 'KO',
                    'objectType'  => 'emails',
                    'objectId'    => $id,
                    'type'        => 'EMAIL',
                    'message'     => '{emailFailed}',
                    'data'        => ['errors' => $isSent['errors']]
                ]);
            }
        } else {
            $configPath = CoreConfigModel::getConfigPath();
            $encryptKey = CoreConfigModel::getEncryptKey();
            exec("php src/app/email/scripts/sendEmail.php '{$configPath}' {$id} {$args['userId']} '{$encryptKey}' > /dev/null &");
            $isSent = ['success' => 'success'];
        }

        return $isSent;
    }

    public static function sendEmail(array $args)
    {
        ValidatorModel::notEmpty($args, ['emailId']);
        ValidatorModel::intVal($args, ['emailId']);

        $email = EmailModel::getById(['id' => $args['emailId']]);
        $email['recipients']  = json_decode($email['recipients']);
        $email['cc']          = json_decode($email['cc']);
        $email['cci']         = json_decode($email['cci']);

        $configuration = ConfigurationModel::getByIdentifier(['identifier' => 'emailServer', 'select' => ['value']]);
        $configuration = json_decode($configuration[0]['value'], true);
        if (empty($configuration)) {
            return ['errors' => 'Configuration is missing'];
        }

        $phpmailer = new PHPMailer();
        $phpmailer->setFrom($configuration['from']);
        if ($configuration['type'] == 'smtp') {
            $phpmailer->isSMTP();
            $phpmailer->Host        = $configuration['host'];
            $phpmailer->Port        = $configuration['port'];
            $phpmailer->SMTPAutoTLS = false;
            if (!empty($configuration['secure'])) {
                $phpmailer->SMTPSecure = $configuration['secure'];
            }
            $phpmailer->SMTPAuth = $configuration['auth'];
            if ($configuration['auth']) {
                $phpmailer->Username = $configuration['user'];
                if (!empty($configuration['password'])) {
                    $phpmailer->Password = AuthenticationModel::decrypt(['cryptedPassword' => $configuration['password']]);
                }
            }
        } elseif ($configuration['type'] == 'sendmail') {
            $phpmailer->isSendmail();
        } elseif ($configuration['type'] == 'qmail') {
            $phpmailer->isQmail();
        } elseif ($configuration['type'] == 'mail') {
            $phpmailer->isMail();
        }

        $phpmailer->CharSet = $configuration['charset'];

        foreach ($email['recipients'] as $recipient) {
            $phpmailer->addAddress($recipient);
        }
        foreach ($email['cc'] as $recipient) {
            $phpmailer->addCC($recipient);
        }
        foreach ($email['cci'] as $recipient) {
            $phpmailer->addBCC($recipient);
        }

        if ($email['is_html']) {
            $phpmailer->isHTML(true);

            $dom = new \DOMDocument();
            $dom->loadHTML($email['body'], LIBXML_NOWARNING);
            $images = $dom->getElementsByTagName('img');

            foreach ($images as $key => $image) {
                $originalSrc = $image->getAttribute('src');
                if (preg_match('/^data:image\/(\w+);base64,/', $originalSrc)) {
                    $encodedImage = substr($originalSrc, strpos($originalSrc, ',') + 1);
                    $imageFormat  = substr($originalSrc, 11, strpos($originalSrc, ';') - 11);
                    $phpmailer->addStringEmbeddedImage(base64_decode($encodedImage), "embeded{$key}", "embeded{$key}.{$imageFormat}");
                    $email['body'] = str_replace($originalSrc, "cid:embeded{$key}", $email['body']);
                }
            }
        }

        $phpmailer->Subject = $email['subject'];
        $phpmailer->Body    = $email['body'];
        if (empty($email['body'])) {
            $phpmailer->AllowEmpty = true;
        }

        $phpmailer->Timeout     = 30;
        $phpmailer->SMTPDebug   = 1;
        $phpmailer->Debugoutput = function ($str) {
        };

        $isSent = $phpmailer->send();
        if (!$isSent) {
            return ['errors' => $phpmailer->ErrorInfo];
        }

        return ['success' => 'success'];
    }

    public static function sendNotification(array $args)
    {
        ValidatorModel::notEmpty($args, ['documentId', 'userId']);
        ValidatorModel::intVal($args, ['documentId', 'userId']);

        $workflow = WorkflowModel::getCurrentStep(['select' => ['user_id'], 'documentId' => $args['documentId']]);
        if (empty($workflow) || $args['status'] == 'REF') {
            $document = DocumentModel::getById(['select' => ['typist'], 'id' => $args['documentId']]);
            $mode     = $args['status'] == 'REF' ? 'REF' : 'END';
            EmailController::sendNotificationToTypist(['documentId' => $args['documentId'], 'senderId' => $GLOBALS['id'], 'recipientId' => $document['typist'], 'mode' => $mode]);
        } else {
            EmailController::sendNotificationToNextUserInWorkflow(['documentId' => $args['documentId'], 'senderId' => $GLOBALS['id'], 'recipientId' => $workflow['user_id']]);
        }

        return true;
    }

    public static function sendNotificationToTypist(array $args)
    {
        if (($args['mode'] == 'DEL' || $args['recipientId'] != $GLOBALS['id']) && !empty($args['recipientId'])) {
            $recipient = UserModel::getById(['select' => ['email', 'preferences', '"isRest"'], 'id' => $args['recipientId']]);
    
            $recipient['preferences'] = json_decode($recipient['preferences'], true);
            if ($recipient['preferences']['notifications'] && $recipient['isRest'] == false) {
                $lang = LanguageController::get(['lang' => $recipient['preferences']['lang']]);
                $url  = UrlController::getCoreUrl() . 'dist/search?documentId=' . $args['documentId'];

                if ($args['mode'] == 'END') {
                    $subject = $lang['notificationEndOfWorkflowSubject'];
                    $body    = $lang['notificationEndOfWorkflowBody'] . '<a href="' . $url . '">'.$url.'</a>' . $lang['notificationFooter'];
                } elseif ($args['mode'] == 'INT') {
                    $subject = $lang['notificationInterruptSubject'];
                    $body    = $lang['notificationInterruptBody'] . '<a href="' . $url . '">'.$url.'</a>' . $lang['notificationFooter'];
                } elseif ($args['mode'] == 'DEL') {
                    $subject = $lang['notificationInterruptSubject'];
                    $body    = $lang['notificationDeletedUserBody'] . '<a href="' . $url . '">'.$url.'</a>' . $lang['notificationFooter'];
                } else {
                    $subject = $lang['notificationRefusedSubject'];
                    $body    = $lang['notificationRefusedBody'] . '<a href="' . $url . '">'.$url.'</a>' . $lang['notificationFooter'];
                }

                EmailController::createEmail([
                    'userId' => $args['senderId'],
                    'data'   => [
                        'sender'     => 'Notification',
                        'recipients' => [$recipient['email']],
                        'subject'    => $subject,
                        'body'       => $body,
                        'isHtml'     => true
                    ]
                ]);
            }
        }
    }

    public static function sendNotificationToNextUserInWorkflow(array $args)
    {
        $nextUser = UserModel::getById(['select' => ['email', 'preferences', 'substitute'], 'id' => $args['recipientId']]);
        EmailController::sendNotificationToUser(['documentId' => $args['documentId'], 'senderId' => $args['senderId'], 'email' => $nextUser['email'], 'preferences' => $nextUser['preferences']]);

        if (!empty($nextUser['substitute'])) {
            $nextSubstituteUser = UserModel::getById(['select' => ['email', 'preferences'], 'id' => $nextUser['substitute']]);
            EmailController::sendNotificationToUser(['documentId' => $args['documentId'], 'senderId' => $args['senderId'], 'email' => $nextSubstituteUser['email'], 'preferences' => $nextSubstituteUser['preferences']]);
        }
    }

    public static function sendNotificationToUser(array $args)
    {
        $args['preferences'] = json_decode($args['preferences'], true);
        if ($args['preferences']['notifications']) {
            $lang = LanguageController::get(['lang' => $args['preferences']['lang']]);
            $url  = UrlController::getCoreUrl() . 'dist/documents/' . $args['documentId'];
            EmailController::createEmail([
                'userId' => $args['senderId'],
                'data'   => [
                    'sender'     => 'Notification',
                    'recipients' => [$args['email']],
                    'subject'    => $lang['notificationDocumentAddedSubject'],
                    'body'       => $lang['notificationDocumentAddedBody'] . '<a href="' . $url . '">'.$url.'</a>' . $lang['notificationFooter'],
                    'isHtml'     => true
                ]
            ]);
        }
    }

    public static function controlCreateEmail(array $args)
    {
        if (!Validator::stringType()->notEmpty()->validate($args['sender'])) {
            return ['errors' => 'Data sender is empty or not a string', 'code' => 400];
        } elseif (!Validator::arrayType()->notEmpty()->validate($args['recipients'])) {
            return ['errors' => 'Data recipients is empty or not an array', 'code' => 400];
        } elseif (!Validator::boolType()->validate($args['isHtml'])) {
            return ['errors' => 'Data isHtml is empty or not a boolean', 'code' => 400];
        }

        return ['success' => 'success'];
    }
}
