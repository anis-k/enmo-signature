<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Home Controller
 * @author dev@maarch.org
 */

namespace Home\controllers;

use Basket\models\BasketModel;
use Resource\models\ResModel;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;
use Parameter\models\ParameterModel;

class HomeController
{
    public function get(Request $request, Response $response)
    {
        $regroupedBaskets = [];

        $user = UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id']]);
        $homeMessage = ParameterModel::getById(['select' => ['param_value_string'], 'id'=> 'homepage_message']);
        $homeMessage = trim($homeMessage['param_value_string']);

        $redirectedBaskets = BasketModel::getRedirectedBasketsByUserId(['userId' => $GLOBALS['userId']]);
        $groups = UserModel::getGroupsByUserId(['userId' => $GLOBALS['userId']]);
        foreach ($groups as $group) {
            $baskets = BasketModel::getAvailableBasketsByGroupUser([
                'select'        => ['baskets.basket_id', 'baskets.basket_name', 'baskets.basket_desc', 'baskets.basket_clause', 'baskets.color', 'users_baskets_preferences.color as pcolor'],
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

                $baskets[$kBasket]['redirected'] = false;
                foreach ($redirectedBaskets as $redirectedBasket) {
                    if ($redirectedBasket['basket_id'] == $basket['basket_id']) {
                        $baskets[$kBasket]['redirected'] = true;
                        $baskets[$kBasket]['redirectedUser'] = $redirectedBasket['userToDisplay'];
                    }
                }

                $baskets[$kBasket]['resourceNumber'] = BasketModel::getResourceNumberByClause(['userId' => $GLOBALS['userId'], 'clause' => $basket['basket_clause']]);

                unset($baskets[$kBasket]['pcolor'], $baskets[$kBasket]['basket_clause']);
            }

            if (!empty($baskets)) {
                $regroupedBaskets[] = [
                    'groupSerialId' => $group['id'],
                    'groupId'       => $group['group_id'],
                    'groupDesc'     => $group['group_desc'],
                    'baskets'       => $baskets
                ];
            }
        }

        $assignedBaskets = BasketModel::getAbsBasketsByUserId(['userId' => $GLOBALS['userId']]);
        foreach ($assignedBaskets as $key => $assignedBasket) {
            $basket = BasketModel::getById(['select' => ['basket_clause'], 'id' => $assignedBasket['basket_id']]);
            $assignedBaskets[$key]['resourceNumber'] = BasketModel::getResourceNumberByClause(['userId' => $assignedBasket['user_abs'], 'clause' => $basket['basket_clause']]);
        }

        return $response->withJson([
            'regroupedBaskets'  => $regroupedBaskets,
            'assignedBaskets'   => $assignedBaskets,
            'homeMessage'       => $homeMessage,
        ]);
    }

    public function getLastRessources(Request $request, Response $response)
    {
        $lastResources = ResModel::getLastResources([
            'select'    => [
                'mlb.alt_identifier',
                'mlb.closing_date',
                'r.creation_date',
                'priorities.color as priority_color',
                'mlb.process_limit_date',
                'r.res_id',
                'status.img_filename as status_icon',
                'status.label_status as status_label',
                'status.id as status_id',
                'r.subject',
            ],
            'limit'     => 5,
            'userId'    => $GLOBALS['userId']
        ]);

        return $response->withJson([
            'lastResources'     => $lastResources,
        ]);
    }
}
