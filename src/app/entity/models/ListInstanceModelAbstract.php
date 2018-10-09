<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Instance Model Abstract
 * @author dev@maarch.org
 */

namespace Entity\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

abstract class ListInstanceModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $aListInstances = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
            'order_by'  => $aArgs['orderBy'],
            'limit'     => $aArgs['limit']
        ]);

        return $aListInstances;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => ['listinstance_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($aListinstance[0])) {
            return [];
        }

        return $aListinstance[0];
    }

    public static function getListByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance', 'users', 'users_entities', 'entities'],
            'left_join' => ['listinstance.item_id = users.user_id', 'users_entities.user_id = users.user_id', 'entities.entity_id = users_entities.entity_id'],
            'where'     => ['res_id = ?', 'item_type = ?', 'difflist_type = ?', 'primary_entity = ?'],
            'data'      => [$aArgs['id'], 'user_id', 'entity_id', 'Y'],
            'order_by'  => ['listinstance_id ASC'],
        ]);

        unset($aArgs['select'][5]);
        unset($aArgs['select'][6]);

        $aListinstance2 = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance', 'entities'],
            'left_join' => ['listinstance.item_id = entities.entity_id'],
            'where'     => ['res_id = ?', 'item_type = ?', 'difflist_type = ?'],
            'data'      => [$aArgs['id'], 'entity_id', 'entity_id'],
            'order_by'  => ['listinstance_id ASC'],
        ]);

        return array_merge($aListinstance, $aListinstance2);
    }

    public static function getVisaCircuitByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance', 'users', 'users_entities', 'entities'],
            'left_join' => ['listinstance.item_id = users.user_id', 'users_entities.user_id = users.user_id', 'entities.entity_id = users_entities.entity_id'],
            'where'     => ['res_id = ?', 'item_type = ?', 'difflist_type = ?', 'primary_entity = ?'],
            'data'      => [$aArgs['id'], 'user_id', 'VISA_CIRCUIT', 'Y'],
            'order_by'  => ['listinstance_id ASC'],
        ]);

        return $aListinstance;
    }

    public static function getAvisCircuitByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance', 'users', 'users_entities', 'entities'],
            'left_join' => ['listinstance.item_id = users.user_id', 'users_entities.user_id = users.user_id', 'entities.entity_id = users_entities.entity_id'],
            'where'     => ['res_id = ?', 'item_type = ?', 'difflist_type = ?', 'primary_entity = ?'],
            'data'      => [$aArgs['id'], 'user_id', 'AVIS_CIRCUIT', 'Y'],
            'order_by'  => ['listinstance_id ASC'],
        ]);

        return $aListinstance;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['res_id']);
        ValidatorModel::intVal($aArgs, ['res_id']);

        DatabaseModel::insert([
            'table'         => 'listinstance',
            'columnsValues' => $aArgs
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'listinstance',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function getCurrentStepByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => ['res_id = ?', 'difflist_type = ?', 'process_date is null'],
            'data'      => [$aArgs['resId'], 'VISA_CIRCUIT'],
            'order_by'  => ['listinstance_id ASC'],
            'limit'     => 1
        ]);

        if (empty($aListinstance[0])) {
            return [];
        }

        return $aListinstance[0];
    }

    public static function getWithConfidentiality(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['entityId', 'userId']);
        ValidatorModel::stringType($aArgs, ['entityId', 'userId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListInstances = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance, res_letterbox, mlb_coll_ext'],
            'where'     => ['listinstance.res_id = res_letterbox.res_id', 'mlb_coll_ext.res_id = res_letterbox.res_id', 'confidentiality = ?', 'destination = ?', 'item_id = ?', 'closing_date is null'],
            'data'      => ['Y', $aArgs['entityId'], $aArgs['userId']]
        ]);

        return $aListInstances;
    }
}
