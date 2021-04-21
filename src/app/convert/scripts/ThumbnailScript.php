<?php

/**
 * Copyright Maarch since 2008 under license.
 * See LICENSE.txt file at the root folder for more details.
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
use Document\models\DocumentModel;
use History\controllers\HistoryController;
use SrcCore\models\DatabasePDO;

//configPath    = $argv[1];
//id            = $argv[2];
//type          = $argv[3];
//userId        = $argv[4];
//page          = $argv[5]; Optionnal

ThumbnailScript::convert(['configPath' => $argv[1], 'id' => $argv[2], 'type' => $argv[3], 'userId' => $argv[4], 'page' => $argv[5] ?? null]);

class ThumbnailScript
{
    public static function convert(array $args)
    {
        DatabasePDO::reset();
        new DatabasePDO(['configPath' => $args['configPath']]);

        $GLOBALS['id'] = $args['userId'];

        $document = DocumentModel::getById(['select' => ['status'], 'id' => $args['id']]);
        DocumentModel::update([
            'set'   => ['status' => 'CONVERTING'],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        if (isset($args['page'])) {
            $isConverted = ConvertThumbnailController::convertOnePage(['configPath' => $args['configPath'], 'id' => $args['id'], 'type' => $args['type'], 'page' => $args['page']]);
        } else {
            $isConverted = ConvertThumbnailController::convert(['configPath' => $args['configPath'], 'id' => $args['id'], 'type' => $args['type']]);
        }
        if (!empty($isConverted['errors'])) {
            DocumentModel::update([
                'set'   => ['status' => 'ERROR'],
                'where' => ['id = ?'],
                'data'  => [$args['id']]
            ]);

            HistoryController::add([
                'code'          => 'KO',
                'objectType'    => $args['type'],
                'objectId'      => $args['id'],
                'type'          => 'THUMBNAIL',
                'message'       => '{thumbnailFailed}',
                'data'          => ['errors' => $isConverted['errors']]
            ]);
        } else {
            if ($document['status'] != 'CONVERTING') {
                DocumentModel::update([
                    'set'   => ['status' => 'READY'],
                    'where' => ['id = ?'],
                    'data'  => [$args['id']]
                ]);
            }
        }

        return $isConverted;
    }
}
