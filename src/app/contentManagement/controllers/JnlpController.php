<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Jnlp Controller
 *
 * @author dev@maarch.org
 */

namespace ContentManagement\controllers;

use Docserver\models\DocserverModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use Template\models\TemplateModel;

require_once 'core/class/Url.php';

class JnlpController
{
    public function generateJnlp(Request $request, Response $response)
    {
        $data = $request->getParams();

        $coreUrl = str_replace('rest/', '', \Url::coreurl());
        $tmpPath = CoreConfigModel::getTmpPath();
        $jnlpUniqueId = DatabaseModel::uniqueId();
        $jnlpFileName = $GLOBALS['userId'] . '_maarchCM_' . $jnlpUniqueId;
        $jnlpFileNameExt = $jnlpFileName . '.jnlp';

        $allCookies = '';
        foreach($_COOKIE as $key => $value) {
            if (!empty($allCookies)) {
                $allCookies .= '; ';
            }
            $allCookies .= $key . '=' . str_replace(' ', '+', $value);
        }
        if (!empty($data['cookies'])) {
            if (!empty($allCookies)) {
                $allCookies .= '; ';
            }
            $allCookies .= $data['cookies'];
        }

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/content_management/xml/config.xml']);
        $jarPath = $coreUrl;
        if ($loadedXml && !empty((string)$loadedXml->CONFIG[0]->jar_path)) {
            $jarPath = (string)$loadedXml->CONFIG[0]->jar_path;
        }

        $jnlpDocument = new \DomDocument('1.0', 'UTF-8');

        $tagJnlp = $jnlpDocument->createElement('jnlp');

        $newAttribute = $jnlpDocument->createAttribute('spec');
        $newAttribute->value = '6.0+';
        $tagJnlp->appendChild($newAttribute);

        $newAttribute = $jnlpDocument->createAttribute('codebase');
        $newAttribute->value = $tmpPath;
        $tagJnlp->appendChild($newAttribute);

        $tagInformation = $jnlpDocument->createElement('information');
        $tagTitle       = $jnlpDocument->createElement('title', 'Editeur de modèle de document');
        $tagVendor      = $jnlpDocument->createElement('vendor', 'MAARCH');
        $tagOffline     = $jnlpDocument->createElement('offline-allowed');
        $tagSecurity    = $jnlpDocument->createElement('security');
        $tagPermissions = $jnlpDocument->createElement('all-permissions');
        $tagResources   = $jnlpDocument->createElement('resources');
        $tagJ2se        = $jnlpDocument->createElement('j2se');

        $newAttribute = $jnlpDocument->createAttribute('version');
        $newAttribute->value = '1.6+';
        $tagJ2se->appendChild($newAttribute);

        $tagJar1 = $jnlpDocument->createElement('jar');
        $newAttribute = $jnlpDocument->createAttribute('href');
        $newAttribute->value = $coreUrl . '/modules/content_management/dist/maarchCM.jar';
        $tagJar1->appendChild($newAttribute);
        $newAttribute = $jnlpDocument->createAttribute('main');
        $newAttribute->value = 'true';
        $tagJar1->appendChild($newAttribute);

        $tagJar2 = $jnlpDocument->createElement('jar');
        $newAttribute = $jnlpDocument->createAttribute('href');
        $newAttribute->value = $jarPath . '/modules/content_management/dist/lib/httpclient-4.5.2.jar';
        $tagJar2->appendChild($newAttribute);

        $tagJar3 = $jnlpDocument->createElement('jar');
        $newAttribute = $jnlpDocument->createAttribute('href');
        $newAttribute->value = $jarPath . '/modules/content_management/dist/lib/httpclient-cache-4.5.2.jar';
        $tagJar3->appendChild($newAttribute);

        $tagJar4 = $jnlpDocument->createElement('jar');
        $newAttribute = $jnlpDocument->createAttribute('href');
        $newAttribute->value = $jarPath . '/modules/content_management/dist/lib/httpclient-win-4.5.2.jar';
        $tagJar4->appendChild($newAttribute);

        $tagJar5 = $jnlpDocument->createElement('jar');
        $newAttribute = $jnlpDocument->createAttribute('href');
        $newAttribute->value = $jarPath . '/modules/content_management/dist/lib/httpcore-4.4.4.jar';
        $tagJar5->appendChild($newAttribute);

        $tagJar6 = $jnlpDocument->createElement('jar');
        $newAttribute = $jnlpDocument->createAttribute('href');
        $newAttribute->value = $jarPath . '/modules/content_management/dist/lib/plugin.jar';
        $tagJar6->appendChild($newAttribute);

        $tagJar7 = $jnlpDocument->createElement('jar');
        $newAttribute = $jnlpDocument->createAttribute('href');
        $newAttribute->value = $jarPath . '/modules/content_management/dist/lib/commons-logging-1.2.jar';
        $tagJar7->appendChild($newAttribute);


        $tagApplication = $jnlpDocument->createElement('application-desc');
        $newAttribute = $jnlpDocument->createAttribute('main-class');
        $newAttribute->value = 'com.maarch.MaarchCM';
        $tagApplication->appendChild($newAttribute);

        $tagArg1 = $jnlpDocument->createElement('argument', $coreUrl . 'rest/jnlp/' . $jnlpUniqueId); //ProcessJnlp
        $tagArg2 = $jnlpDocument->createElement('argument', $data['objectType']); //Type
        $tagArg3 = $jnlpDocument->createElement('argument', $data['table']); //Table
        $tagArg4 = $jnlpDocument->createElement('argument', $data['objectId']); //ObjectId
        $tagArg5 = $jnlpDocument->createElement('argument', $data['uniqueId']);
        $tagArg6 = $jnlpDocument->createElement('argument', "maarchCourrierAuth={$_COOKIE['maarchCourrierAuth']}"); //MaarchCookie
        $tagArg7 = $jnlpDocument->createElement('argument', htmlentities($allCookies)); //AllCookies
        $tagArg8 = $jnlpDocument->createElement('argument', $jnlpFileName); //JnlpFileName
        $tagArg9 = $jnlpDocument->createElement('argument', $GLOBALS['userId']); //CurrentUser
        $tagArg10 = $jnlpDocument->createElement('argument', 'false'); //ConvertPdf
        $tagArg11 = $jnlpDocument->createElement('argument', 'false'); //OnlyConvert
        $tagArg12 = $jnlpDocument->createElement('argument', 0); //HashFile


        $tagJnlp->appendChild($tagInformation);
        $tagInformation->appendChild($tagTitle);
        $tagInformation->appendChild($tagVendor);
        $tagInformation->appendChild($tagOffline);

        $tagJnlp->appendChild($tagSecurity);
        $tagSecurity->appendChild($tagPermissions);

        $tagJnlp->appendChild($tagResources);
        $tagResources->appendChild($tagJ2se);
        $tagResources->appendChild($tagJar1);
        $tagResources->appendChild($tagJar2);
        $tagResources->appendChild($tagJar3);
        $tagResources->appendChild($tagJar4);
        $tagResources->appendChild($tagJar5);
        $tagResources->appendChild($tagJar6);
        $tagResources->appendChild($tagJar7);

        $tagJnlp->appendChild($tagApplication);
        $tagApplication->appendChild($tagArg1);
        $tagApplication->appendChild($tagArg2);
        $tagApplication->appendChild($tagArg3);
        $tagApplication->appendChild($tagArg4);
        $tagApplication->appendChild($tagArg5);
        $tagApplication->appendChild($tagArg6);
        $tagApplication->appendChild($tagArg7);
        $tagApplication->appendChild($tagArg8);
        $tagApplication->appendChild($tagArg9);
        $tagApplication->appendChild($tagArg10);
        $tagApplication->appendChild($tagArg11);
        $tagApplication->appendChild($tagArg12);

        $jnlpDocument->appendChild($tagJnlp);

        $jnlpDocument->save($tmpPath . $jnlpFileNameExt);

        fopen($tmpPath . $jnlpFileName . '.lck', 'w+');

        return $response->withJson(['generatedJnlp' => $jnlpFileNameExt, 'jnlpUniqueId' => $jnlpUniqueId]);
    }

    public function renderJnlp(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        if (explode('.', $data['fileName'])[1] != 'jnlp') {
            return $response->withStatus(403)->withJson(['errors' => 'File extension forbidden']);
        } elseif (strpos($data['fileName'], "{$GLOBALS['userId']}_maarchCM_") === false) {
            return $response->withStatus(403)->withJson(['errors' => 'File name forbidden']);
        }

        $tmpPath = CoreConfigModel::getTmpPath();
        $jnlp = file_get_contents($tmpPath . $data['fileName']);
        if ($jnlp === false) {
            return $response->withStatus(404)->withJson(['errors' => 'Jnlp file not found on ' . $tmpPath]);
        }

        $response->write($jnlp);

        return $response->withHeader('Content-Type', 'application/x-java-jnlp-file');
    }

    public function processJnlp(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getParams();

        $tmpPath = CoreConfigModel::getTmpPath();

        if ($data['action'] == 'editObject') {
            if ($data['objectType'] == 'templateCreation') {
                $explodeFile = explode('.', $data['objectId']);
                $ext = $explodeFile[count($explodeFile) - 1];
                $newFileOnTmp = "tmp_file_{$GLOBALS['userId']}_{$aArgs['jnlpUniqueId']}.{$ext}";

                $pathToCopy = $data['objectId'];
            } elseif ($data['objectType'] == 'templateModification') {
                $docserver = DocserverModel::getCurrentDocserver(['typeId' => 'TEMPLATES', 'collId' => 'templates', 'select' => ['path_template']]);
                $template = TemplateModel::getById(['id' => $data['objectId'], 'select' => ['template_path', 'template_file_name']]);
                if (empty($template)) {
                    $xmlResponse = JnlpController::generateResponse(['type' => 'ERROR', 'data' => ['ERROR' => "Template does not exist"]]);
                    $response->write($xmlResponse);
                    return $response->withHeader('Content-Type', 'application/xml');
                }

                $explodeFile = explode('.', $template['template_file_name']);
                $ext = $explodeFile[count($explodeFile) - 1];
                $newFileOnTmp = "tmp_file_{$GLOBALS['userId']}_{$aArgs['jnlpUniqueId']}.{$ext}";

                $pathToCopy = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $template['template_path']) . $template['template_file_name'];
            } else {
                $xmlResponse = JnlpController::generateResponse(['type' => 'ERROR', 'data' => ['ERROR' => 'Wrong objectType']]);
                $response->write($xmlResponse);
                return $response->withHeader('Content-Type', 'application/xml');
            }

            if (!file_exists($pathToCopy) || !copy($pathToCopy, $tmpPath . $newFileOnTmp)) {
                $xmlResponse = JnlpController::generateResponse(['type' => 'ERROR', 'data' => ['ERROR' => "Failed to copy on {$tmpPath} : {$pathToCopy}"]]);
                $response->write($xmlResponse);
                return $response->withHeader('Content-Type', 'application/xml');
            }

            $fileContent = file_get_contents($tmpPath . $newFileOnTmp, FILE_BINARY);

            $result = [
                'STATUS'            => 'ok',
                'OBJECT_TYPE'       => $data['objectType'],
                'OBJECT_TABLE'      => $data['objectTable'],
                'OBJECT_ID'         => $data['objectId'],
                'UNIQUE_ID'         => $data['uniqueId'],
                'APP_PATH'          => 'start',
                'FILE_CONTENT'      => base64_encode($fileContent),
                'FILE_EXTENSION'    => $ext,
                'ERROR'             => '',
                'END_MESSAGE'       => ''
            ];
            $xmlResponse = JnlpController::generateResponse(['type' => 'SUCCESS', 'data' => $result]);

        } elseif ($data['action'] == 'saveObject') {
            if (empty($data['fileContent']) || empty($data['fileExtension'])) {
                $xmlResponse = JnlpController::generateResponse(['type' => 'ERROR', 'data' => ['ERROR' => 'File content or file extension empty']]);
                $response->write($xmlResponse);
                return $response->withHeader('Content-Type', 'application/xml');
            }

            $encodedFileContent = str_replace(' ', '+', $data['fileContent']);
            $ext = str_replace(["\\", "/", '..'], '', $data['fileExtension']);
            $fileContent = base64_decode($encodedFileContent);
            $fileOnTmp = "tmp_file_{$GLOBALS['userId']}_{$aArgs['jnlpUniqueId']}.{$ext}";

            $file = fopen($tmpPath . $fileOnTmp, 'w');
            fwrite($file, $fileContent);
            fclose($file);

            if (!empty($data['step']) && $data['step'] == 'end') {
                if (file_exists("{$tmpPath}{$GLOBALS['userId']}_maarchCM_{$aArgs['jnlpUniqueId']}.lck")) {
                    unlink("{$tmpPath}{$GLOBALS['userId']}_maarchCM_{$aArgs['jnlpUniqueId']}.lck");
                }
            }

            $xmlResponse = JnlpController::generateResponse(['type' => 'SUCCESS', 'data' => ['END_MESSAGE' => 'Update ok']]);
        } elseif ($data['action'] == 'terminate') {
            if (file_exists("{$tmpPath}{$GLOBALS['userId']}_maarchCM_{$aArgs['jnlpUniqueId']}.lck")) {
                unlink("{$tmpPath}{$GLOBALS['userId']}_maarchCM_{$aArgs['jnlpUniqueId']}.lck");
            }

            $xmlResponse = JnlpController::generateResponse(['type' => 'SUCCESS', 'data' => ['END_MESSAGE' => 'Terminate ok']]);
        } else {
            $result = [
                'STATUS' => 'ko',
                'OBJECT_TYPE'       => $data['objectType'],
                'OBJECT_TABLE'      => $data['objectTable'],
                'OBJECT_ID'         => $data['objectId'],
                'UNIQUE_ID'         => $data['uniqueId'],
                'APP_PATH'          => 'start',
                'FILE_CONTENT'      => '',
                'FILE_EXTENSION'    => '',
                'ERROR'             => 'Missing parameters',
                'END_MESSAGE'       => ''
            ];
            $xmlResponse = JnlpController::generateResponse(['type' => 'ERROR', 'data' => $result]);
        }

        $response->write($xmlResponse);

        return $response->withHeader('Content-Type', 'application/xml');
    }

    public function isLockFileExisting(Request $request, Response $response, array $aArgs)
    {
        $tmpPath = CoreConfigModel::getTmpPath();
        $lockFileName = "{$GLOBALS['userId']}_maarchCM_{$aArgs['jnlpUniqueId']}.lck";

        $fileFound = false;
        if (file_exists($tmpPath . $lockFileName)) {
            $fileFound = true;
        }

        return $response->withJson(['lockFileFound' => $fileFound]);
    }

    private static function generateResponse(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['type', 'data']);
        ValidatorModel::stringType($aArgs, ['type']);
        ValidatorModel::arrayType($aArgs, ['data']);

        $response = new \DomDocument('1.0', 'UTF-8');

        $tagRoot = $response->createElement($aArgs['type']);
        $response->appendChild($tagRoot);

        foreach ($aArgs['data'] as $key => $value) {
            $tag = $response->createElement($key, $value);
            $tagRoot->appendChild($tag);
        }

        return $response->saveXML();
    }
}
