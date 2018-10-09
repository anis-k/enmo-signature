<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Service Model Abstract
 * @author dev@maarch.org
 */

namespace Group\models;

use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

abstract class ServiceModelAbstract
{
    public static function getServicesByXML()
    {
        $xmlfile = ServiceModel::getLoadedXml(['location' => 'apps']);
        $services = [];

        if ($xmlfile) {
            foreach ($xmlfile->SERVICE as $value) {
                if ((string) $value->enabled === 'true') {
                    $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                    $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                    $services['application'][] = [
                        'id'             => (string) $value->id,
                        'name'           => $name,
                        'comment'        => $comment,
                        'servicepage'    => (string) $value->servicepage,
                        'style'          => (string) $value->style,
                        'system_service' => (string) $value->system_service == 'true' ? true : false,
                        'servicetype'    => (string) $value->servicetype,
                    ];
                }
            }
        }

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/config.xml']);

        foreach ($loadedXml->MODULES as $mod) {
            $module = (string) $mod->moduleid;
            $xmlModuleFile = ServiceModel::getLoadedXml(['location' => $module]);

            if ($xmlModuleFile) {
                foreach ($xmlModuleFile->SERVICE as $value) {
                    if ((string) $value->enabled === 'true') {
                        $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                        $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                        $services[$module][] = [
                            'id'             => (string) $value->id,
                            'name'           => $name,
                            'comment'        => $comment,
                            'servicepage'    => (string) $value->servicepage,
                            'style'          => (string) $value->style,
                            'system_service' => (string) $value->system_service == 'true' ? true : false,
                            'servicetype'    => (string) $value->servicetype,
                        ];
                    }
                }
            }
        }

        return $services;
    }

    public static function getApplicationServicesByXML(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['type']);

        $xmlfile = ServiceModel::getLoadedXml(['location' => 'apps']);
        $applicationServices = [];

        if ($xmlfile) {
            foreach ($xmlfile->SERVICE as $value) {
                if ((string)$value->servicetype == $aArgs['type'] && (string)$value->enabled === 'true' && (string)$value->id != 'view_history_batch') {
                    $category = (string) $value->category;
                    $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                    $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                    if (empty($category)) {
                        $applicationServices[] = [
                            'id'            => (string)$value->id,
                            'name'          => $name,
                            'comment'       => $comment,
                            'servicepage'   => (string) $value->servicepage,
                            'shortcut'      => empty((string)$value->shortcut) ? 'false' : (string)$value->shortcut,
                            'style'         => (string) $value->style,
                            'angular'       => empty((string) $value->angular) ? 'false' : (string) $value->angular,
                        ];
                    } else {
                        $applicationServices[$category][] = [
                            'id'            => (string)$value->id,
                            'name'          => $name,
                            'comment'       => $comment,
                            'servicepage'   => (string) $value->servicepage,
                            'shortcut'      => empty((string)$value->shortcut) ? 'false' : (string)$value->shortcut,
                            'style'         => (string) $value->style,
                            'angular'       => empty((string) $value->angular) ? 'false' : (string) $value->angular,
                        ];
                    }
                }
            }
        }

        return $applicationServices;
    }

    public static function getApplicationServicesByUserServices(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userServices', 'type']);
        ValidatorModel::arrayType($aArgs, ['userServices']);
        ValidatorModel::stringType($aArgs, ['type']);

        $xmlfile = ServiceModel::getLoadedXml(['location' => 'apps']);
        $applicationServices = [];

        if ($xmlfile) {
            $hasHistory = false;
            foreach ($xmlfile->SERVICE as $value) {
                if ((string) $value->servicetype == $aArgs['type'] && (string) $value->enabled === 'true' && ((string) $value->system_service == 'true' || in_array((string) $value->id, $aArgs['userServices']))) {
                    if ((string)$value->id == 'view_history' || (string)$value->id == 'view_history_batch') {
                        $hasHistory = true;
                    }
                }
            }
            foreach ($xmlfile->SERVICE as $value) {
                $historyByPass = (string)$value->id == 'view_history' && $hasHistory;
                if ($historyByPass || ((string) $value->servicetype == $aArgs['type'] && (string) $value->enabled === 'true' && ((string) $value->system_service == 'true' || in_array((string) $value->id, $aArgs['userServices'])))) {
                    if ((string)$value->id != 'view_history_batch') {
                        $category = (string)$value->category;
                        $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                        $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                        if (empty($category)) {
                            $applicationServices[] = [
                                'id'            => (string)$value->id,
                                'name'          => $name,
                                'comment'       => $comment,
                                'servicepage'   => (string)$value->servicepage,
                                'shortcut'      => empty((string)$value->shortcut) ? 'false' : (string)$value->shortcut,
                                'style'         => (string)$value->style,
                                'angular'       => empty((string)$value->angular) ? 'false' : (string)$value->angular,
                            ];
                        } else {
                            $applicationServices[$category][] = [
                                'id'            => (string)$value->id,
                                'name'          => $name,
                                'comment'       => $comment,
                                'servicepage'   => (string)$value->servicepage,
                                'shortcut'      => empty((string)$value->shortcut) ? 'false' : (string)$value->shortcut,
                                'style'         => (string)$value->style,
                                'angular'       => empty((string)$value->angular) ? 'false' : (string)$value->angular,
                            ];
                        }
                    }
                }
            }
        }

        return $applicationServices;
    }

    public static function getModulesServicesByXML(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['type']);

        $modulesServices = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/config.xml']);
        foreach ($loadedXml->MODULES as $mod) {
            $module = (string) $mod->moduleid;
            $xmlModuleFile = ServiceModel::getLoadedXml(['location' => $module]);

            if ($xmlModuleFile) {
                foreach ($xmlModuleFile->SERVICE as $value) {
                    if ((string) $value->servicetype == $aArgs['type'] && (string) $value->enabled === 'true') {
                        $category = (string) $value->category;
                        $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                        $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                        if (empty($category)) {
                            $modulesServices[] = [
                                'id'            => (string)$value->id,
                                'name'          => $name,
                                'comment'       => $comment,
                                'servicepage'   => (string) $value->servicepage,
                                'shortcut'      => empty((string)$value->shortcut) ? 'false' : (string)$value->shortcut,
                                'style'         => (string) $value->style,
                                'angular'       => empty((string) $value->angular) ? 'false' : (string) $value->angular,
                            ];
                        } else {
                            $modulesServices[$category][] = [
                                'id'            => (string)$value->id,
                                'name'          => $name,
                                'comment'       => $comment,
                                'servicepage'   => (string) $value->servicepage,
                                'shortcut'      => empty((string)$value->shortcut) ? 'false' : (string)$value->shortcut,
                                'style'         => (string) $value->style,
                                'angular'       => empty((string) $value->angular) ? 'false' : (string) $value->angular,
                            ];
                        }
                    }
                }
            }
        }

        return $modulesServices;
    }

    public static function getModulesServicesByUserServices(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userServices', 'type']);
        ValidatorModel::arrayType($aArgs, ['userServices']);
        ValidatorModel::stringType($aArgs, ['type']);

        $modulesServices = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/config.xml']);
        foreach ($loadedXml->MODULES as $mod) {
            $module = (string) $mod->moduleid;
            $xmlModuleFile = ServiceModel::getLoadedXml(['location' => $module]);

            if ($xmlModuleFile) {
                foreach ($xmlModuleFile->SERVICE as $value) {
                    if ((string) $value->servicetype == $aArgs['type'] && (string) $value->enabled === 'true' && ((string) $value->system_service == 'true' || in_array((string) $value->id, $aArgs['userServices']))) {
                        $category = (string) $value->category;
                        $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                        $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                        if (empty($category)) {
                            $modulesServices[] = [
                                'id'            => (string)$value->id,
                                'name'          => $name,
                                'comment'       => $comment,
                                'servicepage'   => (string) $value->servicepage,
                                'shortcut'      => empty((string)$value->shortcut) ? 'false' : (string)$value->shortcut,
                                'style'         => (string) $value->style,
                                'angular'       => empty((string) $value->angular) ? 'false' : (string) $value->angular,
                            ];
                        } else {
                            $modulesServices[$category][] = [
                                'id'            => (string)$value->id,
                                'name'          => $name,
                                'comment'       => $comment,
                                'servicepage'   => (string) $value->servicepage,
                                'shortcut'      => empty((string)$value->shortcut) ? 'false' : (string)$value->shortcut,
                                'style'         => (string) $value->style,
                                'angular'       => empty((string) $value->angular) ? 'false' : (string) $value->angular,
                            ];
                        }
                    }
                }
            }
        }

        return $modulesServices;
    }

    public static function getAdministrationServicesByUserId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $rawServicesStoredInDB = ServiceModel::getByUserId(['userId' => $aArgs['userId']]);
        $servicesStoredInDB = [];
        foreach ($rawServicesStoredInDB as $value) {
            $servicesStoredInDB[] = $value['service_id'];
        }

        $administration = [];
        $administrationApplication = ServiceModel::getApplicationServicesByUserServices(['userServices' => $servicesStoredInDB, 'type' => 'admin']);
        $administrationModule = ServiceModel::getModulesServicesByUserServices(['userServices' => $servicesStoredInDB, 'type' => 'admin']);
        $administration['administrations'] = array_merge_recursive($administrationApplication, $administrationModule);

        return $administration;
    }

    public static function getByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aServices = DatabaseModel::select([
            'select'    => ['usergroups_services.service_id'],
            'table'     => ['usergroup_content, usergroups_services'],
            'where'     => ['usergroup_content.group_id = usergroups_services.group_id', 'usergroup_content.user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $aServices;
    }

    public static function hasService(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'userId', 'location', 'type']);
        ValidatorModel::stringType($aArgs, ['id', 'userId', 'location', 'type']);

        if ($aArgs['userId'] == 'superadmin') {
            return true;
        }
        $rawServicesStoredInDB = ServiceModel::getByUserId(['userId' => $aArgs['userId']]);
        $servicesStoredInDB = [];
        foreach ($rawServicesStoredInDB as $value) {
            $servicesStoredInDB[] = $value['service_id'];
        }

        $xmlfile = ServiceModel::getLoadedXml(['location' => $aArgs['location']]);

        if ($xmlfile) {
            foreach ($xmlfile->SERVICE as $value) {
                if ((string) $value->servicetype == $aArgs['type'] && (string) $value->id == $aArgs['id'] && (string) $value->enabled === 'true'
                    && ((string) $value->system_service == 'true' || in_array((string) $value->id, $servicesStoredInDB))) {
                    return true;
                }
            }
        }

        return false;
    }

    protected static function getLoadedXml(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['location']);
        ValidatorModel::stringType($aArgs, ['location']);

        $customId = CoreConfigModel::getCustomId();

        if ($aArgs['location'] == 'apps') {
            if (file_exists("custom/{$customId}/apps/maarch_entreprise/xml/services.xml")) {
                $path = "custom/{$customId}/apps/maarch_entreprise/xml/services.xml";
            } else {
                $path = 'apps/maarch_entreprise/xml/services.xml';
            }
        } else {
            if (file_exists("custom/{$customId}/modules/{$aArgs['location']}/xml/services.xml")) {
                $path = "custom/{$customId}/modules/{$aArgs['location']}/xml/services.xml";
            } else {
                $path = "modules/{$aArgs['location']}/xml/services.xml";
            }
        }

        if (!file_exists($path)) {
            return false;
        }

        $loadedXml = simplexml_load_file($path);

        return $loadedXml;
    }
}
