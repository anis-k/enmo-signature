<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Report Model Abstract
* @author dev@maarch.org
*/

namespace Report\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;

abstract class ReportModelAbstract
{
    public static function getByGroupId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $reports = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/reports/xml/reports.xml']);
        if ($loadedXml) {
            foreach ($loadedXml->REPORT as $value) {
                if ((string)$value->ENABLED == "true") {
                    $reports[] = [
                        'id'                => (string)$value->ID,
                        'label'             => constant((string)$value->LABEL),
                        'desc'              => constant((string)$value->DESCRIPTION),
                        'url'               => (string)$value->URL,
                        'in_menu_reports'   => (string)$value->IN_MENU_REPORTS,
                        'origin'            => (string)$value->ORIGIN,
                        'module'            => (string)$value->MODULE,
                        'module_label'      => (string)$value->MODULE_LABEL,
                        'checked'           => false
                    ];
                }
            }
            
            $aReturn = DatabaseModel::select([
                'select'    => ['*'],
                'table'     => ['usergroups_reports'],
                'where'     => ['group_id = ?'],
                'data'      => [$aArgs['groupId']]
            ]);
                
            $selectedReports = [];
            foreach ($aReturn as $value) {
                $selectedReports[] = $value['report_id'];
            }

            foreach ($reports as $key => $value) {
                $reports[$key]['checked'] = in_array($value['id'], $selectedReports);
            }
        }

        return $reports;
    }

    public static function addForGroupId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'reportId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'reportId']);

        DatabaseModel::insert([
            'table'         => 'usergroups_reports',
            'columnsValues' => [
                'group_id'  => $aArgs['groupId'],
                'report_id' => $aArgs['reportId']
            ]
        ]);

        return true;
    }

    public static function deleteForGroupId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'reportIds']);
        ValidatorModel::stringType($aArgs, ['groupId']);
        ValidatorModel::arrayType($aArgs, ['reportIds']);

        DatabaseModel::delete([
            'table' => 'usergroups_reports',
            'where' => ['group_id = ?', 'report_id in (?)'],
            'data'  => [$aArgs['groupId'], $aArgs['reportIds']]
        ]);

        return true;
    }
}
