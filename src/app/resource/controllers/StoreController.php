<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Store Controller
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Resource\controllers;

use Attachment\models\AttachmentModel;
use Contact\models\ContactModel;
use Convert\controllers\ConvertThumbnailController;
use Convert\controllers\ConvertPdfController;
use Docserver\controllers\DocserverController;
use Resource\models\ChronoModel;
use SrcCore\models\ValidatorModel;
use Entity\models\EntityModel;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;
use User\models\UserModel;

class StoreController
{
    public static function storeResource(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['encodedFile', 'data', 'collId', 'table', 'fileFormat', 'status']);
        ValidatorModel::stringType($aArgs, ['collId', 'table', 'fileFormat', 'status']);
        ValidatorModel::arrayType($aArgs, ['data']);

        if (!in_array($aArgs['table'], ['res_letterbox', 'res_attachments'])) {
            return ['errors' => '[storeResource] Table not valid'];
        }

        try {
            $fileContent    = base64_decode(str_replace(['-', '_'], ['+', '/'], $aArgs['encodedFile']));
            $fileName       = 'tmp_file_' . rand() . '.' . $aArgs['fileFormat'];
            $tmpFilepath    = CoreConfigModel::getTmpPath() . $fileName;

            $file = fopen($tmpFilepath, 'w');
            fwrite($file, $fileContent);
            fclose($file);

            $storeResult = DocserverController::storeResourceOnDocServer([
                'collId'            => $aArgs['collId'],
                'docserverTypeId'   => 'DOC',
                'fileInfos'         => [
                    'tmpDir'            => CoreConfigModel::getTmpPath(),
                    'tmpFileName'       => $fileName
                ]
            ]);
            if (!empty($storeResult['errors'])) {
                return ['errors' => '[storeResource] ' . $storeResult['errors']];
            }
            unlink($tmpFilepath);

            $data = StoreController::prepareStorage([
                'data'          => $aArgs['data'],
                'docserverId'   => $storeResult['docserver_id'],
                'status'        => $aArgs['status'],
                'fileName'      => $storeResult['file_destination_name'],
                'fileFormat'    => $aArgs['fileFormat'],
                'fileSize'      => $storeResult['fileSize'],
                'path'          => $storeResult['destination_dir'],
                'fingerPrint'   => $storeResult['fingerPrint']
            ]);

            $resId = false;
            if ($aArgs['table'] == 'res_letterbox') {
                $resId = ResModel::create($data);
            } elseif ($aArgs['table'] == 'res_attachments') {
                $resId = AttachmentModel::create($data);
            }

            return $resId;
        } catch (\Exception $e) {
            return ['errors' => '[storeResource] ' . $e->getMessage()];
        }
    }

    public static function controlFingerPrint(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['pathInit', 'pathTarget']);
        ValidatorModel::stringType($aArgs, ['pathInit', 'pathTarget', 'fingerprintMode']);

        if (!file_exists($aArgs['pathInit'])) {
            return ['errors' => '[controlFingerprint] PathInit does not exist'];
        }
        if (!file_exists($aArgs['pathTarget'])) {
            return ['errors' => '[controlFingerprint] PathTarget does not exist'];
        }

        $fingerprint1 = StoreController::getFingerPrint(['filePath' => $aArgs['pathInit'], 'mode' => $aArgs['fingerprintMode']]);
        $fingerprint2 = StoreController::getFingerPrint(['filePath' => $aArgs['pathTarget'], 'mode' => $aArgs['fingerprintMode']]);

        if ($fingerprint1 != $fingerprint2) {
            return ['errors' => '[controlFingerprint] Fingerprints do not match: ' . $aArgs['pathInit'] . ' and ' . $aArgs['pathTarget']];
        }

        return true;
    }

    public static function getFingerPrint(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['filePath']);
        ValidatorModel::stringType($aArgs, ['filePath', 'mode']);

        if (empty($aArgs['mode']) || $aArgs['mode'] == 'NONE') {
            return '0';
        }

        return hash_file(strtolower($aArgs['mode']), $aArgs['filePath']);
    }

    public static function prepareStorage(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['data', 'docserverId', 'fileName', 'fileFormat', 'fileSize', 'path', 'fingerPrint']);
        ValidatorModel::stringType($aArgs, ['docserverId', 'status', 'fileName', 'fileFormat', 'path', 'fingerPrint']);
        ValidatorModel::arrayType($aArgs, ['data']);
        ValidatorModel::intVal($aArgs, ['fileSize']);

        $statusFound        = false;
        $typistFound        = false;
        $toAddressFound     = false;
        $userPrimaryEntity  = false;

        foreach ($aArgs['data'] as $key => $value) {
            $aArgs['data'][$key]['column'] = strtolower($value['column']);
        }

        foreach ($aArgs['data'] as $key => $value) {
            if (strtolower($value['type']) == 'integer' || strtolower($value['type']) == 'float') {
                if (empty($value['value'])) {
                    $aArgs['data'][$key]['value'] = '0';
                }
            } elseif (strtolower($value['type']) == 'string') {
                $aArgs['data'][$key]['value'] = str_replace(';', '', $value['value']);
                $aArgs['data'][$key]['value'] = str_replace('--', '', $value['value']);
            }

            if ($value['column'] == 'status') {
                $statusFound = true;
            } elseif ($value['column'] == 'typist') {
                $typistFound = true;
            } elseif ($value['column'] == 'custom_t10') {
                $theString = str_replace('>', '', $value['value']);
                $mail = explode("<", $theString);
                $user =  UserModel::getByEmail(['mail' => $mail[count($mail) -1], 'select' => ['user_id']]);
                if (!empty($user[0]['user_id'])) {
                    $toAddressFound = true;
                    $destUser = $user[0]['user_id'];
                    $entity = EntityModel::getByUserId(['userId' => $destUser, 'select' => ['entity_id']]);
                    if (!empty($entity[0]['entity_id'])) {
                        $userEntity = $entity[0]['entity_id'];
                        $userPrimaryEntity = true;
                    }
                } else {
                    $entity = EntityModel::getByEmail(['email' => $mail[count($mail) -1], 'select' => ['entity_id']]);
                    if (!empty($entity[0]['entity_id'])) {
                        $userPrimaryEntity = true;
                    }
                }
            }
        }

        $destUser   = empty($destUser) ? '' : $destUser;
        $userEntity = empty($userEntity) ? '' : $userEntity;

        if (!$typistFound && !$toAddressFound) {
            $aArgs['data'][] = [
                'column'    => 'typist',
                'value'     => 'auto',
                'type'      => 'string'
            ];
        }
        if (!$statusFound) {
            $aArgs['data'][] = [
                'column'    => 'status',
                'value'     => $aArgs['status'],
                'type'      => 'string'
            ];
        }
        if ($toAddressFound) {
            $aArgs['data'][] = [
                'column'    => 'dest_user',
                'value'     => $destUser,
                'type'      => 'string'
            ];
            if (!$typistFound) {
                $aArgs['data'][] = [
                    'column'    => 'typist',
                    'value'     => $destUser,
                    'type'      => 'string'
                ];
            }
        }
        if ($userPrimaryEntity) {
            $destinationFound = false;
            $initiatorFound = false;
            foreach ($aArgs['data'] as $key => $value) {
                if ($value['column'] == 'destination') {
                    if (empty($value['value'])) {
                        $aArgs['data'][$key]['value'] = $userEntity;
                    }
                    $destinationFound = true;
                } elseif ($value['column'] == 'initiator') {
                    if (empty($value['value'])) {
                        $aArgs['data'][$key]['value'] = $userEntity;
                    }
                    $initiatorFound = true;
                }
            }
            if (!$destinationFound) {
                $aArgs['data'][] = [
                    'column'    => 'destination',
                    'value'     => $userEntity,
                    'type'      => 'string'
                ];
            }
            if (!$initiatorFound) {
                $aArgs['data'][] = [
                    'column'    => 'initiator',
                    'value'     => $userEntity,
                    'type'      => 'string'
                ];
            }
        }

        $aArgs['data'][] = [
            'column'    => 'docserver_id',
            'value'     => $aArgs['docserverId'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'creation_date',
            'value'     => 'CURRENT_TIMESTAMP',
            'type'      => 'function'
        ];
        $aArgs['data'][] = [
            'column'    => 'path',
            'value'     => $aArgs['path'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'fingerprint',
            'value'     => $aArgs['fingerPrint'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'filename',
            'value'     => $aArgs['fileName'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'format',
            'value'     => $aArgs['fileFormat'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'filesize',
            'value'     => $aArgs['fileSize'],
            'type'      => 'int'
        ];

        $formatedData = [];
        foreach ($aArgs['data'] as $value) {
            $formatedData[$value['column']] = $value['value'];
        }

        return $formatedData;
    }

    public static function prepareExtStorage(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['data', 'resId']);
        ValidatorModel::arrayType($aArgs, ['data']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $processLimitDateFound  = false;
        $admissionDate          = null;

        foreach ($aArgs['data'] as $key => $value) {
            $aArgs['data'][$key]['column'] = strtolower($value['column']);
        }

        foreach ($aArgs['data'] as $value) {
            if ($value['column'] == 'process_limit_date') {
                $processLimitDateFound = true;
            }
            if ($value['column'] == 'category_id') {
                $categoryId = $value['value'];
            }
            if ($value['column'] == 'admission_date') {
                $admissionDate = $value['value'];
            }
        }

        if (!$processLimitDateFound) {
            $processLimitDate = ResModel::getStoredProcessLimitDate(['resId' => $aArgs['resId'], 'admissionDate' => $admissionDate]);

            $aArgs['data'][] = [
                'column'    => 'process_limit_date',
                'value'     => $processLimitDate,
                'type'      => 'date'
            ];
        }

        foreach ($aArgs['data'] as $key => $value) {
            if (strtolower($value['type']) == 'integer' || strtolower($value['type']) == 'float') {
                if ($value['value'] == '') {
                    $aArgs['data'][$key]['value'] = '0';
                }
                $aArgs['data'][$key]['value'] = str_replace(',', '.', $value['value']);
            }
            if ($value['column'] == 'alt_identifier' && empty($value['value']) && !empty($categoryId)) {
                $document = ResModel::getById(['resId' => $aArgs['resId'], 'select' => ['destination, type_id']]);
                $aArgs['data'][$key]['value'] = ChronoModel::getChrono(['id' => $categoryId, 'entityId' => $document['destination'], 'typeId' => $document['type_id']]);
            } elseif ($value['column'] == 'exp_contact_id' && !empty($value['value']) && !is_numeric($value['value'])) {
                $mail = explode('<', str_replace('>', '', $value['value']));
                $contact = ContactModel::getByEmail(['email' => $mail[count($mail) - 1], 'select' => ['contacts_v2.contact_id']]);
                if (!empty($contact['contact_id'])) {
                    $aArgs['data'][$key]['value'] = $contact['contact_id'];
                } else {
                    $aArgs['data'][$key]['value'] = 0;
                }
            } elseif ($value['column'] == 'address_id' && !empty($value['value']) && !is_numeric($value['value'])) {
                $mail = explode('<', str_replace('>', '', $value['value']));
                $contact = ContactModel::getByEmail(['email' => $mail[count($mail) - 1], 'select' => ['contact_addresses.id']]);
                if (!empty($contact['id'])) {
                    $aArgs['data'][$key]['value'] = $contact['ca_id'];
                } else {
                    $aArgs['data'][$key]['value'] = 0;
                }
            }
        }

        $formatedData = [];
        foreach ($aArgs['data'] as $value) {
            $formatedData[$value['column']] = $value['value'];
        }
        $formatedData['res_id'] = $aArgs['resId'];

        return $formatedData;
    }
}
