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
    public static function getConfigPath()
    {
        $path = empty($_SERVER['CONFIG_DIR']) ? $_SERVER['REDIRECT_CONFIG_DIR'] : $_SERVER['CONFIG_DIR'];

        return $path;
    }

    public static function getConfig()
    {
        $path = CoreConfigModel::getConfigPath();

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => $path . '/config.xml']);

        return $loadedXml;
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

    public static function getEncryptKey()
    {
        if (!empty($_SERVER['MAARCH_ENCRYPT_KEY'])) {
            $encriptKey = $_SERVER['MAARCH_ENCRYPT_KEY'];
        } elseif (!empty($_SERVER['REDIRECT_MAARCH_ENCRYPT_KEY'])) {
            $encriptKey = $_SERVER['REDIRECT_MAARCH_ENCRYPT_KEY'];
        } else {
            $encriptKey = "Security Key Maarch Parapheur #2008";
        }

        return $encriptKey;
    }

    public static function getLibrariesDirectory()
    {
        if (isset($_SERVER['LIBRARIES_DIR'])) {
            $librariesDirectory = rtrim($_SERVER['LIBRARIES_DIR'], '/') . '/';
        } elseif (isset($_SERVER['REDIRECT_LIBRARIES_DIR'])) {
            $librariesDirectory = rtrim($_SERVER['REDIRECT_LIBRARIES_DIR'], '/') . '/';
        } else {
            $librariesDirectory = null;
        }

        return $librariesDirectory;
    }

    public static function getUniqueId()
    {
        $parts = explode('.', microtime(true));
        $sec = $parts[0];
        if (!isset($parts[1])) {
            $msec = 0;
        } else {
            $msec = $parts[1];
        }

        $uniqueId = str_pad(base_convert($sec, 10, 36), 6, '0', STR_PAD_LEFT);
        $uniqueId .= str_pad(base_convert($msec, 10, 16), 4, '0', STR_PAD_LEFT);
        $uniqueId .= str_pad(base_convert(mt_rand(), 10, 36), 6, '0', STR_PAD_LEFT);

        return $uniqueId;
    }
}
