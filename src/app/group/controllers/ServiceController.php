<?php

namespace Group\controllers;

use Group\models\ServiceModel;
use SrcCore\models\ValidatorModel;

class ServiceController
{
    public static function getMenuServicesByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $rawServicesStoredInDB = ServiceModel::getByUserId(['userId' => $aArgs['userId']]);
        $servicesStoredInDB = [];
        foreach ($rawServicesStoredInDB as $value) {
            $servicesStoredInDB[] = $value['service_id'];
        }

        $menu = ServiceModel::getApplicationServicesByUserServices(['userServices' => $servicesStoredInDB, 'type' => 'menu']);
        $menuModules = ServiceModel::getModulesServicesByUserServices(['userServices' => $servicesStoredInDB, 'type' => 'menu']);
        $menu = array_merge($menu, $menuModules);

        return $menu;
    }
}
