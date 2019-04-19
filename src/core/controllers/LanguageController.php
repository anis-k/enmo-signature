<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Language Controller
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class LanguageController
{
    public static function get(array $args = [])
    {
        ValidatorModel::stringType($args, ['lang']);

        if (empty($args['lang'])) {
            $user = UserModel::getById(['select' => ['preferences'], 'id' => $GLOBALS['id']]);
            $user['preferences'] = json_decode($user['preferences'], true);
            $args['lang'] = $user['preferences']['lang'];
        }

        $language = LanguageController::getLanguage(['language' => $args['lang']]);

        return $language['lang'];
    }

    public function getByLang(Request $request, Response $response, array $args)
    {
        $language = LanguageController::getLanguage(['language' => $args['lang']]);

        return $response->withJson($language);
    }

    public static function getAvailableLanguages()
    {
        $files = array_diff(scandir('lang'), ['..', '.']);

        $loadedXml = CoreConfigModel::getConfig();
        if (!empty((string)$loadedXml->config->customLangPathDirectory)) {
            $overloadDirectory = (string)$loadedXml->config->customLangPathDirectory;
            $files = array_merge($files, array_diff(scandir($overloadDirectory), ['..', '.']));
        }

        $files = array_unique($files);

        $languages = [];
        foreach ($files as $value) {
            $languages[] = str_replace('.json', '', $value);
        }

        return $languages;
    }

    private static function getLanguage(array $args)
    {
        ValidatorModel::notEmpty($args, ['language']);
        ValidatorModel::stringType($args, ['language']);

        $language = ['lang' => []];

        if (is_file("lang/{$args['language']}.json")) {
            $file = file_get_contents("lang/{$args['language']}.json");
            $language = json_decode($file, true);
        }

        $loadedXml = CoreConfigModel::getConfig();
        if (!empty((string)$loadedXml->config->customLangPathDirectory)) {
            $overloadDirectory = rtrim((string)$loadedXml->config->customLangPathDirectory, '/');
            $overloadFile = "{$overloadDirectory}/{$args['language']}.json";

            if (is_file($overloadFile)) {
                $file = file_get_contents($overloadFile);
                $overloadedLanguage = json_decode($file, true);
                foreach ($overloadedLanguage['lang'] as $key => $value) {
                    $language['lang'][$key] = $value;
                }
            }
        }

        if (empty($language['lang'])) {
            return ['lang' => []];
        }

        return $language;
    }
}
