<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Version Update Controller
 * @author dev@maarch.org
 */

namespace VersionUpdate\controllers;

use Gitlab\Client;
use Group\models\ServiceModel;
use Parameter\models\ParameterModel;
use Slim\Http\Request;
use Slim\Http\Response;

class VersionUpdateController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_update_control', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $client = Client::create('https://labs.maarch.org/api/v4/');
        try {
            $tags = $client->api('tags')->all('12');
        } catch (\Exception $e) {
            return $response->withJson(['errors' => $e->getMessage()]);
        }

        $parameter = ParameterModel::getById(['select' => ['param_value_string'], 'id' => 'database_version']);

        $currentVersionBranch = substr($parameter['param_value_string'], 0, 5);
        $currentVersionBranchYear = substr($parameter['param_value_string'], 0, 2);
        $currentVersionBranchMonth = substr($parameter['param_value_string'], 3, 2);
        $currentVersionTag = substr($parameter['param_value_string'], 6);

        $currentMinorVersions = [];
        $availableMajorVersions = [];

        foreach ($tags as $value) {
            if (!preg_match("/^\d{2}\.\d{2}\.\d+$/", $value['name'])) {
                continue;
            }
            $tag = substr($value['name'], 6);
            $pos = strpos($value['name'], $currentVersionBranch);
            if ($pos === false) {
                $year = substr($value['name'], 0, 2);
                $month = substr($value['name'], 3, 2);
                if (($year == $currentVersionBranchYear && $month > $currentVersionBranchMonth) || $year > $currentVersionBranchYear) {
                    $availableMajorVersions[] = $value['name'];
                }
            } else {
                if ($tag > $currentVersionTag) {
                    $currentMinorVersions[] = $value['name'];
                }
            }
        }

        return $response->withJson([
            'currentMinorVersions'      => $currentMinorVersions,
            'availableMajorVersions'    => $availableMajorVersions,
            'currentVersion'            => $parameter['param_value_string']
        ]);
    }
}
