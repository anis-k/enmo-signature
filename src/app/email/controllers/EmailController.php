<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Email Controller
 * @author dev@maarch.org
 */

namespace Email\controllers;

use Configuration\models\ConfigurationModel;
use Email\models\EmailModel;
use History\controllers\HistoryController;
use PHPMailer\PHPMailer\PHPMailer;
use Respect\Validation\Validator;
use SrcCore\models\AuthenticationModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;

class EmailController
{
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
            'user_id'               => $args['userId'],
            'sender'                => $args['data']['sender'],
            'recipients'            => json_encode($args['data']['recipients']),
            'cc'                    => empty($args['data']['cc']) ? '[]' : json_encode($args['data']['cc']),
            'cci'                   => empty($args['data']['cci']) ? '[]' : json_encode($args['data']['cci']),
            'object'                => empty($args['data']['object']) ? null : $args['data']['object'],
            'body'                  => empty($args['data']['body']) ? null : $args['data']['body'],
            'document'              => null,
            'isHtml'                => $args['data']['isHtml'] ? 'true' : 'false',
            'status'                => 'WAITING'
        ]);

        if (!empty($args['data']['status']) && $args['data']['status'] == 'EXPRESS') {
            $isSent = EmailController::sendEmail(['emailId' => $id]);
            if (!empty($isSent['success'])) {
                EmailModel::update(['set' => ['status' => 'SENT', 'send_date' => 'CURRENT_TIMESTAMP'], 'where' => ['id = ?'], 'data' => [$id]]);
            } else {
                EmailModel::update(['set' => ['status' => 'ERROR'], 'where' => ['id = ?'], 'data' => [$id]]);
            }
        } else {
            $configPath = CoreConfigModel::getConfigPath();
            $encryptKey = CoreConfigModel::getEncryptKey();
            exec("php src/app/email/scripts/sendEmail.php '{$configPath}' {$id} {$args['userId']} '{$encryptKey}' > /dev/null &");
            $isSent = ['success' => 'success'];
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'emails',
            'objectId'      => $id,
            'type'          => 'CREATION',
            'message'       => "Email added"
        ]);

        return $isSent;
    }

    public static function sendEmail(array $args)
    {
        ValidatorModel::notEmpty($args, ['emailId']);
        ValidatorModel::intVal($args, ['emailId']);

        $email = EmailModel::getById(['id' => $args['emailId']]);
        $email['recipients']    = json_decode($email['recipients']);
        $email['cc']            = json_decode($email['cc']);
        $email['cci']           = json_decode($email['cci']);

        $configuration = ConfigurationModel::getByIdentifier(['identifier' => 'emailServer', 'select' => ['value']]);
        $configuration = json_decode($configuration['value'], true);
        if (empty($configuration)) {
            return ['errors' => 'Configuration is missing'];
        }

        $phpmailer = new PHPMailer();

        if ($configuration['type'] == 'smtp') {
            $phpmailer->isSMTP();
            $phpmailer->Host = $configuration['host'];
            $phpmailer->Port = $configuration['port'];
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
            $phpmailer->setFrom($configuration['from']);
        } elseif ($configuration['type'] == 'sendmail') {
            $phpmailer->isSendmail();
        } elseif ($configuration['type'] == 'qmail') {
            $phpmailer->isQmail();
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
                    $imageFormat = substr($originalSrc, 11, strpos($originalSrc, ';') - 11);
                    $phpmailer->addStringEmbeddedImage(base64_decode($encodedImage), "embeded{$key}", "embeded{$key}.{$imageFormat}");
                    $email['body'] = str_replace($originalSrc, "cid:embeded{$key}", $email['body']);
                }
            }
        }

        $phpmailer->Subject = $email['object'];
        $phpmailer->Body = $email['body'];
        if (empty($email['body'])) {
            $phpmailer->AllowEmpty = true;
        }

        $phpmailer->Timeout = 30;
        $phpmailer->SMTPDebug = 1;

        $isSent = $phpmailer->send();
        if (!$isSent) {
            return ['errors' => $phpmailer->ErrorInfo];
        }

        return ['success' => 'success'];
    }

    private static function controlCreateEmail(array $args)
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
