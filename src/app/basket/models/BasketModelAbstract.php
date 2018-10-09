<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief   Basket Model Abstract
* @author  dev@maarch.org
*/

namespace Basket\models;

use SrcCore\models\ValidatorModel;
use Resource\models\ResModel;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use User\models\UserBasketPreferenceModel;
use User\models\UserModel;

abstract class BasketModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);

        $aBaskets = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['baskets'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
            'order_by'  => $aArgs['orderBy']
        ]);

        return $aBaskets;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aBasket = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['baskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($aBasket[0])) {
            return [];
        }

        return $aBasket[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'basket_name', 'basket_desc', 'clause', 'isVisible', 'flagNotif']);
        ValidatorModel::stringType($aArgs, ['id', 'basket_name', 'color', 'basket_desc', 'clause', 'isVisible', 'flagNotif']);

        DatabaseModel::insert([
            'table'         => 'baskets',
            'columnsValues' => [
                'basket_id'         => $aArgs['id'],
                'basket_name'       => $aArgs['basket_name'],
                'basket_desc'       => $aArgs['basket_desc'],
                'basket_clause'     => $aArgs['clause'],
                'is_visible'        => $aArgs['isVisible'],
                'flag_notif'        => $aArgs['flagNotif'],
                'color'             => $aArgs['color'],
                'coll_id'           => 'letterbox_coll',
            ]
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'basket_name', 'basket_desc', 'clause', 'isVisible', 'flagNotif']);
        ValidatorModel::stringType($aArgs, ['id', 'basket_name', 'color', 'basket_desc', 'clause', 'isVisible', 'flagNotif', 'basket_res_order']);
        
        DatabaseModel::update([
            'table'     => 'baskets',
            'set'       => [
                'basket_name'       => $aArgs['basket_name'],
                'basket_desc'       => $aArgs['basket_desc'],
                'basket_clause'     => $aArgs['clause'],
                'basket_res_order'  => empty($aArgs['basket_res_order']) ? 'res_id DESC' : $aArgs['basket_res_order'],
                'is_visible'        => $aArgs['isVisible'],
                'flag_notif'        => $aArgs['flagNotif'],
                'color'             => $aArgs['color'],
                'coll_id'           => 'letterbox_coll',
            ],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }

    public static function updateOrder(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['order']);

        DatabaseModel::update([
            'table'     => 'baskets',
            'set'       => [
                'basket_order'  => $aArgs['order']
            ],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        DatabaseModel::delete([
            'table' => 'baskets',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket_redirect',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket_status',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'actions_groupbaskets',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'users_baskets_preferences',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);

        return true;
    }

    public static function createGroupAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'actionId', 'usedInBasketlist', 'usedInActionPage', 'defaultActionList']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId', 'whereClause', 'usedInBasketlist', 'usedInActionPage', 'defaultActionList']);
        ValidatorModel::intVal($aArgs, ['actionId']);

        DatabaseModel::insert([
            'table'         => 'actions_groupbaskets',
            'columnsValues' => [
                'id_action'             => $aArgs['actionId'],
                'where_clause'          => $aArgs['whereClause'],
                'group_id'              => $aArgs['groupId'],
                'basket_id'             => $aArgs['id'],
                'used_in_basketlist'    => $aArgs['usedInBasketlist'],
                'used_in_action_page'   => $aArgs['usedInActionPage'],
                'default_action_list'   => $aArgs['defaultActionList'],
            ]
        ]);

        return true;
    }

    public static function getGroupActionRedirect(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $aRedirects = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['groupbasket_redirect'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return $aRedirects;
    }

    public static function createGroupActionRedirect(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'actionId', 'redirectMode']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId', 'entityId', 'keyword', 'redirectMode']);
        ValidatorModel::intVal($aArgs, ['actionId']);

        DatabaseModel::insert([
            'table'         => 'groupbasket_redirect',
            'columnsValues' => [
                'action_id'     => $aArgs['actionId'],
                'group_id'      => $aArgs['groupId'],
                'basket_id'     => $aArgs['id'],
                'entity_id'     => $aArgs['entityId'],
                'keyword'       => $aArgs['keyword'],
                'redirect_mode' => $aArgs['redirectMode']
            ]
        ]);

        return true;
    }

    public static function updateGroupActionRedirect(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'groupbasket_redirect',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function getGroupActionStatus(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);

        $aStatuses = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['groupbasket_status'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy']
        ]);

        return $aStatuses;
    }

    public static function createGroupActionStatus(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'actionId', 'statusId']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId', 'statusId']);
        ValidatorModel::intVal($aArgs, ['actionId']);
        ValidatorModel::intType($aArgs, ['order']);

        DatabaseModel::insert([
            'table'         => 'groupbasket_status',
            'columnsValues' => [
                'action_id'     => $aArgs['actionId'],
                'group_id'      => $aArgs['groupId'],
                'basket_id'     => $aArgs['id'],
                'status_id'     => $aArgs['statusId'],
                '"order"'       => $aArgs['order']
            ]
        ]);

        return true;
    }

    public static function getActionsForGroupById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions_groupbaskets'],
            'where'     => ['basket_id = ?', 'group_id = ?'],
            'data'      => [$aArgs['id'], $aArgs['groupId']]
        ]);

        return $aGroups;
    }

    public static function hasGroup(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId']);

        return !empty(GroupBasketModel::get(['where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$aArgs['id'], $aArgs['groupId']]]));
    }

    public static function getResListById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['basketId', 'userId']);
        ValidatorModel::stringType($aArgs, ['basketId', 'userId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aBasket = DatabaseModel::select([
            'select'    => ['basket_clause', 'basket_res_order'],
            'table'     => ['baskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['basketId']]
        ]);

        if (empty($aBasket[0]) || empty($aBasket[0]['basket_clause'])) {
            return [];
        }

        $where = PreparedClauseController::getPreparedClause(['clause' => $aBasket[0]['basket_clause'], 'userId' => $aArgs['userId']]);

        $aResList = ResModel::getOnView([
            'select'    => $aArgs['select'],
            'where'     => [$where],
            'orderBy'   => empty($aBasket[0]['basket_res_order']) ? ['creation_date DESC'] : [$aBasket[0]['basket_res_order']],
        ]);

        return $aResList;
    }

    public static function getBasketsByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);
        ValidatorModel::arrayType($aArgs, ['unneededBasketId']);
        ValidatorModel::boolType($aArgs, ['absenceUneeded']);

        $userGroups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);
        $groupIds = [];
        foreach ($userGroups as $value) {
            $groupIds[] = $value['group_id'];
        }

        $aBaskets = [];
        if (!empty($groupIds)) {
            $where = ['groupbasket.group_id in (?)', 'groupbasket.basket_id = baskets.basket_id', 'groupbasket.group_id = usergroups.group_id'];
            $data = [$groupIds];
            if (!empty($aArgs['unneededBasketId'])) {
                $where[] = 'groupbasket.basket_id not in (?)';
                $data[]  = $aArgs['unneededBasketId'];
            }
            $aBaskets = DatabaseModel::select([
                    'select'    => ['usergroups.id as groupSerialId', 'groupbasket.basket_id', 'groupbasket.group_id', 'basket_name', 'basket_desc', 'basket_clause', 'usergroups.group_desc'],
                    'table'     => ['groupbasket, baskets, usergroups'],
                    'where'     => $where,
                    'data'      => $data,
                    'order_by'  => ['groupbasket.group_id, basket_order, basket_name']
            ]);

            $user = UserModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['id']]);
            $userPrefs = UserBasketPreferenceModel::get([
                'select'    => ['group_serial_id', 'basket_id'],
                'where'     => ['user_serial_id = ?'],
                'data'      => [$user['id']]
            ]);

            foreach ($aBaskets as $key => $value) {
                unset($aBaskets[$key]['groupserialid']);
                $aBaskets[$key]['groupSerialId'] = $value['groupserialid'];
                $aBaskets[$key]['is_virtual'] = 'N';
                $aBaskets[$key]['basket_owner'] = $aArgs['userId'];
                $aBaskets2 = DatabaseModel::select([
                        'select'    => ['new_user'],
                        'table'     => ['user_abs'],
                        'where'     => ['user_abs = ?', 'basket_id = ?'],
                        'data'      => [$aArgs['userId'], $value['basket_id']],
                ]);
                $aBaskets[$key]['userToDisplay'] = UserModel::getLabelledUserById(['userId' => $aBaskets2[0]['new_user']]);
                $aBaskets[$key]['enabled'] = true;
                $aBaskets[$key]['allowed'] = false;
                foreach ($userPrefs as $userPref) {
                    if ($userPref['group_serial_id'] == $value['groupserialid'] && $userPref['basket_id'] == $value['basket_id']) {
                        $aBaskets[$key]['allowed'] = true;
                    }
                }
            }
            if (empty($aArgs['absenceUneeded'])) {
                $aBaskets = array_merge($aBaskets, BasketModel::getAbsBasketsByUserId(['userId' => $aArgs['userId']]));
            }
        }

        return $aBaskets;
    }

    public static function getAbsBasketsByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aBaskets = DatabaseModel::select([
                'select'    => ['ba.basket_id', 'ba.basket_name', 'ba.basket_desc', 'ua.user_abs', 'ua.basket_owner', 'ua.is_virtual'],
                'table'     => ['baskets ba, user_abs ua'],
                'where'     => ['ua.new_user = ?', 'ua.basket_id = ba.basket_id'],
                'data'      => [$aArgs['userId']],
                'order_by'  => ['ba.basket_order, ba.basket_name']
        ]);

        foreach ($aBaskets as $key => $value) {
            $aBaskets[$key]['userToDisplay'] = UserModel::getLabelledUserById(['userId' => $value['user_abs']]);
        }

        return $aBaskets;
    }

    public static function setRedirectedBaskets(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userAbs', 'newUser', 'basketId', 'basketOwner', 'isVirtual']);
        ValidatorModel::stringType($aArgs, ['userAbs', 'newUser', 'basketId', 'basketOwner', 'isVirtual']);

        DatabaseModel::insert([
            'table'         => 'user_abs',
            'columnsValues' => [
                'user_abs'      => $aArgs['userAbs'],
                'new_user'      => $aArgs['newUser'],
                'basket_id'     => $aArgs['basketId'],
                'basket_owner'  => $aArgs['basketOwner'],
                'is_virtual'    => $aArgs['isVirtual']
            ]
        ]);

        return true;
    }

    public static function updateRedirectedBaskets(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'basketOwner', 'basketId', 'userAbs', 'newUser']);
        ValidatorModel::stringType($aArgs, ['userId']);

        DatabaseModel::update([
            'table'     => 'user_abs',
            'set'       => [
                'new_user' => $aArgs['newUser']
            ],
            'where'     => ['basket_id = ?', 'basket_owner = ?', 'user_abs = ?', 'new_user = ?'],
            'data'      => [$aArgs['basketId'], $aArgs['basketOwner'], $aArgs['userAbs'], $aArgs['userId']]
        ]);

        return true;
    }

    public static function deleteBasketRedirection(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'basketId']);
        ValidatorModel::stringType($aArgs, ['userId', 'basketId']);

        DatabaseModel::delete([
            'table' => 'user_abs',
            'where' => ['(user_abs = ? OR basket_owner = ?)', 'basket_id = ?'],
            'data'  => [$aArgs['userId'], $aArgs['userId'], $aArgs['basketId']]
        ]);

        return true;
    }

    public static function getRedirectedBasketsByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aBaskets = DatabaseModel::select([
            'select'    => ['ba.basket_id', 'ba.basket_name', 'ua.new_user', 'ua.basket_owner'],
            'table'     => ['baskets ba, user_abs ua'],
            'where'     => ['ua.user_abs = ?', 'ua.basket_id = ba.basket_id'],
            'data'      => [$aArgs['userId']],
            'order_by'  => ['ua.system_id']
        ]);

        foreach ($aBaskets as $key => $value) {
            $user = UserModel::getByUserId(['userId' => $value['new_user'], 'select' => ['firstname', 'lastname']]);
            $aBaskets[$key]['userToDisplay']     = "{$user['firstname']} {$user['lastname']}";
            $aBaskets[$key]['userIdRedirection'] = $value['new_user'];
            $aBaskets[$key]['user']              = "{$user['firstname']} {$user['lastname']}" ;
        }

        return $aBaskets;
    }

    public static function getRegroupedBasketsByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $regroupedBaskets = [];

        $user = UserModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['id']]);

        $groups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);
        foreach ($groups as $group) {
            $baskets = BasketModel::getAvailableBasketsByGroupUser([
                'select'        => ['baskets.basket_id', 'baskets.basket_name', 'baskets.basket_desc', 'baskets.color', 'users_baskets_preferences.color as pcolor'],
                'userSerialId'  => $user['id'],
                'groupId'       => $group['group_id'],
                'groupSerialId' => $group['id']
            ]);

            foreach ($baskets as $kBasket => $basket) {
                if (!empty($basket['pcolor'])) {
                    $baskets[$kBasket]['color'] = $basket['pcolor'];
                }
                if (empty($baskets[$kBasket]['color'])) {
                    $baskets[$kBasket]['color'] = '#666666';
                }
                unset($baskets[$kBasket]['pcolor']);
            }

            $regroupedBaskets[] = [
                'groupSerialId' => $group['id'],
                'groupId'       => $group['group_id'],
                'groupDesc'     => $group['group_desc'],
                'baskets'       => $baskets
            ];
        }

        return $regroupedBaskets;
    }

    public static function getAvailableBasketsByGroupUser(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userSerialId', 'groupId', 'groupSerialId', 'select']);
        ValidatorModel::intVal($aArgs, ['userSerialId', 'groupSerialId']);
        ValidatorModel::stringType($aArgs, ['groupId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $baskets = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['groupbasket, baskets, users_baskets_preferences'],
            'where'     => [
                'groupbasket.basket_id = baskets.basket_id',
                'baskets.basket_id = users_baskets_preferences.basket_id',
                'groupbasket.group_id = ?',
                'users_baskets_preferences.group_serial_id = ?',
                'users_baskets_preferences.user_serial_id = ?',
                'baskets.is_visible = ?',
                'baskets.basket_id != ?'
            ],
            'data'      => [$aArgs['groupId'], $aArgs['groupSerialId'], $aArgs['userSerialId'], 'Y', 'IndexingBasket'],
            'order_by'  => ['baskets.basket_order', 'baskets.basket_name']
        ]);

        return $baskets;
    }

    public static function getBasketPages(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['unneeded']);

        $basketPages = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/basket/xml/basketpage.xml']);
        if ($loadedXml) {
            foreach ($loadedXml->BASKETPAGE as $value) {
                if (empty($aArgs['unneeded']) || !in_array((string)$value->ID, $aArgs['unneeded'])) {
                    $basketPages[] = [
                        'id'    => (string)$value->ID,
                        'label' => constant((string)$value->LABEL),
                        'name'  => (string)$value->NAME
                    ];
                }
            }
        }

        return $basketPages;
    }

    public static function getDefaultActionIdByBasketId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['basketId', 'groupId']);
        ValidatorModel::stringType($aArgs, ['basketId', 'groupId']);

        $aAction = DatabaseModel::select(
            [
            'select'    => ['id_action'],
            'table'     => ['actions_groupbaskets'],
            'where'     => ['basket_id = ?', 'group_id = ?', 'default_action_list = \'Y\''],
            'data'      => [$aArgs['basketId'], $aArgs['groupId']]
            ]
        );

        if (empty($aAction[0])) {
            return '';
        }

        return $aAction[0]['id_action'];
    }

    public static function getResourceNumberByClause(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'clause']);
        ValidatorModel::stringType($aArgs, ['userId', 'clause']);

        $count = ResModel::getOnView([
            'select'    => ['COUNT(1)'],
            'where'     => [PreparedClauseController::getPreparedClause(['userId' => $aArgs['userId'], 'clause' => $aArgs['clause']])]
        ]);

        if (empty($count[0]['count'])) {
            return 0;
        }

        return $count[0]['count'];
    }
}
