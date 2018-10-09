<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
* @brief   Action Model Abstract
* @author  dev@maarch.org
*/

namespace Action\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;

abstract class ActionModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select']);

        $actions = DatabaseModel::select([
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['actions']
        ]);

        return $actions;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::select([
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['actions'],
            'where'  => ['id = ?'],
            'data'   => [$aArgs['id']]
        ]);

        if (empty($aReturn[0])) {
            return [];
        }

        $aReturn = $aReturn[0];
        $aReturn['actionCategories'] = DatabaseModel::select([
            'select' => ['category_id'],
            'table'  => ['actions_categories'],
            'where'  => ['action_id = ?'],
            'data'   => [$aArgs['id']]
        ]);
       
        return $aReturn;
    }

    public static function create(array $aArgs)
    {
        $actionCategories = empty($aArgs['actionCategories']) ? [] : $aArgs['actionCategories'];
        unset($aArgs['actionCategories']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'actions_id_seq']);
        $aArgs['id'] = $nextSequenceId;

        DatabaseModel::insert([
            'table'         => 'actions',
            'columnsValues' => $aArgs
        ]);

        $data = [];
        $data['action_id'] = $nextSequenceId;
        foreach ($actionCategories as $actionCategory) {
            $data['category_id'] = $actionCategory;
            DatabaseModel::insert([
                'table'         => 'actions_categories',
                'columnsValues' => $data
            ]);
        }

        return $nextSequenceId;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        
        DatabaseModel::update([
            'table'     => 'actions',
            'set'       => [
                'keyword'           => $aArgs['keyword'],
                'label_action'      => $aArgs['label_action'],
                'id_status'         => $aArgs['id_status'],
                'action_page'       => $aArgs['action_page'],
                'history'           => $aArgs['history'],
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        DatabaseModel::delete([
            'table' => 'actions_categories',
            'where'  => ['action_id = ?'],
            'data'   => [$aArgs['id']]
        ]);

        $tab['action_id'] = $aArgs['id'];

        if (!empty($aArgs['actionCategories'])) {
            foreach ($aArgs['actionCategories'] as $actionCategory) {
                $tab['category_id'] = $actionCategory;
                DatabaseModel::insert([
                    'table'         => 'actions_categories',
                    'columnsValues' => $tab
                ]);
            }
        }

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        DatabaseModel::delete([
            'table' => 'actions',
            'where' => ['id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'actions_categories',
            'where' => ['action_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'actions_groupbaskets',
            'where' => ['id_action = ?'],
            'data'  => [$aArgs['id']]
        ]);

        return true;
    }

    public static function getActionPages()
    {
        $actionsPages              = [];
        $actionsPages['modules'][] = 'Apps';

        $paths = ['core/xml/actions_pages.xml', 'modules/avis/xml/actions_pages.xml', 'modules/export_seda/xml/actions_pages.xml'];

        foreach ($paths as $path) {
            $loadedXml = CoreConfigModel::getXmlLoaded(['path' => $path]);
            if ($loadedXml) {
                foreach ($loadedXml->ACTIONPAGE as $actionPage) {
                    if (!defined((string) $actionPage->LABEL)) {
                        $label = $actionPage->LABEL;
                    } else {
                        $label = constant((string) $actionPage->LABEL);
                    }
                    if (!empty((string) $actionPage->MODULE)) {
                        $origin = (string) $actionPage->MODULE;
                    } else {
                        $origin = 'apps';
                    }
                    if (!empty((string) $actionPage->DESC)) {
                        $desc = constant((string) $actionPage->DESC);
                    } else {
                        $desc = 'No description';
                    }
                    $actionsPages['actionsPageList'][] = [
                        'id'     => (string) $actionPage->ID,
                        'label'  => $label,
                        'name'   => (string) $actionPage->NAME,
                        'desc'   => $desc,
                        'origin' => ucfirst($origin)
                    ];
                }
            }
        }

        array_multisort(
            array_map(
                function ($element) {
                    return $element['label'];
                }, $actionsPages['actionsPageList']
            ),
            SORT_ASC, $actionsPages['actionsPageList']
        );

        $actionsPages['modules'] = array_unique($actionsPages['modules']);
        sort($actionsPages['modules']);

        return $actionsPages;
    }

    public static function getKeywords()
    {
        $tabKeyword   = [];
        $tabKeyword[] = ['value' => '', 'label' => _NO_KEYWORD];
        $tabKeyword[] = ['value' => 'redirect', 'label' => _REDIRECTION, 'desc' => _KEYWORD_REDIRECT_DESC];
        $tabKeyword[] = ['value' => 'indexing', 'label' => _INDEXING, 'desc' => _KEYWORD_INDEXING_DESC];

        return $tabKeyword;
    }

    public static function getActionPageById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $action = DatabaseModel::select([
            'select' => ['action_page'],
            'table'  => ['actions'],
            'where'  => ['id = ? AND enabled = ?'],
            'data'   => [$aArgs['id'], 'Y']
        ]);

        if (empty($action[0])) {
            return '';
        }

        return $action[0]['action_page'];
    }

    public static function getDefaultActionByGroupBasketId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'basketId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'basketId']);

        $action = DatabaseModel::select([
            'select' => ['id_action'],
            'table'  => ['actions_groupbaskets'],
            'where'  => ['group_id = ?', 'basket_id = ?', 'default_action_list = ?'],
            'data'   => [$aArgs['groupId'], $aArgs['basketId'], 'Y']
        ]);

        if (empty($action[0])) {
            return '';
        }

        return $action[0]['id_action'];
    }

    public static function getForBasketPage(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['basketId', 'groupId']);
        ValidatorModel::stringType($aArgs, ['basketId', 'groupId']);

        $actions = DatabaseModel::select([
            'select'    => ['id_action', 'where_clause', 'default_action_list', 'actions.label_action'],
            'table'     => ['actions_groupbaskets, actions'],
            'where'     => ['basket_id = ?', 'group_id = ?', 'used_in_action_page = ?', 'actions_groupbaskets.id_action = actions.id'],
            'data'      => [$aArgs['basketId'], $aArgs['groupId'], 'Y'],
            'order_by'  => ['default_action_list DESC']
        ]);

        return $actions;
    }
}
