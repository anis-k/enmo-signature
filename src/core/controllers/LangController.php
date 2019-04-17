<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Url Controller
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class LangController
{
    public static function get(array $args = [])
    {
        ValidatorModel::stringType($args, ['lang']);

        if (empty($args['lang'])) {
            $user = UserModel::getById(['select' => ['preferences'], 'id' => $GLOBALS['id']]);
            $user['preferences'] = json_decode($user['preferences'], true);
            $args['lang'] = $user['preferences']['lang'];
        }

        $lang = [];
        if (is_file("lang/{$args['lang']}.json")) {
            $file = file_get_contents("lang/{$args['lang']}.json");
            $langFile = json_decode($file, true);
            if (!empty($langFile['lang'])) {
                $lang = $langFile['lang'];
            }
        }

        return $lang;
    }

    public function getByLang(Request $request, Response $response, array $args)
    {
        if (!is_file("lang/{$args['lang']}.json")) {
            return $response->withStatus(400)->withJson(['errors' => 'Lang does not exist']);
        }

        $file = file_get_contents("lang/{$args['lang']}.json");
        $lang = json_decode($file, true);

        return $response->withJson($lang);
    }

    public static function getAvailableLanguages()
    {
        $files = array_diff(scandir('lang/'), ['..', '.']);

        $languages = [];
        foreach ($files as $value) {
            $languages[] = str_replace('.json', '', $value);
        }

        return $languages;
    }
}
