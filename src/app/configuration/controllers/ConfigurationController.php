<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Configuration Controller
 * @author dev@maarch.org
 */

namespace Configuration\controllers;

use Configuration\models\ConfigurationModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\AuthenticationModel;
use User\controllers\UserController;

class ConfigurationController
{
    public function update(Request $request, Response $response, array $args)
    {
        if (!UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_configuration'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        $configuration = ConfigurationModel::getByIdentifier(['identifier' => $args['identifier']]);

        if ($args['identifier'] == 'emailServer') {
            $check = ConfigurationController::checkMailer($body);
            if (!empty($check['errors'])) {
                return $response->withStatus(400)->withJson(['errors' => $check['errors']]);
            }

            if ($body['auth'] && empty($body['password']) && !empty($configuration)) {
                $configuration['value'] = json_decode($configuration['value'], true);
                if (!empty($configuration['value']['password'])) {
                    $body['password'] = $configuration['value']['password'];
                }
            } elseif ($body['auth'] && !empty($body['password'])) {
                $body['password'] = AuthenticationModel::encrypt(['password' => $body['password']]);
            } elseif (!$body['auth']) {
                $body['user'] = null;
                $body['password'] = null;
            }
            $data = json_encode([
                'type'      => $body['type'],
                'host'      => $body['host'],
                'port'      => $body['port'],
                'user'      => empty($body['user']) ? null : $body['user'],
                'password'  => empty($body['password']) ? null : $body['password'],
                'auth'      => $body['auth'],
                'secure'    => $body['secure'],
                'from'      => $body['from'],
                'charset'   => empty($body['charset']) ? 'utf-8' : $body['charset']
            ]);
        }

        if (!empty($data)) {
            if (empty($configuration)) {
                ConfigurationModel::create(['identifier' => $args['identifier'], 'value' => $data]);
            } else {
                ConfigurationModel::update(['set' => ['value' => $data], 'where' => ['identifier = ?'], 'data' => [$args['identifier']]]);
            }
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'configurations',
            'objectId'      => $args['identifier'],
            'type'          => 'MODIFICATION',
            'message'       => '{configurationUpdated}'
        ]);

        return $response->withStatus(204);
    }

    private static function checkMailer(array $args)
    {
        if (!Validator::stringType()->notEmpty()->validate($args['type'])) {
            return ['errors' => 'Body type is empty or not a string'];
        }
        
        if ($args['type'] == 'smtp') {
            if (!Validator::stringType()->notEmpty()->validate($args['host'])) {
                return ['errors' => 'Body host is empty or not a string'];
            } elseif (!Validator::intVal()->notEmpty()->validate($args['port'])) {
                return ['errors' => 'Body port is empty or not an integer'];
            } elseif (!Validator::boolType()->validate($args['auth'])) {
                return ['errors' => 'Body auth is empty or not a boolean'];
            } elseif (!Validator::stringType()->notEmpty()->validate($args['secure'])) {
                return ['errors' => 'Body secure is empty or not a string'];
            } elseif (!Validator::stringType()->notEmpty()->validate($args['from'])) {
                return ['errors' => 'Body from is empty or not a string'];
            }
            if ($args['auth']) {
                if (!Validator::stringType()->notEmpty()->validate($args['user'])) {
                    return ['errors' => 'Body user is empty or not a string'];
                }
            }
        }

        return ['success' => 'success'];
    }
}
