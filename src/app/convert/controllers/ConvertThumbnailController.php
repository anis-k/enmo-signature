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


use Docserver\controllers\DocserverController;
use Docserver\models\AdrModel;
use Docserver\models\DocserverModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;

class ConvertThumbnailController
{
    public static function convert(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'type']);
        ValidatorModel::stringType($args, ['type']);
        ValidatorModel::intVal($args, ['id']);

        if ($args['type'] == 'document') {
            $adr = AdrModel::getDocumentsAdr([
                'select'  => ['path', 'filename'],
                'where'   => ['main_document_id = ?', 'type = ?'],
                'data'    => [$args['id'], 'DOC']
            ]);
            $docserverType = 'DOC';
        } elseif ($args['type'] == 'attachment') {
            $adr = AdrModel::getAttachmentsAdr([
                'select'    => ['path', 'filename'],
                'where'     => ['attachment_id = ?', 'type = ?'],
                'data'      => [$args['id'], 'ATTACH']
            ]);
            $docserverType = 'ATTACH';
        }

        if (empty($adr) || empty($docserverType)) {
            return ['errors' => 'Document does not exist'];
        }

        $docserver = DocserverModel::getByType(['type' => $docserverType, 'select' => ['path']]);
        if (empty($docserver['path']) || !is_dir($docserver['path'])) {
            return ['errors' => 'Docserver does not exist'];
        }

        $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!is_file($pathToDocument) || !is_readable($pathToDocument)) {
            return ['errors' => 'Document not found on docserver or not readable'];
        }

        $filename = pathinfo($pathToDocument, PATHINFO_FILENAME);
        $tmpPath = CoreConfigModel::getTmpPath();

        $img = new \Imagick();
        $img->pingImage($pathToDocument);
        $pageCount = $img->getNumberImages();

        $i = 0;
        while ($i < $pageCount) {
            $fileNameOnTmp = rand() . $filename;

            $command = ConvertThumbnailController::getConvertCommand() . " "
                . escapeshellarg($pathToDocument) . "[{$i}] " . escapeshellarg("{$tmpPath}{$fileNameOnTmp}.png");
            exec($command.' 2>&1', $output, $return);

            if ($return !== 0) {
                return ['errors' => "[ConvertThumbnail] Convert command failed for page {$i} : ".implode(" ", $output)];
            }

            $resource = file_get_contents("{$tmpPath}{$fileNameOnTmp}.png");

            $storeInfos = DocserverController::storeResourceOnDocServer([
                'encodedFile'     => base64_encode($resource),
                'format'          => 'png',
                'docserverType'   => $docserverType
            ]);
            if (!empty($storeInfos['errors'])) {
                return ['errors' => $storeInfos['errors']];
            }

            unlink("{$tmpPath}{$fileNameOnTmp}.png");

            if ($args['type'] == 'document') {
                AdrModel::createDocumentAdr([
                    'documentId'    => $args['id'],
                    'type'          => 'TNL' . ($i + 1),
                    'path'          => $storeInfos['path'],
                    'filename'      => $storeInfos['filename'],
                    'fingerprint'   => $storeInfos['fingerprint']
                ]);
            } elseif ($args['type'] == 'attachment') {
                AdrModel::createAttachmentAdr([
                    'attachmentId'   => $args['id'],
                    'type'           => 'TNL' . ($i + 1),
                    'path'           => $storeInfos['path'],
                    'filename'       => $storeInfos['filename'],
                    'fingerprint'    => $storeInfos['fingerprint']
                ]);
            }
            ++$i;
        }

        return true;
    }

    public static function convertOnePage(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'page']);
        ValidatorModel::intVal($args, ['id', 'page']);

        $adr = AdrModel::getDocumentsAdr([
            'select'  => ['path', 'filename'],
            'where'   => ['main_document_id = ?', 'type = ?'],
            'data'    => [$args['id'], 'DOC']
        ]);
        if (empty($adr)) {
            return ['errors' => 'Document does not exist'];
        }

        $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
        if (empty($docserver['path']) || !is_dir($docserver['path'])) {
            return ['errors' => 'Docserver does not exist'];
        }

        $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!is_file($pathToDocument) || !is_readable($pathToDocument)) {
            return ['errors' => 'Document not found on docserver or not readable'];
        }

        $filename = pathinfo($pathToDocument, PATHINFO_FILENAME);
        $tmpPath = CoreConfigModel::getTmpPath();

        $img = new \Imagick();
        $img->pingImage($pathToDocument);
        $pageCount = $img->getNumberImages();
        if ($pageCount < $args['page']) {
            return ['errors' => 'Page does not exist'];
        }

        $fileNameOnTmp = rand() . $filename;

        $convertPage = $args['page'] - 1;
        $command = ConvertThumbnailController::getConvertCommand() . " "
            . escapeshellarg($pathToDocument) . "[{$convertPage}] " . escapeshellarg("{$tmpPath}{$fileNameOnTmp}.png");
        exec($command.' 2>&1', $output, $return);

        if ($return !== 0) {
            return ['errors' => "[ConvertThumbnail] Convert command failed for page {$args['page']} : ".implode(" ", $output)];
        }

        $resource = file_get_contents("{$tmpPath}{$fileNameOnTmp}.png");

        $storeInfos = DocserverController::storeResourceOnDocServer([
            'encodedFile'     => base64_encode($resource),
            'format'          => 'png',
            'docserverType'   => 'DOC'
        ]);
        if (!empty($storeInfos['errors'])) {
            return ['errors' => $storeInfos['errors']];
        }

        unlink("{$tmpPath}{$fileNameOnTmp}.png");

        AdrModel::createDocumentAdr([
            'documentId'    => $args['id'],
            'type'          => 'TNL' . $args['page'],
            'path'          => $storeInfos['path'],
            'filename'      => $storeInfos['filename'],
            'fingerprint'   => $storeInfos['fingerprint']
        ]);

        return true;
    }

    /**
     * [Choose between graphicImage and imageMagics]
     */
    public function getConvertCommand() {

        $command = 'gm version';

        exec($command.' 2>&1', $output, $return);

        if ($return !== 0) {
            return "convert -density 500x500 -quality 100 -background white -alpha remove";
        } else {
            return "gm convert -density 500x500 -quality 100 -background white +matte";
        }
    }
}
