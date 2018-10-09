<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Link Controller
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Link\controllers;

use Link\models\LinkModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class LinkController
{
    public function getByResId(Request $request, Response $response, $aArgs)
    {
        $check = Validator::intVal()->validate($aArgs['resId']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $aLinks = LinkModel::getByResId(['resId' => $aArgs['resId']]);

        return $response->withJson($aLinks);
    }
}
