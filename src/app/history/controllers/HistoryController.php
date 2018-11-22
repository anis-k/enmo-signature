<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief History Controller
* @author dev@maarch.org
*/

namespace History\controllers;

use SrcCore\models\ValidatorModel;
use History\models\HistoryModel;
use User\models\UserModel;

class HistoryController
{
    public static function add(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['tableName', 'recordId', 'eventType', 'info']);
        ValidatorModel::stringType($aArgs, ['tableName', 'eventType', 'info']);

        HistoryModel::create([
            'tableName' => $aArgs['tableName'],
            'recordId'  => $aArgs['recordId'],
            'eventType' => $aArgs['eventType'],
            'userId'    => $GLOBALS['id'],
            'info'      => $aArgs['info'],
        ]);

    }
}
