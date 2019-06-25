<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ConfigurationControllerTest extends TestCase
{
    private static $ldapId = null;
    private static $emailId = null;
    private static $ldapLabel = null;
    private static $emailLabel = null;

    public function testCreate()
    {
        $configurationController = new \Configuration\controllers\ConfigurationController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        //EMAIL
        self::$emailLabel = 'Mon serveur email de secours ' . rand();
        $args = [
            'identifier'    => 'emailServer',
            'label'         => self::$emailLabel,
            'value'         => [
                'type'      => 'smtp',
                'host'      => '1.2.3.4',
                'port'      => 22,
                'user'      => 'mor',
                'password'  => 'pheus',
                'auth'      => true,
                'secure'    => 'tls',
                'from'      => 'test@test.com'
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(204, $response->getStatusCode());

        //LDAP
        self::$ldapLabel = 'Mon serveur ldap de secours ' . rand();
        $args = [
            'identifier'    => 'ldapServer',
            'label'         => self::$ldapLabel,
            'value'         => [
                'uri'           => '1.2.3.4',
                'ssl'           => false,
                'prefix'        => 'one',
                'suffix'        => 'sion',
                'baseDN'        => null
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(204, $response->getStatusCode());


        //Errors
        unset($args['value']['uri']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame("Body['value'] uri is empty or not a string", $responseBody->errors);

        unset($args['value']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame("Body value is empty or not an array", $responseBody->errors);

        unset($args['label']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame("Body label is empty or not a string", $responseBody->errors);

        unset($args['identifier']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame("Body is not set or empty", $responseBody->errors);
    }

    public function testGet()
    {
        $configurationController = new \Configuration\controllers\ConfigurationController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        //LDAP
        $fullRequest = $request->withQueryParams(['identifier' => 'ldapServer']);
        $response     = $configurationController->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->configurations);
        $this->assertNotEmpty($responseBody->configurations);
        foreach ($responseBody->configurations as $configuration) {
            if ($configuration->label == self::$ldapLabel) {
                self::$ldapId = $configuration->id;
            }
        }
        $this->assertNotEmpty(self::$ldapId);

        //EMAIL
        $fullRequest = $request->withQueryParams(['identifier' => 'emailServer']);
        $response     = $configurationController->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->configurations);
        $this->assertNotEmpty($responseBody->configurations);
        foreach ($responseBody->configurations as $configuration) {
            if ($configuration->label == self::$emailLabel) {
                self::$emailId = $configuration->id;
            }
        }
        $this->assertNotEmpty(self::$emailId);

        //CONNECTION
        $fullRequest = $request->withQueryParams(['identifier' => 'connection']);
        $response     = $configurationController->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->configurations);
        $this->assertInternalType('string', $responseBody->configurations->value);
        $this->assertInternalType('array', $responseBody->configurations->availableConnections);


        //Errors
        $response     = $configurationController->get($request, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('QueryParams identifier is empty or not a string', $responseBody->errors);
    }

    public function testGetById()
    {
        $configurationController = new \Configuration\controllers\ConfigurationController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        //LDAP
        $response     = $configurationController->getById($request, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$ldapId, $responseBody->configuration->id);
        $this->assertSame('ldapServer', $responseBody->configuration->identifier);
        $this->assertSame(self::$ldapLabel, $responseBody->configuration->label);
        $this->assertSame('1.2.3.4', $responseBody->configuration->value->uri);
        $this->assertSame(false, $responseBody->configuration->value->ssl);
        $this->assertSame('one', $responseBody->configuration->value->prefix);
        $this->assertSame('sion', $responseBody->configuration->value->suffix);
        $this->assertSame(null, $responseBody->configuration->value->baseDN);

        //EMAIL
        $response     = $configurationController->getById($request, new \Slim\Http\Response(), ['id' => self::$emailId]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$emailId, $responseBody->configuration->id);
        $this->assertSame('emailServer', $responseBody->configuration->identifier);
        $this->assertSame(self::$emailLabel, $responseBody->configuration->label);
        $this->assertSame('smtp', $responseBody->configuration->value->type);
        $this->assertSame('1.2.3.4', $responseBody->configuration->value->host);
        $this->assertSame(22, $responseBody->configuration->value->port);
        $this->assertSame('mor', $responseBody->configuration->value->user);
        $this->assertSame(true, $responseBody->configuration->value->auth);
        $this->assertSame('tls', $responseBody->configuration->value->secure);
        $this->assertSame('test@test.com', $responseBody->configuration->value->from);
    }

    public function testTestConnection()
    {
        $configurationController = new \Configuration\controllers\ConfigurationController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $fullRequest = $request->withQueryParams(['login' => 'smith', 'password' => 'agent']);
        $response     = $configurationController->testConnection($fullRequest, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(false, $responseBody->connection);
        $this->assertSame('Can\'t contact LDAP server', $responseBody->informations);


        //Errors
        $fullRequest = $request->withQueryParams(['login' => 'smith']);
        $response     = $configurationController->testConnection($fullRequest, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('QueryParams password is empty or not a string', $responseBody->errors);

        $response     = $configurationController->testConnection($request, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('QueryParams login is empty or not a string', $responseBody->errors);
    }

    public function testUpdate()
    {
        $configurationController = new \Configuration\controllers\ConfigurationController();

        //EMAIL
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'label'         => 'new label',
            'value'         => [
                'type'      => 'mail',
                'host'      => '5.6.7.8',
                'port'      => 24,
                'user'      => 'tri',
                'auth'      => true,
                'secure'    => 'ssl',
                'from'      => 'test2@test.com'
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$emailId]);
        $this->assertSame(204, $response->getStatusCode());

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $configurationController->getById($request, new \Slim\Http\Response(), ['id' => self::$emailId]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$emailId, $responseBody->configuration->id);
        $this->assertSame('emailServer', $responseBody->configuration->identifier);
        $this->assertSame('new label', $responseBody->configuration->label);
        $this->assertSame('mail', $responseBody->configuration->value->type);
        $this->assertSame('5.6.7.8', $responseBody->configuration->value->host);
        $this->assertSame(24, $responseBody->configuration->value->port);
        $this->assertSame('tri', $responseBody->configuration->value->user);
        $this->assertSame(true, $responseBody->configuration->value->auth);
        $this->assertSame('ssl', $responseBody->configuration->value->secure);
        $this->assertSame('test2@test.com', $responseBody->configuration->value->from);

        //LDAP
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'label'         => 'new label',
            'value'         => [
                'uri'           => '5.6.7.8',
                'ssl'           => true,
                'prefix'        => 'neo',
                'suffix'        => 'smith',
                'baseDN'        => 'DC=maarch,DC=com'
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $this->assertSame(204, $response->getStatusCode());

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $configurationController->getById($request, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$ldapId, $responseBody->configuration->id);
        $this->assertSame('ldapServer', $responseBody->configuration->identifier);
        $this->assertSame('new label', $responseBody->configuration->label);
        $this->assertSame('5.6.7.8', $responseBody->configuration->value->uri);
        $this->assertSame(true, $responseBody->configuration->value->ssl);
        $this->assertSame('neo', $responseBody->configuration->value->prefix);
        $this->assertSame('smith', $responseBody->configuration->value->suffix);
        $this->assertSame('DC=maarch,DC=com', $responseBody->configuration->value->baseDN);


        //Errors
        unset($args['value']['uri']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame("Body['value'] uri is empty or not a string", $responseBody->errors);

        unset($args['value']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame("Body value is empty or not an array", $responseBody->errors);

        unset($args['label']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $configurationController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame("Body is not set or empty", $responseBody->errors);
    }

    public function testDelete()
    {
        $configurationController = new \Configuration\controllers\ConfigurationController();

        //LDAP
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $configurationController->delete($request, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $this->assertSame(204, $response->getStatusCode());

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $configurationController->getById($request, new \Slim\Http\Response(), ['id' => self::$ldapId]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Configuration does not exist', $responseBody->errors);

        //EMAIL
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $configurationController->delete($request, new \Slim\Http\Response(), ['id' => self::$emailId]);
        $this->assertSame(204, $response->getStatusCode());

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $configurationController->getById($request, new \Slim\Http\Response(), ['id' => self::$emailId]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Configuration does not exist', $responseBody->errors);


        //Errors
        $response     = $configurationController->update($request, new \Slim\Http\Response(), ['id' => 'test']);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Route id is not an integer', $responseBody->errors);
    }
}
