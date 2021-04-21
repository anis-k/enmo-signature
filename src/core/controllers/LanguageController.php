<?php

/**
 * Copyright Maarch since 2008 under license.
 * See LICENSE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Language Controller
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use Respect\Validation\Validator;
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

    public function generateLang(Request $request, Response $response)
    {
        if (!is_file('dist/main.js')) {
            return $response->withStatus(403)->withJson(['errors' => 'Route forbidden']);
        }
        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['langId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body langId is empty or not a string']);
        }

        $path = "lang/{$body['langId']}.json";

        if (!is_file($path) || !is_writable($path)) {
            return $response->withStatus(400)->withJson(['errors' => "lang/{$body['langId']}.json is not a file or is not writable"]);
        }

        $file = @fopen($path, 'w');
        if ($file === false) {
            return $response->withStatus(400)->withJson(['errors' => "Cannot open file : lang/{$body['langId']}.json"]);
        }

        $content = json_encode($body['jsonContent'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        fwrite($file, $content);
        fclose($file);

        return $response->withStatus(204);
    }

    public static function getAvailableCoreLanguages(Request $request, Response $response)
    {
        $files = array_diff(scandir('lang'), ['..', '.']);
        $languages = [];
        foreach ($files as $value) {
            $path = 'lang/' . $value;
            $langName = str_replace('.json', '', $value) ;
            $fileContent = file_get_contents($path);
            $fileContent = json_decode($fileContent, true);
            $languages[$langName] = $fileContent;
        }
        return $response->withJson(['languages' => $languages]);
    }
}
