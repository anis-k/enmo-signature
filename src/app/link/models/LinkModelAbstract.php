<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Link Model
 * @author dev@maarch.org
 */

namespace Link\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

abstract class LinkModelAbstract
{
    public static function getByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aLinks = DatabaseModel::select([
            'select'    => ['*'],
            'table'     => ['res_linked'],
            'where'     => ['res_parent = ? OR res_child = ?'],
            'data'      => [$aArgs['resId'], $aArgs['resId']]
        ]);

        return $aLinks;
    }
}
