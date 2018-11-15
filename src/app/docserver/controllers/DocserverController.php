<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Docserver Controller
* @author dev@maarch.org
*/

namespace Docserver\controllers;

use SrcCore\models\ValidatorModel;
use Docserver\models\DocserverModel;

class DocserverController
{
    const FINGERPRINT_MODE = 'sha512';

    public static function storeResourceOnDocServer(array $args)
    {
        ValidatorModel::notEmpty($args, ['encodedFile', 'docserverType', 'format']);
        ValidatorModel::stringType($args, ['docserverType', 'format']);

        $docserver = DocserverModel::getByType(['type' => $args['docserverType'], 'select' => ['id', 'path', 'actual_size_number']]);
        if (empty($docserver)) {
            return ['errors' => '[storeRessourceOnDocserver] No available Docserver'];
        }

        $pathOnDocserver = DocserverController::createPathOnDocServer(['path' => $docserver['path']]);
        if (!empty($pathOnDocserver['errors'])) {
            return ['errors' => '[storeRessourceOnDocserver] ' . $pathOnDocserver['errors']];
        }

        $docinfo = DocserverController::getNextFileNameInDocServer(['pathOnDocserver' => $pathOnDocserver['pathToDocServer']]);
        if (!empty($docinfo['errors'])) {
            return ['errors' => '[storeRessourceOnDocserver] ' . $docinfo['errors']];
        }
        if (!DocserverController::isPathAvailable(['path' => $docinfo['destinationDir']])) {
            return ['errors' => '[storeRessourceOnDocserver] Path to docserver is not available'];
        }

        $docinfo['fileDestinationName'] .= ".{$args['format']}";
        $file = base64_decode($args['encodedFile']);
        file_put_contents($docinfo['destinationDir'] . $docinfo['fileDestinationName'], $file);
        chmod($docinfo['destinationDir'] . $docinfo['fileDestinationName'], 0770);

        DocserverModel::update([
            'set'   => [
                'actual_size_number'    => $docserver['actual_size_number'] + filesize($docinfo['destinationDir'] . $docinfo['fileDestinationName'])
            ],
            'where' => ['id = ?'],
            'data'  => [$docserver['id']]
        ]);

        return [
            'path'          => substr($docinfo['destinationDir'], strlen($docserver['path'])),
            'filename'      => $docinfo['fileDestinationName'],
            'fingerprint'   => DocserverController::getFingerPrint(['path' => ($docinfo['destinationDir'] . $docinfo['fileDestinationName'])])
        ];
    }

    public static function createPathOnDocServer(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['path']);
        ValidatorModel::stringType($aArgs, ['path']);

        if (!is_dir($aArgs['path'])) {
            return ['errors' => '[createPathOnDocServer] Path does not exist ' . $aArgs['path']];
        }

        error_reporting(0);
        umask(0022);

        $yearPath = $aArgs['path'] . date('Y') . '/';
        if (!is_dir($yearPath)) {
            mkdir($yearPath, 0770);
            if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($yearPath));
            }
            umask(0022);
            chmod($yearPath, 0770);
        }

        $monthPath = $yearPath . date('m') . '/';
        if (!is_dir($monthPath)) {
            mkdir($monthPath, 0770);
            if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($monthPath));
            }
            umask(0022);
            chmod($monthPath, 0770);
        }

        $pathToDS = $monthPath;

        return ['pathToDocServer' => $pathToDS];
    }

    public static function getNextFileNameInDocServer(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['pathOnDocserver']);
        ValidatorModel::stringType($aArgs, ['pathOnDocserver']);

        if (!is_dir($aArgs['pathOnDocserver'])) {
            return ['errors' => '[getNextFileNameInDocServer] PathOnDocserver does not exist'];
        }

        umask(0022);

        $aFiles = scandir($aArgs['pathOnDocserver']);
        array_shift($aFiles); // Remove . line
        array_shift($aFiles); // Remove .. line

        $filesNb = count($aFiles);
        if ($filesNb == 0) {
            $zeroOnePath = $aArgs['pathOnDocserver'] . '0001/';

            if (!mkdir($zeroOnePath, 0770)) {
                return ['errors' => '[getNextFileNameInDocServer] Directory creation failed: ' . $zeroOnePath];
            } else {
                if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                    exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($zeroOnePath));
                }
                umask(0022);
                chmod($zeroOnePath, 0770);

                return [
                    'destinationDir'        => $zeroOnePath,
                    'fileDestinationName'   => '0001_' . mt_rand(),
                ];
            }
        } else {
            $destinationDir = $aArgs['pathOnDocserver'] . str_pad(count($aFiles), 4, '0', STR_PAD_LEFT) . '/';
            $aFilesBis = scandir($aArgs['pathOnDocserver'] . strval(str_pad(count($aFiles), 4, '0', STR_PAD_LEFT)));
            array_shift($aFilesBis); // Remove . line
            array_shift($aFilesBis); // Remove .. line

            $filesNbBis = count($aFilesBis);
            if ($filesNbBis >= 1000) { //If number of files >= 1000 then creates a new subdirectory
                $zeroNumberPath = $aArgs['pathOnDocserver'] . str_pad($filesNb + 1, 4, '0', STR_PAD_LEFT) . '/';

                if (!mkdir($zeroNumberPath, 0770)) {
                    return ['errors' => '[getNextFileNameInDocServer] Directory creation failed: ' . $zeroNumberPath];
                } else {
                    if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                        exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($zeroNumberPath));
                    }
                    umask(0022);
                    chmod($zeroNumberPath, 0770);

                    return [
                        'destinationDir'        => $zeroNumberPath,
                        'fileDestinationName'   => '0001_' . mt_rand(),
                    ];
                }
            } else {
                $higher = $filesNbBis + 1;
                foreach ($aFilesBis as $value) {
                    $currentFileName = explode('.', $value);
                    if ($higher <= (int)$currentFileName[0]) {
                        $higher = (int)$currentFileName[0] + 1;
                    }
                }

                return [
                    'destinationDir'        => $destinationDir,
                    'fileDestinationName'   => str_pad($higher, 4, '0', STR_PAD_LEFT) . '_' . mt_rand(),
                ];
            }
        }
    }

    public static function getFingerPrint(array $args)
    {
        ValidatorModel::notEmpty($args, ['path']);
        ValidatorModel::stringType($args, ['path']);

        return hash_file(self::FINGERPRINT_MODE, $args['path']);
    }

    private static function isPathAvailable(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['path']);
        ValidatorModel::stringType($aArgs, ['path']);

        if (!is_dir($aArgs['path'])) {
            return false;
        }
        if (!is_readable($aArgs['path'])) {
            return false;
        }
        if (!is_writable($aArgs['path'])) {
            return false;
        }

        return true;
    }
}
