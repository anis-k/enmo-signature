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
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\AuthenticationModel;

class ConfigurationController
{
    public function getByIdentifier(Request $request, Response $response, array $args)
    {
        $configuration = ConfigurationModel::getByIdentifier(['identifier' => $args['identifier']]);

        if ($args['identifier'] == 'emailServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_email_configuration'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }

        } elseif ($args['identifier'] == 'ldapServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_ldap_configurations'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
        }

        if (!empty($data)) {
            if (empty($configuration)) {
                ConfigurationModel::create(['identifier' => $args['identifier'], 'value' => $data]);
            } else {
                ConfigurationModel::update(['set' => ['value' => $data], 'where' => ['identifier = ?'], 'data' => [$args['identifier']]]);
            }
        }

        return $response->withJson(['errors' => 'Privilege forbidden']);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $body = $request->getParsedBody();

        $configuration = ConfigurationModel::getByIdentifier(['identifier' => $args['identifier']]);

        if ($args['identifier'] == 'emailServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_email_configuration'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }

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
        } elseif ($args['identifier'] == 'ldapServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_ldap_configurations'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }

            $check = ConfigurationController::checkLdapConfigurations($body);
            if (!empty($check['errors'])) {
                return $response->withStatus(400)->withJson(['errors' => $check['errors']]);
            }

            $data = json_encode($body);
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

    private static function checkLdapConfigurations(array $configurations)
    {
        if (!Validator::arrayType()->notEmpty()->validate($configurations)) {
            return ['errors' => 'Body is empty or not an array'];
        }

        foreach ($configurations as $key => $configuration) {
            if (!Validator::stringType()->notEmpty()->validate($configuration['uri'])) {
                return ['errors' => "Body[{$key}] uri is empty or not a string"];
            } elseif (!Validator::boolType()->validate($configuration['ssl'])) {
                return ['errors' => "Body[{$key}] ssl is empty or not a boolean"];
            }
        }

        return ['success' => 'success'];
    }
}
