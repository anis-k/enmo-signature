<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Res Docserver association Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Docserver\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class ResDocserverModelAbstract
{

    /**
     * Retrieve the path of source file to process
     * @param string $resTable resource table
     * @param string $adrTable adr table
     * @param bigint $resId Id of the resource to process
     * @param string $adrType type of the address
     * $resTable, $adrTable, $resId, $adrType = 'DOC'
     * @return string
     */
    public static function getSourceResourcePath(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resTable']);
        ValidatorModel::notEmpty($aArgs, ['adrTable']);
        ValidatorModel::notEmpty($aArgs, ['resId']);
        
        if (!isset($aArgs['adrType'])) {
            $aArgs['adrType'] = 'DOC';
        }

        if ($aArgs['adrType'] == 'DOC') {
            $table = $aArgs['resTable'];
            $where = ['res_id=?'];
            $data  = [$aArgs['resId']];
        } else {
            $table = $aArgs['adrTable'];
            $where = ['res_id = ?', 'adr_type = ?'];
            $data  = [$aArgs['resId'], $aArgs['adrType']];
        }

        $aReturn = DatabaseModel::select([
            'select'    => [$table.'.path', $table.'.filename', $table.'.offset_doc', 'docservers.path_template'],
            'table'     => [$table, 'docservers'],
            'where'     => $where,
            'data'      => $data,
            'left_join' => [$table.'.docserver_id = docservers.docserver_id']
        ]);

        if (empty($aReturn)) {
            return false;
        }

        $resPath            = '';
        $resFilename        = '';
        if (isset($aReturn[0]['path'])) {
            $resPath = $aReturn[0]['path'];
        }
        if (isset($aReturn[0]['filename'])) {
            $resFilename = $aReturn[0]['filename'];
        }
        if (isset($aReturn[0]['offset_doc'])
            && $aReturn[0]['offset_doc'] <> ''
            && $aReturn[0]['offset_doc'] <> ' '
        ) {
            $sourceFilePath = $aReturn[0]['path']
                . $aReturn[0]['filename']
                . DIRECTORY_SEPARATOR . $aReturn[0]['offset_doc'];
        } else {
            $sourceFilePath = $resPath . $resFilename;
        }
        $resPathTemplate = '';
        if (isset($aReturn[0]['path_template'])) {
            $resPathTemplate = $aReturn[0]['path_template'];
        }

        $sourceFilePath = $resPathTemplate . $sourceFilePath;
        $sourceFilePath = str_replace('#', DIRECTORY_SEPARATOR, $sourceFilePath);

        return $sourceFilePath;
    }
}
