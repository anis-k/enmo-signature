<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief History Model
* @author dev@maarch.org
*/

namespace History\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

abstract class HistoryModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intVal($aArgs, ['limit']);

        $aHistories = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['history'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aHistories;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['tableName', 'recordId', 'eventType', 'userId', 'info', 'moduleId', 'eventId']);
        ValidatorModel::stringType($aArgs, ['tableName', 'eventType', 'userId', 'info', 'moduleId', 'eventId']);

        DatabaseModel::insert([
            'table'         => 'history',
            'columnsValues' => [
                'table_name' => $aArgs['tableName'],
                'record_id'  => $aArgs['recordId'],
                'event_type' => $aArgs['eventType'],
                'user_id'    => $aArgs['userId'],
                'event_date' => 'CURRENT_TIMESTAMP',
                'info'       => $aArgs['info'],
                'id_module'  => $aArgs['moduleId'],
                'remote_ip'  => $_SERVER['REMOTE_ADDR'],
                'event_id'   => $aArgs['eventId'],
            ]
        ]);

        return true;
    }

    public static function getByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aHistories = DatabaseModel::select([
            'select'   => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'    => ['history'],
            'where'    => ['user_id = ?', 'event_date > (CURRENT_TIMESTAMP - interval \'7 DAYS\')'],
            'data'     => [$aArgs['userId']],
            'order_by' => ['event_date DESC'],
            'limit'    => 200
        ]);

        return $aHistories;
    }

    public static function getFilter(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['select','event_date']);
        ValidatorModel::stringType($aArgs, ['select']);

        $aReturn = DatabaseModel::select(
            [
            'select'   => ['DISTINCT('.$aArgs['select'].')'],
            'table'    => ['history'],
            'where'    => ["event_date >= date '".$aArgs['event_date']."'","event_date < date '".$aArgs['event_date']."' + interval '1 month'"],
            'order_by' => [$aArgs['select'].' ASC']
            ]
        );

        return $aReturn;
    }
}
