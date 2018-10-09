<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief DocerverType Controller
* @author dev@maarch.org
*/

namespace Docserver\controllers;

use Group\models\ServiceModel;
use Docserver\models\DocserverTypeModel;
use Slim\Http\Request;
use Slim\Http\Response;

class DocserverTypeController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['docserverTypes' => DocserverTypeModel::get(['orderBy' => ['docserver_type_label']])]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $docserverType = DocserverTypeModel::getById(['id' => $aArgs['id']]);

        if(empty($docserverType)){
            return $response->withStatus(400)->withJson(['errors' => 'Docserver Type not found']);
        }

        return $response->withJson($docserverType);
    }
}
