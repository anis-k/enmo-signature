<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Action Controller
* @author dev@maarch.org
*/

namespace Action\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Action\models\ActionModel;

class ActionController
{
    public function get(Request $request, Response $response)
    {
        $actions = ActionModel::get(['select' => ['id', 'label', 'color', 'logo', 'event'], 'orderBy' => ['id']]);

        return $response->withJson(['actions' => $actions]);
    }
}
