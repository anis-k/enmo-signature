<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
 * @brief Action Model
 * @author dev@maarch.org
 */

namespace Action\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class ActionModel
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);

        $actions = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['actions'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy']
        ]);

        return $actions;
    }
}
