<?php

/**
* Copyright Maarch since 2008 under license.
* See LICENSE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Auto Complete Controller
* @author dev@maarch.org
*/

namespace SrcCore\controllers;

use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class AutoCompleteController
{
    const LIMIT = 50;
    const TINY_LIMIT = 5;

    public static function getUsers(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['search'])) {
            return $response->withStatus(400)->withJson(['errors' => 'QueryParams search is empty or not a string']);
        }

        $requestData = AutoCompleteController::getDataForRequest([
            'search'        => $queryParams['search'],
            'fields'        => '(firstname ilike ? OR lastname ilike ? OR email ilike ?)',
            'where'         => ['"isRest" = ?'],
            'data'          => ['false'],
            'fieldsNumber'  => 3
        ]);

        $users = UserModel::get([
            'select'    => ['id', 'firstname', 'lastname', 'email', 'substitute', 'signature_modes'],
            'where'     => $requestData['where'],
            'data'      => $requestData['data'],
            'orderBy'   => ['lastname'],
            'limit'     => self::LIMIT
        ]);

        $validSignatureModes = CoreConfigModel::getSignatureModes();
        $validSignatureModes = array_column($validSignatureModes, 'id');
        foreach ($users as $key => $user) {
            $users[$key]['substitute']     = !empty($user['substitute']);
            $users[$key]['signatureModes'] = array_reverse(array_values(array_intersect($validSignatureModes, json_decode($user['signature_modes'], true))));
            unset($users[$key]['signature_modes']);
        }
        
        return $response->withJson($users);
    }

    public static function getDataForRequest(array $args)
    {
        ValidatorModel::notEmpty($args, ['search', 'fields', 'fieldsNumber']);
        ValidatorModel::stringType($args, ['search', 'fields']);
        ValidatorModel::arrayType($args, ['where', 'data']);
        ValidatorModel::intType($args, ['fieldsNumber']);

        $searchItems = explode(' ', $args['search']);

        foreach ($searchItems as $item) {
            if (strlen($item) >= 2) {
                $args['where'][] = $args['fields'];
                for ($i = 0; $i < $args['fieldsNumber']; $i++) {
                    $args['data'][] = "%{$item}%";
                }
            }
        }

        return ['where' => $args['where'], 'data' => $args['data']];
    }

    public static function getInsensitiveFieldsForRequest(array $args)
    {
        ValidatorModel::notEmpty($args, ['fields']);
        ValidatorModel::arrayType($args, ['fields']);

        $fields = [];
        foreach ($args['fields'] as $key => $field) {
            $fields[$key] = "unaccent({$field}::text)";
            $fields[$key] .= " ilike unaccent(?::text)";
        }
        $fields = implode(' OR ', $fields);
        $fields = "({$fields})";

        return $fields;
    }
}
