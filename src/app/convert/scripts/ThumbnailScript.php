<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Thumbnail Script
 * @author dev@maarch.org
 */

namespace Convert\scripts;

require 'vendor/autoload.php';

use Convert\controllers\ConvertThumbnailController;
use History\controllers\HistoryController;
use SrcCore\models\DatabasePDO;

//configPath    = $argv[1];
//id            = $argv[2];
//type          = $argv[3];
//userId        = $argv[4];

ThumbnailScript::convert(['configPath' => $argv[1], 'id' => $argv[2], 'type' => $argv[3], 'userId' => $argv[4]]);

class ThumbnailScript
{
    public static function convert(array $args)
    {
        DatabasePDO::reset();
        new DatabasePDO(['configPath' => $args['configPath']]);

        $GLOBALS['id'] = $args['userId'];

        $isConverted = ConvertThumbnailController::convert(['id' => $args['id'], 'type' => $args['type']]);
        if (!empty($isConverted['errors'])) {
            HistoryController::add([
                'code'          => 'KO',
                'objectType'    => $args['type'],
                'objectId'      => $args['id'],
                'type'          => 'THUMBNAIL',
                'message'       => '{thumbnailFailed}',
                'data'          => ['errors' => $isConverted['errors']]
            ]);
        }

        return $isConverted;
    }
}
