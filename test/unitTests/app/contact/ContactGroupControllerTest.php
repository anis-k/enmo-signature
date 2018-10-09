<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

use PHPUnit\Framework\TestCase;

class ContactGroupControllerTest extends TestCase
{
    private static $id = null;


    public function testCreate()
    {
        $contactGroupController = new \Contact\controllers\ContactGroupController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'label'             => 'Groupe petition',
            'description'       => 'Groupe de petition',
            'public'            => true
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactGroupController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$id = $responseBody->contactsGroup;

        $this->assertInternalType('int', self::$id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $contactGroupController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $user = \User\models\UserModel::getByUserId(['select' => ['id'], 'userId' => 'superadmin']);
        $this->assertSame(self::$id, $responseBody->contactsGroup->id);
        $this->assertSame('Groupe petition', $responseBody->contactsGroup->label);
        $this->assertSame('Groupe de petition', $responseBody->contactsGroup->description);
        $this->assertSame(true, $responseBody->contactsGroup->public);
        $this->assertSame($user['id'], $responseBody->contactsGroup->owner);
        $this->assertSame('superadmin', $responseBody->contactsGroup->entity_owner);
        $this->assertInternalType('string', $responseBody->contactsGroup->labelledOwner);
        $this->assertInternalType('array', $responseBody->contactsGroup->contacts);
    }

    public function testGet()
    {
        $contactGroupController = new \Contact\controllers\ContactGroupController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $contactGroupController->get($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->contactsGroups);
        $this->assertNotNull($responseBody->contactsGroups);
    }

    public function testUpdate()
    {
        $contactGroupController = new \Contact\controllers\ContactGroupController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'label'             => 'Groupe petition updated',
            'description'       => 'Groupe de petition updated',
            'public'            => false
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactGroupController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $contactGroupController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->contactsGroup->id);
        $this->assertSame('Groupe petition updated', $responseBody->contactsGroup->label);
        $this->assertSame('Groupe de petition updated', $responseBody->contactsGroup->description);
        $this->assertSame(false, $responseBody->contactsGroup->public);
        $this->assertSame('superadmin', $responseBody->contactsGroup->entity_owner);
        $this->assertInternalType('string', $responseBody->contactsGroup->labelledOwner);
        $this->assertInternalType('array', $responseBody->contactsGroup->contacts);
    }

    public function testAddContacts()
    {
        $contactGroupController = new \Contact\controllers\ContactGroupController();

        $contacts = \Contact\models\ContactModel::getOnView([
            'select'    => ['ca_id'],
            'limit'     => 1
        ]);

        if (!empty($contacts[0])) {
            //  UPDATE
            $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
            $request        = \Slim\Http\Request::createFromEnvironment($environment);

            $aArgs = [
                'contacts'  => [$contacts[0]['ca_id']]
            ];
            $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

            $response     = $contactGroupController->addContacts($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
            $responseBody = json_decode((string)$response->getBody());

            $this->assertSame(self::$id, $responseBody->contactsGroup->id);
            $this->assertNotEmpty($responseBody->contactsGroup);
            $this->assertNotEmpty($responseBody->contactsGroup->contacts);
            $this->assertSame($contacts[0]['ca_id'], $responseBody->contactsGroup->contacts[0]->addressId);
            $this->assertSame(0, $responseBody->contactsGroup->contacts[0]->position);
            $this->assertInternalType('string', $responseBody->contactsGroup->contacts[0]->contact);
            $this->assertInternalType('string', $responseBody->contactsGroup->contacts[0]->address);
        }
    }

    public function testDeleteContacts()
    {
        $contactGroupController = new \Contact\controllers\ContactGroupController();

        $contacts = \Contact\models\ContactModel::getOnView([
            'select'    => ['ca_id'],
            'limit'     => 1
        ]);

        if (!empty($contacts[0])) {
            //  UPDATE
            $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
            $request        = \Slim\Http\Request::createFromEnvironment($environment);

            $response     = $contactGroupController->deleteContact($request, new \Slim\Http\Response(), ['id' => self::$id, 'addressId' => $contacts[0]['ca_id']]);
            $responseBody = json_decode((string)$response->getBody());

            $this->assertSame('success', $responseBody->success);
        }

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $contactGroupController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $user = \User\models\UserModel::getByUserId(['select' => ['id'], 'userId' => 'superadmin']);
        $this->assertSame(self::$id, $responseBody->contactsGroup->id);
        $this->assertSame($user['id'], $responseBody->contactsGroup->owner);
        $this->assertSame('superadmin', $responseBody->contactsGroup->entity_owner);
        $this->assertInternalType('string', $responseBody->contactsGroup->labelledOwner);
        $this->assertInternalType('array', $responseBody->contactsGroup->contacts);
        $this->assertEmpty($responseBody->contactsGroup->contacts);
    }

    public function testDelete()
    {
        $contactGroupController = new \Contact\controllers\ContactGroupController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $contactGroupController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $contactGroupController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('Contacts group not found', $responseBody->errors);
    }
}
