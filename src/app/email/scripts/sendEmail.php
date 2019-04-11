<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Email Script
 * @author dev@maarch.org
 */

namespace Email\scripts;

require 'vendor/autoload.php';

use Email\controllers\EmailController;
use Email\models\EmailModel;
use History\controllers\HistoryController;
use SrcCore\models\DatabasePDO;

//configPath = $argv[1];
//emailId    = $argv[2];
//userId     = $argv[3];
//encryptKey = $argv[4];

EmailScript::send(['configPath' => $argv[1], 'emailId' => $argv[2], 'userId' => $argv[3], 'encryptKey' => $argv[4]]);

class EmailScript
{
    public static function send(array $args)
    {
        DatabasePDO::reset();
        new DatabasePDO(['configPath' => $args['configPath']]);

        $GLOBALS['id'] = $args['userId'];
        $_SERVER['MAARCH_ENCRYPT_KEY'] = $args['encryptKey'];

        $isSent = EmailController::sendEmail(['emailId' => $args['emailId']]);
        if (!empty($isSent['success'])) {
            EmailModel::update(['set' => ['status' => 'SENT', 'send_date' => 'CURRENT_TIMESTAMP'], 'where' => ['id = ?'], 'data' => [$args['emailId']]]);
        } else {
            EmailModel::update(['set' => ['status' => 'ERROR'], 'where' => ['id = ?'], 'data' => [$args['emailId']]]);
            HistoryController::add([
                'code'          => 'KO',
                'objectType'    => 'emails',
                'objectId'      => $args['emailId'],
                'type'          => 'EMAIL',
                'message'       => '{emailFailed}',
                'data'          => ['errors' => $isSent['errors']]
            ]);
        }

        return $isSent;
    }
}
