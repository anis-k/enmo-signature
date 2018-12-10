<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Core Config Model
* @author dev@maarch.org
* @ingroup core
*/

namespace SrcCore\models;

class CoreConfigModel
{
    public static function getConfig()
    {
        $path = empty($_SERVER['CONFIG_DIR']) ? $_SERVER['REDIRECT_CONFIG_DIR'] : $_SERVER['CONFIG_DIR'];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => $path . 'config.xml']);

        return $loadedXml;
    }

    public static function getLanguage()
    {
        $availableLanguages = ['en', 'fr'];

        $loadedXml = CoreConfigModel::getConfig();

        if ($loadedXml) {
            $lang = (string)$loadedXml->config->lang;
            if (in_array($lang, $availableLanguages)) {
                return $lang;
            }
        }

        return 'fr';
    }

    /**
     * Get the timezone
     *
     * @return string
     */
    public static function getTimezone()
    {
        $timezone = 'Europe/Paris';

        $loadedXml = CoreConfigModel::getConfig();

        if ($loadedXml) {
            if (!empty((string)$loadedXml->config->timezone)) {
                $timezone = (string)$loadedXml->config->timezone;
            }
        }

        return $timezone;
    }

    /**
     * Get the tmp dir
     *
     * @return string
     */
    public static function getTmpPath()
    {
        if (isset($_SERVER['MAARCH_TMP_DIR'])) {
            $tmpDir = $_SERVER['MAARCH_TMP_DIR'];
        } else {
            $tmpDir = sys_get_temp_dir();
        }

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755);
        }

        return $tmpDir . '/';
    }

    public static function getXmlLoaded(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['path']);
        ValidatorModel::stringType($aArgs, ['path']);

        $xmlfile = null;
        if (file_exists($aArgs['path'])) {
            $xmlfile = simplexml_load_file($aArgs['path']);
        }

        return $xmlfile;
    }
}
