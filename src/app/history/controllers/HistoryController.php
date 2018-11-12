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

use Respect\Validation\Validator;
use SrcCore\controllers\LogsController;
use Group\models\ServiceModel;
use SrcCore\models\ValidatorModel;
use History\models\HistoryModel;
use Notification\controllers\NotificationsEventsController;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;

class HistoryController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'view_history', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getQueryParams();

        $check = Validator::floatVal()->notEmpty()->validate($data['startDate']);
        $check = $check && Validator::floatVal()->notEmpty()->validate($data['endDate']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $maxRequestSize = 25000;

        $histories = HistoryModel::get([
            'select'    => ['event_date', 'event_type', 'user_id', 'info', 'remote_ip'],
            'where'     => ['event_date > ?', 'event_date < ?'],
            'data'      => [date('Y-m-d H:i:s', $data['startDate']), date('Y-m-d H:i:s', $data['endDate'])],
            'orderBy'   => ['event_date DESC'],
            'limit'     => $maxRequestSize
        ]);

        $limitExceeded = (count($histories) == $maxRequestSize);

        return $response->withJson(['histories' => $histories, 'limitExceeded' => $limitExceeded]);
    }

    public static function add(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['tableName', 'recordId', 'eventType', 'info', 'eventId']);
        ValidatorModel::stringType($aArgs, ['tableName', 'eventType', 'info', 'eventId', 'moduleId', 'level']);

        if (empty($aArgs['isTech'])) {
            $aArgs['isTech'] = false;
        }
        if (empty($aArgs['moduleId'])) {
            $aArgs['moduleId'] = 'admin';
        }
        if (empty($aArgs['level'])) {
            $aArgs['level'] = 'DEBUG';
        }

        // TODO LOG
        // LogsController::add($aArgs);

        if (empty($aArgs['userId'])) {
            $aArgs['userId'] = $GLOBALS['login'];
        }

        HistoryModel::create([
            'tableName' => $aArgs['tableName'],
            'recordId'  => $aArgs['recordId'],
            'eventType' => $aArgs['eventType'],
            'userId'    => $aArgs['userId'],
            'info'      => $aArgs['info'],
            'moduleId'  => $aArgs['moduleId'],
            'eventId'   => $aArgs['eventId'],
        ]);

    }

    public function getByUserId(Request $request, Response $response, array $aArgs)
    {
        $user = UserModel::getById(['id' => $aArgs['userSerialId'], 'select' => ['user_id']]);
        if ($user['user_id'] != $GLOBALS['userId'] && !ServiceModel::hasService(['id' => 'view_history', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $aHistories = HistoryModel::getByUserId(['userId' => $user['user_id'], 'select' => ['info', 'event_date']]);

        return $response->withJson(['histories' => $aHistories]);
    }
}
