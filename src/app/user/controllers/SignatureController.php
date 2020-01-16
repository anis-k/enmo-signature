<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Signature Controller
* @author dev@maarch.org
*/

namespace User\controllers;

use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\SignatureModel;
use User\models\UserModel;

class SignatureController
{
    public function get(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
            $user = UserModel::getById(['id' => $args['id'], 'select' => ['substitute']]);
            if (empty($user) || $user['substitute'] != $GLOBALS['id']) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
        }

        $docserver = DocserverModel::getByType(['type' => 'SIGNATURE', 'select' => ['path']]);
        if (empty($docserver['path']) || !is_dir($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver \'SIGNATURE\' does not exist']);
        }

        $where = ['user_id = ?'];
        if ($GLOBALS['id'] != $args['id']) {
            $where[] = 'substituted = true';
        }
        $rawSignatures = SignatureModel::get([
            'select'    => ['id', 'path', 'filename', 'fingerprint', 'substituted'],
            'where'     => $where,
            'data'      => [$args['id']],
            'orderBy'   => ['id DESC']
        ]);


        $signatures = [];
        foreach ($rawSignatures as $signature) {
            $pathToSignature = $docserver['path'] . $signature['path'] . $signature['filename'];
            if (is_file($pathToSignature)) {
                $fingerprint = DocserverController::getFingerPrint(['path' => $pathToSignature]);
                if ($signature['fingerprint'] == $fingerprint) {
                    $signatures[] = [
                        'id'                => $signature['id'],
                        'substituted'       => $signature['substituted'],
                        'encodedSignature'  => base64_encode(file_get_contents($pathToSignature))
                    ];
                }
            }
        }

        return $response->withJson(['signatures' => $signatures]);
    }

    public function create(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        $check = Validator::notEmpty()->validate($body['encodedSignature']);
        $check = $check && Validator::stringType()->notEmpty()->validate($body['format']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $signature  = base64_decode($body['encodedSignature']);
        $finfo      = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType   = $finfo->buffer($signature);
        $type       = explode('/', $mimeType);
        $size       = strlen($signature);
        if ($type[0] != 'image') {
            return $response->withStatus(400)->withJson(['errors' => 'Signature is not an image']);
        } elseif ($size > 1000000) {
            return $response->withStatus(400)->withJson(['errors' => 'Max file size reached (1 MB)']);
        }

        $storeInfos = DocserverController::storeResourceOnDocServer([
            'encodedFile'       => $body['encodedSignature'],
            'format'            => $body['format'],
            'docserverType'     => 'SIGNATURE'
        ]);

        if (!empty($storeInfos['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => $storeInfos['errors']]);
        }

        $id = SignatureModel::create([
            'userId'                => $args['id'],
            'path'                  => $storeInfos['path'],
            'filename'              => $storeInfos['filename'],
            'fingerprint'           => $storeInfos['fingerprint'],
            'external_application'  => null
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'signatures',
            'objectId'      => $id,
            'type'          => 'CREATION',
            'message'       => '{userSignatureAdded}',
            'data'          => ['userId' => $args['id']]
        ]);

        return $response->withJson(['signatureId' => $id]);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        SignatureModel::delete(['where' => ['user_id = ?', 'id = ?'], 'data' => [$args['id'], $args['signatureId']]]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'signatures',
            'objectId'      => $args['signatureId'],
            'type'          => 'SUPPRESSION',
            'message'       => '{userSignatureDeleted}',
            'data'          => ['userId' => $args['id']]
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function updateExternalSignatures(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $user = UserModel::getById(['select' => [1], 'id' => $args['id']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $body = $request->getParsedBody();

        if (!Validator::arrayType()->notEmpty()->validate($body['signatures'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body signature is empty or not an array']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['externalApplication'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body externalApplication is empty or not a string']);
        }

        foreach ($body['signatures'] as $key => $signature) {
            if (!Validator::notEmpty()->validate($signature['encodedSignature'])) {
                return $response->withStatus(400)->withJson(['errors' => "Body signatures[{$key}] encodedSignature is empty"]);
            } elseif (!Validator::stringType()->notEmpty()->validate($signature['format'])) {
                return $response->withStatus(400)->withJson(['errors' => "Body signatures[{$key}] format is empty or not a string"]);
            }
        }

        SignatureModel::delete(['where' => ['user_id = ?', 'external_application = ?'], 'data' => [$args['id'], $body['externalApplication']]]);

        foreach ($body['signatures'] as $signature) {
            $storeInfos = DocserverController::storeResourceOnDocServer([
                'encodedFile'       => $signature['encodedSignature'],
                'format'            => $signature['format'],
                'docserverType'     => 'SIGNATURE'
            ]);
            if (!empty($storeInfos['errors'])) {
                return $response->withStatus(500)->withJson(['errors' => $storeInfos['errors']]);
            }

            $id = SignatureModel::create([
                'userId'                => $args['id'],
                'path'                  => $storeInfos['path'],
                'filename'              => $storeInfos['filename'],
                'fingerprint'           => $storeInfos['fingerprint'],
                'external_application'  => $body['externalApplication']
            ]);

            HistoryController::add([
                'code'          => 'OK',
                'objectType'    => 'signatures',
                'objectId'      => $id,
                'type'          => 'CREATION',
                'message'       => '{userSignatureAdded}',
                'data'          => ['userId' => $args['id'], 'externalApplication' => $body['externalApplication']]
            ]);
        }

        return $response->withStatus(204);
    }

    public function updateSubstituted(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $user = UserModel::getById(['select' => [1], 'id' => $args['id']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $body = $request->getParsedBody();

        if (!Validator::boolType()->validate($body['substituted'])) {
            return $response->withStatus(400)->withJson(['errors' => "Body substituted is not set or not a boolean"]);
        }

        SignatureModel::update([
            'set'   => ['substituted' => $body['substituted'] ? 'true' : 'false'],
            'where' => ['user_id = ?', 'id = ?'],
            'data'  => [$args['id'], $args['signatureId']]
        ]);

        return $response->withStatus(204);
    }
}
