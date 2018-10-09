<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Batch History Controller
* @author dev@maarch.org
*/

namespace History\controllers;

use Group\models\ServiceModel;
use History\models\BatchHistoryModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class BatchHistoryController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'view_history_batch', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getQueryParams();

        $check = Validator::floatVal()->notEmpty()->validate($data['startDate']);
        $check = $check && Validator::floatVal()->notEmpty()->validate($data['endDate']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $maxRequestSize = 25000;

        $batchHistories = BatchHistoryModel::get([
            'select'    => ['event_date', 'module_name', 'total_processed', 'total_errors', 'info'],
            'where'     => ['event_date > ?', 'event_date < ?'],
            'data'      => [date('Y-m-d H:i:s', $data['startDate']), date('Y-m-d H:i:s', $data['endDate'])],
            'orderBy'   => ['event_date DESC'],
            'limit'     => $maxRequestSize
        ]);

        $limitExceeded = (count($batchHistories) == $maxRequestSize);

        return $response->withJson(['batchHistories' => $batchHistories, 'limitExceeded' => $limitExceeded]);
    }
}
