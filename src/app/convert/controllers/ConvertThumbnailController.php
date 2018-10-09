<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Convert Thumbnail Controller
 * @author dev@maarch.org
 */

namespace Convert\controllers;

use Attachment\models\AttachmentModel;
use Convert\models\AdrModel;
use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Parameter\models\ParameterModel;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;

class ConvertThumbnailController
{
    public static function convert(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['collId', 'resId']);
        ValidatorModel::stringType($aArgs, ['collId']);
        ValidatorModel::intVal($aArgs, ['resId', 'outgoingId']);
        ValidatorModel::boolType($aArgs, ['isOutgoingVersion','isVersion']);

        if ($aArgs['collId'] == 'letterbox_coll') {
            if (empty($aArgs['outgoingId'])) {
                $resource = ResModel::getById(['resId' => $aArgs['resId'], 'select' => ['docserver_id', 'path', 'filename']]);
                $convertedDocument = AdrModel::getConvertedDocumentById([
                    'select' => ['docserver_id','path', 'filename'],
                    'resId' => $aArgs['resId'],
                    'collId' => $aArgs['collId'],
                    'type' => 'PDF',
                    'isVersion' => false
                ]);
            } else {
                $resource = AttachmentModel::getById(['id' => $aArgs['outgoingId'], 'isVersion' => $aArgs['isOutgoingVersion'], 'select' => ['docserver_id', 'path', 'filename']]);
                $convertedDocument = AdrModel::getConvertedDocumentById([
                    'select' => ['docserver_id','path', 'filename'],
                    'resId' => $aArgs['outgoingId'],
                    'collId' => 'attachments_coll',
                    'type' => 'PDF',
                    'isVersion' => $aArgs['isOutgoingVersion']
                ]);
            }
        } else {
            $resource = AttachmentModel::getById(['id' => $aArgs['resId'], 'isVersion' => $aArgs['isVersion'], 'select' => ['docserver_id', 'path', 'filename']]);
            $convertedDocument = AdrModel::getConvertedDocumentById([
                'select' => ['docserver_id','path', 'filename'],
                'resId' => $aArgs['resId'],
                'collId' => $aArgs['collId'],
                'type' => 'PDF',
                'isVersion' => $aArgs['isVersion']
            ]);
        }

        if (empty($resource)) {
            return ['errors' => '[ConvertThumbnail] Resource does not exist'];
        }

        if (empty($convertedDocument)) {
            $docserver = DocserverModel::getByDocserverId(['docserverId' => $resource['docserver_id'], 'select' => ['path_template']]);
            if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
                return ['errors' => '[ConvertThumbnail] Docserver does not exist'];
            }

            $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $resource['path']) . $resource['filename'];
            if (!file_exists($pathToDocument)) {
                return ['errors' => '[ConvertThumbnail] Document does not exist on docserver'];
            }
        } else {
            $docserver = DocserverModel::getByDocserverId(['docserverId' => $convertedDocument['docserver_id'], 'select' => ['path_template']]);
            $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $convertedDocument['path']) . $convertedDocument['filename'];
            if (!file_exists($pathToDocument)) {
                return ['errors' => '[ConvertThumbnail] Document does not exist on docserver'];
            }
        }

        $ext = pathinfo($pathToDocument, PATHINFO_EXTENSION);
        $filename = pathinfo($pathToDocument, PATHINFO_FILENAME);
        $tmpPath = CoreConfigModel::getTmpPath();
        $fileNameOnTmp = rand() . $filename;

        if (in_array($ext, ['maarch', 'html'])) {
            if ($ext == 'maarch') {
                copy($pathToDocument, "{$tmpPath}{$fileNameOnTmp}.html");
                $pathToDocument = "{$tmpPath}{$fileNameOnTmp}.html";
            }
            $command = "wkhtmltoimage --height 600 --width 400 --quality 100 --zoom 0.2 "
                . escapeshellarg($pathToDocument) . ' ' . escapeshellarg("{$tmpPath}{$fileNameOnTmp}.png");
        } else {
            $size = '750x900';
            $parameter = ParameterModel::getById(['id' => 'thumbnailsSize', 'select' => ['param_value_string']]);
            if (!empty($parameter) && preg_match('/^[0-9]{3,4}[x][0-9]{3,4}$/', $parameter['param_value_string'])) {
                $size = $parameter['param_value_string'];
            }
            $command = "convert -thumbnail {$size} -background white -alpha remove "
                . escapeshellarg($pathToDocument) . '[0] ' . escapeshellarg("{$tmpPath}{$fileNameOnTmp}.png");
        }
        exec($command.' 2>&1', $output, $return);

        if ($return !== 0) {
            return ['errors' => "[ConvertThumbnail] ".implode(" ", $output)];
        }

        $storeResult = DocserverController::storeResourceOnDocServer([
            'collId'    => $aArgs['collId'],
            'fileInfos' => [
                'tmpDir'        => $tmpPath,
                'tmpFileName'   => $fileNameOnTmp . '.png',
            ],
            'docserverTypeId'   => 'TNL'
        ]);

        if (!empty($storeResult['errors'])) {
            return ['errors' => "[ConvertThumbnail] {$storeResult['errors']}"];
        }

        if ($aArgs['collId'] == 'letterbox_coll') {
            AdrModel::createDocumentAdr([
                'resId'         => $aArgs['resId'],
                'type'          => 'TNL',
                'docserverId'   => $storeResult['docserver_id'],
                'path'          => $storeResult['destination_dir'],
                'filename'      => $storeResult['file_destination_name'],
            ]);
        } else {
            AdrModel::createAttachAdr([
                'resId'         => $aArgs['resId'],
                'type'          => 'TNL',
                'docserverId'   => $storeResult['docserver_id'],
                'path'          => $storeResult['destination_dir'],
                'filename'      => $storeResult['file_destination_name'],
                'isVersion'     => $aArgs['isVersion'],
            ]);
        }

        return true;
    }
}
