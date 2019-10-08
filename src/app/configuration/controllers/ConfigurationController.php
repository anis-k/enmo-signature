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
    const CONNECTION_MODES  = ['default', 'ldap'];

    public function get(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['identifier'])) {
            return $response->withStatus(400)->withJson(['errors' => 'QueryParams identifier is empty or not a string']);
        }

        $select = ['id', 'label'];
        if ($queryParams['identifier'] == 'emailServer' && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_email_configuration'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        } elseif ($queryParams['identifier'] == 'ldapServer' && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_connections'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        } elseif ($queryParams['identifier'] == 'connection') {
            $select[] = 'value';
        }

        $configurations = ConfigurationModel::getByIdentifier(['identifier' => $queryParams['identifier'], 'select' => $select]);
        if ($queryParams['identifier'] == 'connection') {
            $ldapConfigurations = ConfigurationModel::getByIdentifier(['identifier' => 'ldapServer', 'select' => [1]]);
            $configurations = $configurations[0];
            $configurations['value'] = json_decode($configurations['value']);
            $configurations['availableConnections'] = [['id' => 'default', 'allowed' => true], ['id' => 'ldap', 'allowed' => !empty($ldapConfigurations)]];
        }

        return $response->withJson(['configurations' => $configurations]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        $configuration = ConfigurationModel::getById(['id' => $args['id']]);
        if (empty($configuration)) {
            return $response->withStatus(400)->withJson(['errors' => 'Configuration does not exist']);
        }

        $configuration['value'] = json_decode($configuration['value'], true);

        if ($configuration['identifier'] == 'emailServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_email_configuration'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
            $configuration['value']['passwordAlreadyExists'] = !empty($configuration['value']['password']);
            $configuration['value']['password'] = '';
        } elseif ($configuration['identifier'] == 'ldapServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_connections'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
            $ldapConfigurations = ConfigurationModel::getByIdentifier(['identifier' => 'ldapServer', 'select' => [1]]);
            $configuration['count'] = count($ldapConfigurations);
        }

        return $response->withJson(['configuration' => $configuration]);
    }

    public function create(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::stringType()->notEmpty()->length(1, 64)->validate($body['identifier'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body identifier is empty, not a string or longer than 64']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        } elseif (!Validator::arrayType()->notEmpty()->validate($body['value'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body value is empty or not an array']);
        }

        if ($body['identifier'] == 'emailServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_email_configuration'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }

            $check = ConfigurationController::checkMailer($body['value']);
            if (!empty($check['errors'])) {
                return $response->withStatus(400)->withJson(['errors' => $check['errors']]);
            }

            if ($body['value']['auth'] && !empty($body['value']['password'])) {
                $body['value']['password'] = AuthenticationModel::encrypt(['password' => $body['value']['password']]);
            } elseif (!$body['value']['auth']) {
                $body['value']['user'] = null;
                $body['value']['password'] = null;
            }
            $data = json_encode([
                'type'      => $body['value']['type'],
                'host'      => $body['value']['host'],
                'port'      => $body['value']['port'],
                'user'      => empty($body['value']['user']) ? null : $body['value']['user'],
                'password'  => empty($body['value']['password']) ? null : $body['value']['password'],
                'auth'      => $body['value']['auth'],
                'secure'    => $body['value']['secure'],
                'from'      => $body['value']['from'],
                'charset'   => empty($body['value']['charset']) ? 'utf-8' : $body['value']['charset']
            ]);
        } elseif ($body['identifier'] == 'ldapServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_connections'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }

            $check = ConfigurationController::checkLdapConfiguration($body['value']);
            if (!empty($check['errors'])) {
                return $response->withStatus(400)->withJson(['errors' => $check['errors']]);
            }

            $data = json_encode([
                'uri'       => $body['value']['uri'],
                'ssl'       => $body['value']['ssl'],
                'prefix'    => empty($body['value']['prefix']) ? null : $body['value']['prefix'],
                'suffix'    => empty($body['value']['suffix']) ? null : $body['value']['suffix'],
                'baseDN'    => empty($body['value']['baseDN']) ? null : $body['value']['baseDN']
            ]);
        }

        if (empty($data)) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $id = ConfigurationModel::create(['identifier' => $body['identifier'], 'label' => $body['label'], 'value' => $data]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'configurations',
            'objectId'      => $id,
            'type'          => 'CREATION',
            'message'       => "{configurationAdded} : {$body['label']}",
            'data'          => ['identifier' => $body['identifier']]
        ]);

        return $response->withStatus(204);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $body = $request->getParsedBody();

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        } elseif (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        } elseif (!Validator::notEmpty()->validate($body['value'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body value is empty or not an array']);
        }

        $configuration = ConfigurationModel::getById(['id' => $args['id']]);
        if (empty($configuration)) {
            return $response->withStatus(400)->withJson(['errors' => 'Configuration does not exist']);
        }

        if ($configuration['identifier'] == 'emailServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_email_configuration'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }

            $check = ConfigurationController::checkMailer($body['value']);
            if (!empty($check['errors'])) {
                return $response->withStatus(400)->withJson(['errors' => $check['errors']]);
            }

            if ($body['value']['auth'] && empty($body['value']['password'])) {
                $configuration['value'] = json_decode($configuration['value'], true);
                if (!empty($configuration['value']['password'])) {
                    $body['value']['password'] = $configuration['value']['password'];
                }
            } elseif ($body['value']['auth'] && !empty($body['value']['password'])) {
                $body['value']['password'] = AuthenticationModel::encrypt(['password' => $body['value']['password']]);
            } elseif (!$body['value']['auth']) {
                $body['value']['user'] = null;
                $body['value']['password'] = null;
            }

            $data = json_encode([
                'type'      => $body['value']['type'],
                'host'      => $body['value']['host'],
                'port'      => $body['value']['port'],
                'user'      => empty($body['value']['user']) ? null : $body['value']['user'],
                'password'  => empty($body['value']['password']) ? null : $body['value']['password'],
                'auth'      => $body['value']['auth'],
                'secure'    => $body['value']['secure'],
                'from'      => $body['value']['from'],
                'charset'   => empty($body['value']['charset']) ? 'utf-8' : $body['value']['charset']
            ]);
        } elseif ($configuration['identifier'] == 'ldapServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_connections'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
            $check = ConfigurationController::checkLdapConfiguration($body['value']);
            if (!empty($check['errors'])) {
                return $response->withStatus(400)->withJson(['errors' => $check['errors']]);
            }

            $data = json_encode([
                'uri'       => $body['value']['uri'],
                'ssl'       => $body['value']['ssl'],
                'prefix'    => empty($body['value']['prefix']) ? null : $body['value']['prefix'],
                'suffix'    => empty($body['value']['suffix']) ? null : $body['value']['suffix'],
                'baseDN'    => empty($body['value']['baseDN']) ? null : $body['value']['baseDN']
            ]);
        } elseif ($configuration['identifier'] == 'connection') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_connections'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
            if (!in_array($body['value'], ConfigurationController::CONNECTION_MODES)) {
                return $response->withStatus(400)->withJson(['errors' => 'Connection forbidden']);
            }
            if ($body['value'] == 'ldap') {
                $ldapConfigurations = ConfigurationModel::getByIdentifier(['identifier' => 'ldapServer', 'select' => [1]]);
                if (empty($ldapConfigurations)) {
                    return $response->withStatus(400)->withJson(['errors' => 'No LDAP connections available']);
                }
            }

            $data = json_encode($body['value']);
        }

        if (empty($data)) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        ConfigurationModel::update(['set' => ['label' => $body['label'], 'value' => $data], 'where' => ['id = ?'], 'data' => [$args['id']]]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'configurations',
            'objectId'      => $args['id'],
            'type'          => 'MODIFICATION',
            'message'       => "{configurationUpdated} : {$body['label']}",
            'data'          => ['identifier' => $configuration['identifier']]
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $configuration = ConfigurationModel::getById(['id' => $args['id']]);
        if (empty($configuration)) {
            return $response->withStatus(400)->withJson(['errors' => 'Configuration does not exist']);
        }

        if ($configuration['identifier'] == 'emailServer' && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_email_configuration'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        } elseif ($configuration['identifier'] == 'ldapServer') {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_connections'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
            $ldapConfigurations = ConfigurationModel::getByIdentifier(['identifier' => 'ldapServer', 'select' => [1]]);
            if (count($ldapConfigurations) == 1) {
                $connectionConfiguration = ConfigurationModel::getByIdentifier(['identifier' => 'connection', 'select' => ['value']]);
                if ($connectionConfiguration[0]['value'] == '"ldap"') {
                    return $response->withStatus(400)->withJson(['errors' => 'Ldap connection is activated']);
                }
            }
        } elseif ($configuration['identifier'] == 'connection') {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        ConfigurationModel::delete(['id' => $args['id']]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'configurations',
            'objectId'      => $args['id'],
            'type'          => 'SUPPRESSION',
            'message'       => "{configurationDeleted} : {$configuration['label']}",
            'data'          => ['identifier' => $configuration['identifier']]
        ]);

        return $response->withStatus(204);
    }

    public function testConnection(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['login'])) {
            return $response->withStatus(400)->withJson(['errors' => 'QueryParams login is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($queryParams['password'])) {
            return $response->withStatus(400)->withJson(['errors' => 'QueryParams password is empty or not a string']);
        } elseif (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $configuration = ConfigurationModel::getById(['id' => $args['id']]);
        if (empty($configuration)) {
            return $response->withStatus(400)->withJson(['errors' => 'Configuration does not exist']);
        }

        if ($configuration['identifier'] != 'ldapServer' || !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_connections'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $ldapConfiguration = json_decode($configuration['value'], true);
        $uri = ($ldapConfiguration['ssl'] === true ? "LDAPS://{$ldapConfiguration['uri']}" : $ldapConfiguration['uri']);
        $ldap = @ldap_connect($uri);
        if ($ldap === false) {
            $error = 'Ldap connect failed : uri is maybe wrong';
            return $response->withJson(['connection' => false, 'informations' => $error]);
        }
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 10);
        $login = (!empty($ldapConfiguration['prefix']) ? $ldapConfiguration['prefix'] . '\\' . $queryParams['login'] : $queryParams['login']);
        $login = (!empty($ldapConfiguration['suffix']) ? $login . $ldapConfiguration['suffix'] : $login);
        if (!empty($ldapConfiguration['baseDN'])) { //OpenLDAP
            $search = @ldap_search($ldap, $ldapConfiguration['baseDN'], "(uid={$login})", ['dn']);
            if ($search === false) {
                $error = 'Ldap search failed : baseDN is maybe wrong => ' . ldap_error($ldap);
                return $response->withJson(['connection' => false, 'informations' => $error]);
            }
            $entries = ldap_get_entries($ldap, $search);
            $login = $entries[0]['dn'];
        }
        $authenticated = @ldap_bind($ldap, $login, $queryParams['password']);
        if (!$authenticated) {
            $error = ldap_error($ldap);
            return $response->withJson(['connection' => false, 'informations' => $error]);
        }

        return $response->withJson(['connection' => true, 'informations' => 'success']);
    }

    private static function checkMailer(array $args)
    {
        if (!Validator::stringType()->notEmpty()->validate($args['type'])) {
            return ['errors' => 'Body type is empty or not a string'];
        }
        
        if ($args['type'] == 'smtp') {
            if (!Validator::stringType()->notEmpty()->validate($args['host'])) {
                return ['errors' => 'Body[\'value\'] host is empty or not a string'];
            } elseif (!Validator::intVal()->notEmpty()->validate($args['port'])) {
                return ['errors' => 'Body[\'value\'] port is empty or not an integer'];
            } elseif (!Validator::boolType()->validate($args['auth'])) {
                return ['errors' => 'Body[\'value\'] auth is empty or not a boolean'];
            } elseif (!Validator::stringType()->validate($args['secure'])) {
                return ['errors' => 'Body[\'value\'] secure is not a string'];
            } elseif (!Validator::stringType()->notEmpty()->validate($args['from'])) {
                return ['errors' => 'Body[\'value\'] from is empty or not a string'];
            }
            if ($args['auth']) {
                if (!Validator::stringType()->notEmpty()->validate($args['user'])) {
                    return ['errors' => 'Body[\'value\'] user is empty or not a string'];
                }
            }
        }

        return ['success' => 'success'];
    }

    private static function checkLdapConfiguration(array $configuration)
    {
        if (!Validator::stringType()->notEmpty()->validate($configuration['uri'])) {
            return ['errors' => "Body['value'] uri is empty or not a string"];
        } elseif (!Validator::boolType()->validate($configuration['ssl'])) {
            return ['errors' => "Body['value'] ssl is empty or not a boolean"];
        }

        return ['success' => 'success'];
    }
}
