<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Contact Group Controller
 * @author dev@maarch.org
 */

namespace Contact\controllers;

use Contact\models\ContactGroupModel;
use Contact\models\ContactModel;
use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ContactGroupController
{
    public function get(Request $request, Response $response)
    {
        $hasService = ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin']);

        $user = UserModel::getByUserId(['select' => ['id'], 'userId' => $GLOBALS['userId']]);

        $contactsGroups = ContactGroupModel::get();
        foreach ($contactsGroups as $key => $contactsGroup) {
            if (!$contactsGroup['public'] && $user['id'] != $contactsGroup['owner'] && !$hasService) {
                unset($contactsGroups[$key]);
                continue;
            }
            $contactsGroups[$key]['position'] = $key;
            $contactsGroups[$key]['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['owner']]);
            $contactsGroups[$key]['nbContacts'] = ContactGroupModel::getListById(['id' => $contactsGroup['id'], 'select' => ['COUNT(1)']])[0]['count'];
        }
        
        return $response->withJson(['contactsGroups' => array_values($contactsGroups)]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        $contactsGroup = ContactGroupModel::getById(['id' => $aArgs['id']]);
        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts group not found']);
        }

        $user = UserModel::getByUserId(['select' => ['id'], 'userId' => $GLOBALS['userId']]);
        if ($contactsGroup['owner'] != $user['id'] && !ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $contactsGroup['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['owner']]);
        $contactsGroup['contacts'] = ContactGroupController::getFormattedListById(['id' => $aArgs['id']])['list'];
        $contactsGroup['nbContacts'] = count($contactsGroup['contacts']);

        return $response->withJson(['contactsGroup' => $contactsGroup]);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['description']);
        $check = $check && Validator::boolType()->validate($data['public']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $user = UserModel::getByUserId(['select' => ['id'], 'userId' => $GLOBALS['userId']]);
        $existingGroup = ContactGroupModel::get(['select' => [1], 'where' => ['label = ?', 'owner = ?'], 'data' => [$data['label'], $user['id']]]);
        if (!empty($existingGroup)) {
            return $response->withStatus(400)->withJson(['errors' => _CONTACTS_GROUP_LABEL_ALREADY_EXISTS]);
        }

        $data['public'] = $data['public'] ? 'true' : 'false';
        $data['owner'] = $user['id'];
        $data['entity_owner'] = ($GLOBALS['userId'] == 'superadmin' ? 'superadmin' : UserModel::getPrimaryEntityByUserId(['userId' => $GLOBALS['userId']])['entity_id']);

        $id = ContactGroupModel::create($data);

        HistoryController::add([
            'tableName' => 'contacts_groups',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _CONTACTS_GROUP_ADDED . " : {$data['label']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupCreation',
        ]);

        return $response->withJson(['contactsGroup' => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        $contactsGroup = ContactGroupModel::getById(['select' => ['owner'], 'id' => $aArgs['id']]);
        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts Group does not exist']);
        }

        $user = UserModel::getByUserId(['select' => ['id'], 'userId' => $GLOBALS['userId']]);
        if ($contactsGroup['owner'] != $user['id'] && !ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['description']);
        $check = $check && Validator::boolType()->validate($data['public']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $existingGroup = ContactGroupModel::get(['select' => [1], 'where' => ['label = ?', 'owner = ?', 'id != ?'], 'data' => [$data['label'], $user['id'], $aArgs['id']]]);
        if (!empty($existingGroup)) {
            return $response->withStatus(400)->withJson(['errors' => _CONTACTS_GROUP_LABEL_ALREADY_EXISTS]);
        }

        $data['id'] = $aArgs['id'];
        $data['public'] = $data['public'] ? 'true' : 'false';

        ContactGroupModel::update($data);

        HistoryController::add([
            'tableName' => 'contacts_groups',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _CONTACTS_GROUP_UPDATED . " : {$data['label']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        $contactsGroup = ContactGroupModel::getById(['select' => ['owner'], 'id' => $aArgs['id']]);
        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts Group does not exist']);
        }

        $user = UserModel::getByUserId(['select' => ['id'], 'userId' => $GLOBALS['userId']]);
        if ($contactsGroup['owner'] != $user['id'] && !ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        ContactGroupModel::delete(['id' => $aArgs['id']]);

        HistoryController::add([
            'tableName' => 'contacts_groups',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _CONTACTS_GROUP_DELETED . " : {$aArgs['id']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupSuppression',
        ]);

        $contactsGroups = ContactGroupModel::get();
        foreach ($contactsGroups as $key => $contactsGroup) {
            $contactsGroups[$key]['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['owner']]);
        }

        return $response->withJson(['success' => 'success']);
    }

    public function addContacts(Request $request, Response $response, array $aArgs)
    {
        $contactsGroup = ContactGroupModel::getById(['select' => ['owner', 'label'], 'id' => $aArgs['id']]);
        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts Group does not exist']);
        }

        $user = UserModel::getByUserId(['select' => ['id'], 'userId' => $GLOBALS['userId']]);
        if ($contactsGroup['owner'] != $user['id'] && !ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::arrayType()->notEmpty()->validate($data['contacts']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $rawList = ContactGroupModel::getListById(['select' => ['contact_addresses_id'], 'id' => $aArgs['id']]);
        $list = [];
        foreach ($rawList as $rawListItem) {
            $list[] = $rawListItem['contact_addresses_id'];
        }

        foreach ($data['contacts'] as $addressId) {
            if (!in_array($addressId, $list)) {
                ContactGroupModel::addContact(['id' => $aArgs['id'], 'addressId' => $addressId]);
            }
        }

        HistoryController::add([
            'tableName' => 'contacts_groups_lists',
            'recordId'  => $aArgs['id'],
            'eventType' => 'ADD',
            'info'      => _CONTACTS_GROUP_LIST_ADDED . " : {$contactsGroup['label']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupListCreation',
        ]);

        $contactsGroup = ContactGroupModel::getById(['id' => $aArgs['id']]);
        $contactsGroup['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['owner']]);
        $contactsGroup['contacts'] = ContactGroupController::getFormattedListById(['id' => $aArgs['id']])['list'];

        return $response->withJson(['contactsGroup' => $contactsGroup]);
    }

    public function deleteContact(Request $request, Response $response, array $aArgs)
    {
        $contactsGroup = ContactGroupModel::getById(['select' => ['owner', 'label'], 'id' => $aArgs['id']]);
        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts Group does not exist']);
        }

        $user = UserModel::getByUserId(['select' => ['id'], 'userId' => $GLOBALS['userId']]);
        if ($contactsGroup['owner'] != $user['id'] && !ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        ContactGroupModel::deleteContact(['id' => $aArgs['id'], 'addressId' => $aArgs['addressId']]);

        HistoryController::add([
            'tableName' => 'contacts_groups_lists',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _CONTACTS_GROUP_LIST_DELETED . " : {$contactsGroup['label']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupListSuppression',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public static function getFormattedListById(array $aArgs)
    {
        $list = ContactGroupModel::getListById(['select' => ['contact_addresses_id'], 'id' => $aArgs['id']]);

        $contacts = [];
        $position = 0;
        foreach ($list as $listItem) {
            $contact = ContactModel::getOnView([
                'select'    => [
                    'ca_id', 'firstname', 'lastname', 'contact_lastname', 'contact_firstname', 'society', 'address_num',
                    'address_street', 'address_town', 'address_postal_code', 'is_corporate_person'
                ],
                'where'     => ['ca_id = ?'],
                'data'      => [$listItem['contact_addresses_id']]
            ]);

            if (!empty($contact[0])) {
                $contacts[] = ContactGroupController::getFormattedContact(['contact' => $contact[0], 'position' => $position])['contact'];
                ++$position;
            }
        }

        return ['list' => $contacts];
    }

    public static function getFormattedContact(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['contact']);
        ValidatorModel::arrayType($aArgs, ['contact']);
        ValidatorModel::intType($aArgs, ['position']);

        $address = '';
        if (empty($aArgs['position'])) {
            $aArgs['position'] = 0;
        }
        if ($aArgs['contact']['is_corporate_person'] == 'Y') {
            $address.= $aArgs['contact']['firstname'];
            $address.= (empty($address) ? $aArgs['contact']['lastname'] : " {$aArgs['contact']['lastname']}");
            if (!empty($address)) {
                $address.= ', ';
            }
            if (!empty($aArgs['contact']['address_num'])) {
                $address.= $aArgs['contact']['address_num'] . ' ';
            }
            if (!empty($aArgs['contact']['address_street'])) {
                $address.= $aArgs['contact']['address_street'] . ' ';
            }
            if (!empty($aArgs['contact']['address_town'])) {
                $address.= $aArgs['contact']['address_town'] . ' ';
            }
            if (!empty($aArgs['contact']['address_postal_code'])) {
                $address.= $aArgs['contact']['address_postal_code'] . ' ';
            }
            $contact = [
                'position'  => $aArgs['position'],
                'addressId' => $aArgs['contact']['ca_id'],
                'contact'   => $aArgs['contact']['society'],
                'address'   => $address
            ];
        } else {
            if (!empty($aArgs['contact']['address_num'])) {
                $address.= $aArgs['contact']['address_num'] . ' ';
            }
            if (!empty($aArgs['contact']['address_street'])) {
                $address.= $aArgs['contact']['address_street'] . ' ';
            }
            if (!empty($aArgs['contact']['address_town'])) {
                $address.= $aArgs['contact']['address_town'] . ' ';
            }
            if (!empty($aArgs['contact']['address_postal_code'])) {
                $address.= $aArgs['contact']['address_postal_code'] . ' ';
            }

            $contact = [
                'position'  => $aArgs['position'],
                'addressId' => $aArgs['contact']['ca_id'],
                'contact'   => "{$aArgs['contact']['contact_firstname']} {$aArgs['contact']['contact_lastname']} {$aArgs['contact']['society']}",
                'address'   => $address
            ];
        }

        return ['contact' => $contact];
    }
}
