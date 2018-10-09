<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   FolderTypeModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Folder\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class FolderTypeModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select']);

        $folderType = DatabaseModel::select([
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['foldertypes']
        ]);

        return $folderType;
    }

    public static function getFolderTypeDocTypeFirstLevel(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select']);

        $folderType = DatabaseModel::select([
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['foldertypes_doctypes_level1'],
            'where'  => ['doctypes_first_level_id = ?'],
            'data'   => [$aArgs['doctypes_first_level_id']]
        ]);

        return $folderType;
    }

    public static function createFolderTypeDocTypeFirstLevel(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['foldertype_id', 'doctypes_first_level_id']);
        ValidatorModel::intVal($aArgs, ['foldertype_id', 'doctypes_first_level_id']);

        DatabaseModel::insert([
            'table'         => 'foldertypes_doctypes_level1',
            'columnsValues' => $aArgs
        ]);

        return true;
    }

    public static function deleteFolderTypeDocTypeFirstLevel(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['doctypes_first_level_id']);
        ValidatorModel::intVal($aArgs, ['doctypes_first_level_id']);

        DatabaseModel::delete([
            'table' => 'foldertypes_doctypes_level1',
            'where' => ['doctypes_first_level_id = ?'],
            'data'  => [$aArgs['doctypes_first_level_id']]
        ]);

        return true;
    }
}
