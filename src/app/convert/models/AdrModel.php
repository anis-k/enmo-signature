<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Adr Model
 * @author dev@maarch.org
 */

namespace Convert\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class AdrModel
{
    public static function getConvertedDocumentById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'type', 'collId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::boolType($aArgs, ['isVersion']);
        ValidatorModel::arrayType($aArgs, ['select']);

        if ($aArgs['collId'] == 'letterbox_coll') {
            $table = "adr_letterbox";
        } else if ($aArgs['isVersion']) {
            $table = "adr_attachments_version";
        } else {
            $table = "adr_attachments";
        }

        $attachment = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => ['res_id = ?', 'type = ?'],
            'data'      => [$aArgs['resId'], $aArgs['type']],
        ]);

        if (empty($attachment[0])) {
            return [];
        }

        return $attachment[0];
    }

    public static function getDocumentsAdr(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $addresses = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['adr_main_documents'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
        ]);

        return $addresses;
    }
}
