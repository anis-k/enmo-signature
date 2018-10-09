<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   DoctypeExtModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Doctype\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;

class DoctypeExtModelAbstract
{
    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::select(
            [
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['mlb_doctype_ext'],
            'where'  => ['type_id = ?'],
            'data'   => [$aArgs['id']]
            ]
        );

        if (empty($aReturn[0])) {
            return [];
        }

        $aReturn = $aReturn[0];
       
        return $aReturn;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['type_id', 'process_mode']);
        ValidatorModel::intVal($aArgs, ['type_id']);

        DatabaseModel::insert([
            'table'         => 'mlb_doctype_ext',
            'columnsValues' => $aArgs
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['type_id', 'process_mode']);
        ValidatorModel::intVal($aArgs, ['type_id']);
        
        DatabaseModel::update([
            'table'     => 'mlb_doctype_ext',
            'set'       => $aArgs,
            'where'     => ['type_id = ?'],
            'data'      => [$aArgs['type_id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['type_id']);
        ValidatorModel::intVal($aArgs, ['type_id']);

        DatabaseModel::delete([
            'table' => 'mlb_doctype_ext',
            'where' => ['type_id = ?'],
            'data'  => [$aArgs['type_id']]
        ]);

        return true;
    }
}
