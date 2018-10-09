<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ContactControllerTest extends TestCase
{
    private static $id = null;
    private static $addressId = null;

    public function testCreate()
    {
        $contactController = new \Contact\controllers\ContactController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'firstname'         => 'Hal',
            'lastname'          => 'Jordan',
            'contactType'       => '106',
            'contactPurposeId'  => '3',
            'isCorporatePerson' => 'N',
            'email'             => 'hal.jordan@glc.com',
            'society'           => 'Green Lantern Corps',
            'societyShort'      => 'GLC',
            'title'             => 'title1',
            'function'          => 'member',
            'addressNum'        => '1',
            'addressStreet'     => 'somewhere',
            'addressCountry'    => 'OA',
            'phone'             => '911',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->contactId);
        $this->assertInternalType('int', $responseBody->addressId);

        self::$id = $responseBody->contactId;
        self::$addressId = $responseBody->addressId;


        //  READ
        $contact = \Contact\models\ContactModel::getById(['id' => self::$id]);

        $this->assertInternalType('array', $contact);
        $this->assertSame(self::$id, $contact['contact_id']);
        $this->assertSame(106, $contact['contact_type']);
        $this->assertSame('Green Lantern Corps', $contact['society']);
        $this->assertSame('GLC', $contact['society_short']);
        $this->assertSame('Hal', $contact['firstname']);
        $this->assertSame('Jordan', $contact['lastname']);
        $this->assertSame('title1', $contact['title']);
        $this->assertSame('member', $contact['function']);
        $this->assertSame('superadmin', $contact['user_id']);
        $this->assertSame('SUPERADMIN', $contact['entity_id']);
        $this->assertSame('Y', $contact['enabled']);

        $contact = \Contact\models\ContactModel::getByAddressId(['addressId' => self::$addressId]);

        $this->assertInternalType('array', $contact);
        $this->assertSame(self::$addressId, $contact['id']);
        $this->assertSame(self::$id, $contact['contact_id']);
        $this->assertSame(3, $contact['contact_purpose_id']);
        $this->assertSame(null, $contact['firstname']);
        $this->assertSame(null, $contact['lastname']);
        $this->assertSame('hal.jordan@glc.com', $contact['email']);
        $this->assertSame('1', $contact['address_num']);
        $this->assertSame(null, $contact['address_town']);
        $this->assertSame('somewhere', $contact['address_street']);
        $this->assertSame('OA', $contact['address_country']);
        $this->assertSame('911', $contact['phone']);
        $this->assertSame('superadmin', $contact['user_id']);
        $this->assertSame('SUPERADMIN', $contact['entity_id']);
        $this->assertSame('Y', $contact['enabled']);
    }

    public function testCreateAddress()
    {
        $contactController = new \Contact\controllers\ContactController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            "contactPurposeId"  => 2,
            "email"             => "office@group.com",
            "phone"             => "+33120212223",
            "addressNum"        => "14",
            "addressStreet"     => "Avenue du Pérou",
            "addressZip"        => "75016",
            "addressTown"       => "Paris",
            "addressCountry"    => "France"
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactController->createAddress($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->addressId);

        $contact = \Contact\models\ContactModel::getByAddressId(['addressId' => $responseBody->addressId]);

        $this->assertInternalType('array', $contact);
        $this->assertSame($responseBody->addressId, $contact['id']);
        $this->assertSame(self::$id, $contact['contact_id']);
        $this->assertSame(2, $contact['contact_purpose_id']);
        $this->assertSame(null, $contact['firstname']);
        $this->assertSame(null, $contact['lastname']);
        $this->assertSame('office@group.com', $contact['email']);
        $this->assertSame('14', $contact['address_num']);
        $this->assertSame('Avenue du Pérou', $contact['address_street']);
        $this->assertSame('75016', $contact['address_postal_code']);
        $this->assertSame('Paris', $contact['address_town']);
        $this->assertSame('France', $contact['address_country']);
        $this->assertSame('+33120212223', $contact['phone']);
        $this->assertSame('superadmin', $contact['user_id']);
        $this->assertSame('SUPERADMIN', $contact['entity_id']);
        $this->assertSame('Y', $contact['enabled']);

        \SrcCore\models\DatabaseModel::delete([
            'table' => 'contact_addresses',
            'where' => ['id = ?'],
            'data'  => [$responseBody->addressId]
        ]);

        //  READ
        $contact = \Contact\models\ContactModel::getByAddressId(['addressId' => $responseBody->addressId]);
        $this->assertInternalType('array', $contact);
        $this->assertEmpty($contact);

        $aArgs = [
            "contactPurposeId"  => 2
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactController->createAddress($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Bad Request', $responseBody->errors);
    }

    public function testUpdate()
    {
        $contactController = new \Contact\controllers\ContactController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'firstname'         => 'Guy',
            'lastname'          => 'Gardner',
            'title'             => 'title2',
            'function'          => '2nd member',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $contact = \Contact\models\ContactModel::getById(['id' => self::$id]);

        $this->assertInternalType('array', $contact);
        $this->assertSame(self::$id, $contact['contact_id']);
        $this->assertSame(106, $contact['contact_type']);
        $this->assertSame('Green Lantern Corps', $contact['society']);
        $this->assertSame('GLC', $contact['society_short']);
        $this->assertSame('Guy', $contact['firstname']);
        $this->assertSame('Gardner', $contact['lastname']);
        $this->assertSame('title2', $contact['title']);
        $this->assertSame('2nd member', $contact['function']);
        $this->assertSame('superadmin', $contact['user_id']);
        $this->assertSame('SUPERADMIN', $contact['entity_id']);
        $this->assertSame('Y', $contact['enabled']);

        $aArgs = [
            'firstname'         => 'Guy',
            'lastname'          => 'Gardner',
            'title'             => 'title2',
            'function'          => '2nd member',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactController->update($fullRequest, new \Slim\Http\Response(), ['id' => -1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Contact does not exist', $responseBody->errors);
    }

    public function testUpdateAddress()
    {
        $contactController = new \Contact\controllers\ContactController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            "contact_purpose_id"    => 2,
            "email"                 => "updatedemail@mail.com",
            "phone"                 => "+66",
            "address_num"           => "23",
            "address_street"        => "Rue des GL",
            "address_postal_code"   => "75000",
            "address_town"          => "Paris",
            "address_country"       => "France"
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactController->updateAddress($fullRequest, new \Slim\Http\Response(), ['id' => self::$id, 'addressId' => self::$addressId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        $contact = \Contact\models\ContactModel::getByAddressId(['addressId' => self::$addressId]);

        $this->assertInternalType('array', $contact);
        $this->assertSame(self::$addressId, $contact['id']);
        $this->assertSame(self::$id, $contact['contact_id']);
        $this->assertSame(2, $contact['contact_purpose_id']);
        $this->assertSame(null, $contact['firstname']);
        $this->assertSame(null, $contact['lastname']);
        $this->assertSame('updatedemail@mail.com', $contact['email']);
        $this->assertSame('23', $contact['address_num']);
        $this->assertSame('Rue des GL', $contact['address_street']);
        $this->assertSame('75000', $contact['address_postal_code']);
        $this->assertSame('Paris', $contact['address_town']);
        $this->assertSame('France', $contact['address_country']);
        $this->assertSame('+66', $contact['phone']);
        $this->assertSame('superadmin', $contact['user_id']);
        $this->assertSame('SUPERADMIN', $contact['entity_id']);
        $this->assertSame('Y', $contact['enabled']);


        $aArgs = [
            "contact_purpose_id"    => 2,
            "email"                 => "updatedemail@mail.com",
            "phone"                 => "+66",
            "address_num"           => "23",
            "address_street"        => "Rue des GL",
            "address_postal_code"   => "75000",
            "address_town"          => "Paris",
            "address_country"       => "France"
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $contactController->updateAddress($fullRequest, new \Slim\Http\Response(), ['id' => -1, 'addressId' => self::$addressId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Contact or address do not exist', $responseBody->errors);
    }

    public function testGetContactCommunicationByContactId()
    {
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $contactController = new \Contact\controllers\ContactController();
        $response          = $contactController->getCommunicationByContactId($request, new \Slim\Http\Response(), ['contactId' => (string)self::$id]);
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
    }

    public function testDelete()
    {
        //  DELETE
        \SrcCore\models\DatabaseModel::delete([
            'table' => 'contacts_v2',
            'where' => ['contact_id = ?'],
            'data'  => [self::$id]
        ]);
        \SrcCore\models\DatabaseModel::delete([
            'table' => 'contact_addresses',
            'where' => ['id = ?'],
            'data'  => [self::$addressId]
        ]);

        //  READ
        $contact = \Contact\models\ContactModel::getById(['id' => self::$id]);
        $this->assertInternalType('array', $contact);
        $this->assertEmpty($contact);

        $contact = \Contact\models\ContactModel::getByAddressId(['addressId' => self::$addressId]);
        $this->assertInternalType('array', $contact);
        $this->assertEmpty($contact);
    }

    public function testControlLengthNameAfnor()
    {
        $name = \Contact\controllers\ContactController::controlLengthNameAfnor(['title' => 'title1', 'fullName' => 'Prénom NOM', 'strMaxLength' => 38]);

        $this->assertSame('Monsieur Prénom NOM', $name);

        $name = \Contact\controllers\ContactController::controlLengthNameAfnor(['title' => 'title3', 'fullName' => 'Prénom NOM TROP LOOOOOOOOOOOOONG', 'strMaxLength' => 38]);

        $this->assertSame('Mlle Prénom NOM TROP LOOOOOOOOOOOOONG', $name);
    }

    public function testAvailableReferential()
    {
        $contactController = new \Contact\controllers\ContactController();
        $availableReferential = $contactController->availableReferential();
        $this->assertInternalType('array', $availableReferential);
        $this->assertNotEmpty($availableReferential);
    }

    public function testGetFilling()
    {
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $contactController = new \Contact\controllers\ContactController();
        $response          = $contactController->getFilling($request, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertInternalType('array', (array)$responseBody->contactsFilling);
    }

    public function testUpdateFilling()
    {
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            "enable"            => true,
            "rating_columns"    => ["society", "function"],
            "first_threshold"   => 22,
            "second_threshold"  => 85
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $contactController = new \Contact\controllers\ContactController();
        $response          = $contactController->updateFilling($fullRequest, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        $response          = $contactController->getFilling($request, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertSame(true, $responseBody->contactsFilling->enable);
        $this->assertSame(22, $responseBody->contactsFilling->first_threshold);
        $this->assertSame(85, $responseBody->contactsFilling->second_threshold);
        $this->assertSame('society', $responseBody->contactsFilling->rating_columns[0]);
        $this->assertSame('function', $responseBody->contactsFilling->rating_columns[1]);

        $aArgs = [
            "enable"            => true,
            "first_threshold"   => 22,
            "second_threshold"  => 85
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response          = $contactController->updateFilling($fullRequest, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertSame('Bad Request', $responseBody->errors);
    }
}
