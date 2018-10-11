<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Resource Controller
* @author dev@maarch.org
*/

namespace Document\controllers;

use Document\models\DocumentModel;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;

class DocumentController
{
    public function get(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        if (empty($data['offset']) || !is_numeric($data['offset'])) {
            $data['offset'] = 0;
        }
        if (empty($data['limit']) || !is_numeric($data['limit'])) {
            $data['limit'] = 0;
        }

        $user = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);

        $documents = DocumentModel::getByUserId(['select' => ['*'], 'userId' => $user['id']]);

        return $response->withJson(['documents' => $documents]);
    }
}
