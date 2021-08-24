<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Watermark Controller
 * @author dev@maarch.org
 */

namespace Document\controllers;

use Document\models\DocumentModel;
use setasign\Fpdi\Tcpdf\Fpdi;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;

class WatermarkController
{
    public static function watermarkDocument(array $args)
    {
        ValidatorModel::notEmpty($args, ['config', 'encodedDocument', 'id']);
        ValidatorModel::intVal($args, ['id']);
        ValidatorModel::stringType($args, ['encodedDocument']);
        ValidatorModel::arrayType($args, ['config']);

        $watermark = $args['config'];

        if (empty($watermark['enabled']) || empty($watermark['text'])) {
            return null;
        }

        $mainDocument = DocumentModel::getById(['select' => ['*'] ,'id' => $args['id']]);
        $mainDocument['metadata'] = json_decode($mainDocument['metadata'], true);

        foreach ($mainDocument['metadata'] as $key => $metadatum) {
            $mainDocument['metadata.' . $key] = $metadatum;
        }
        unset($mainDocument['metadata']);

        $text = $watermark['text'];
        preg_match_all('/\[(.*?)\]/i', $watermark['text'], $matches);
        foreach ($matches[1] as $value) {
            if ($value == 'date_now') {
                $tmp = date('d-m-Y');
            } elseif ($value == 'hour_now') {
                $tmp = date('H:i');
            } else {
                $tmp = $mainDocument[$value] ?? '';
            }
            $text = str_replace("[{$value}]", $tmp, $text);
        }

        $configPath = CoreConfigModel::getConfigPath();
        $overrideFile = "{$configPath}/override/setasign/fpdi_pdf-parser/src/autoload.php";
        if (file_exists($overrideFile)) {
            require_once($overrideFile);
        }

        try {
            $watermarkFile = CoreConfigModel::getTmpPath() . "tmp_file_{$GLOBALS['id']}_" .rand(). "_watermark.pdf";
            file_put_contents($watermarkFile, base64_decode($args['encodedDocument']));

            $pdf = new Fpdi('P', 'pt');
            $nbPages = $pdf->setSourceFile($watermarkFile);
            $pdf->setPrintHeader(false);
            for ($i = 1; $i <= $nbPages; $i++) {
                $page = $pdf->importPage($i, 'CropBox');
                $size = $pdf->getTemplateSize($page);
                $pdf->AddPage($size['orientation'], $size);
                $pdf->useImportedPage($page);
                $pdf->SetFont('courier', '', 8); // Size 8
                $pdf->SetTextColor(0, 0, 0); // Color black
                $pdf->SetAlpha(0.5); // Opacity
                $pdf->Rotate(0); // No rotation
                $pdf->Text(8, $watermark['posY'], $text, false, false, true, 0, 0, $watermark['align']); // Position X = 8
            }
            $fileContent = $pdf->Output('', 'S');
        } catch (\Exception $e) {
            $fileContent = null;
        }

        return $fileContent;
    }
}
