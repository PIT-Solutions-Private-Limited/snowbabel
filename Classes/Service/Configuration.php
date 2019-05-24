<?php
namespace PITS\Snowbabel\Service;

/*
 *  Copyright notice
 *
 *  (c) 2011 Daniel Alder <info@snowflake.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Configuration.
 */
class Configuration
{
    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var
     * $configuration*/
    protected $configuration;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var string
     */
    protected $xmlPath = '';

    /**
     * @var mixed
     */
    protected $extjsParams;

    /**
     * $StandartValues.
     *
     * @var array
     */
    protected $StandartValues = [
        'LocalExtensionPath' => 'typo3conf/ext/',
        'SystemExtensionPath' => 'typo3/sysext/',
        'GlobalExtensionPath' => 'typo3/ext/',

        'ShowLocalExtensions' => 1,
        'ShowSystemExtensions' => 1,
        'ShowGlobalExtensions' => 1,

        'ShowOnlyLoadedExtensions' => 1,
        'ShowTranslatedLanguages' => 0,

        'ApprovedExtensions' => '',

        'XmlFilter' => 1,

        'AutoBackupEditing' => 1,
        'AutoBackupCronjob' => 0,

        'CopyDefaultLanguage' => 1,

        'AvailableLanguages' => '30',

        'SchedulerCheck' => 0,

        'ConfigurationChanged' => 0,
    ];

    /**
     * constructor.
     *
     * @param bool $extjsParams
     */
    public function __construct($extjsParams = false)
    {
        $this->loadConfiguration();
    }

    /**
     * @param  $value
     * @param  $name
     *
     * @return bool
     */
    public function setExtensionConfiguration($value, $name)
    {
        if (isset($value, $name)) {
            $this->configuration['Extension'][$name] = $value;

            return true;
        }

        return false;
    }

    /**
     * @param  $value
     *
     * @return bool
     */
    public function setExtensionConfigurationLoadedExtensions($value)
    {
        if (isset($value)) {
            $this->configuration['Extension']['LoadedExtensions'] = $value;

            return true;
        }

        return false;
    }

    /**
     * @param  $value
     * @param  $name
     *
     * @return bool
     */
    public function setApplicationConfiguration($value, $name)
    {
        if (isset($value, $name)) {
            $this->configuration['Application'][$name] = $value;

            return true;
        }

        return false;
    }

    /**
     * @param  $value
     * @param  $name
     *
     * @return bool
     */
    public function setUserConfiguration($value, $name)
    {
        if (isset($value, $name)) {
            $this->configuration['User'][$name] = $value;

            return true;
        }

        return false;
    }

    /**
     * @param  $value
     * @param  $name
     *
     * @return bool
     */
    public function setUserConfigurationColumn($value, $name)
    {
        if (isset($value, $name)) {
            // set 1 and 0 to true and false
            $value = $value ? true : false;
            $this->configuration['User']['Columns'][$name] = $value;

            return true;
        }

        return false;
    }

    /**
     * @param  $ExtensionList
     *
     * @return bool
     */
    public function setUserConfigurationExtensions($ExtensionList)
    {
        if (\is_array($ExtensionList)) {
            $this->configuration['User']['Extensions'] = $ExtensionList;

            return true;
        }

        return false;
    }

    /**
     * @param  $ExtjsParams
     *
     * @return bool
     */
    public function setExtjsConfiguration($ExtjsParams)
    {
        if (\is_array($ExtjsParams)) {
            $this->configuration['Extjs'] = $ExtjsParams;

            return true;
        }

        return false;
    }

    /**
     * @param array
     * @param mixed $submittedValues
     *
     * @return array
     */
    public function formatSubmitted($submittedValues)
    {
        if (\is_array($submittedValues)) {
            unset($submittedValues['action'], $submittedValues['controller']);
        } else {
            return false;
        }
        array_pop($submittedValues);
        foreach ($submittedValues as $key => $value) {
            if ('true' === $value) {
                $submittedValues[$key] = 1;
            } elseif ('false' === $value) {
                $submittedValues[$key] = 0;
            } elseif (\is_array($value)) {
                $submittedValues[$key] = implode(',', $value);
            } else {
                $submittedValues[$key] = $value;
            }
        }

        return $submittedValues;
    }

    /**
     * @param mixed $submittedValues
     *
     * @return void
     */
    public function saveFormSettings($submittedValues)
    {
        $formValues = $this->formatSubmitted($submittedValues);

        // Get Old Values
        $LocalconfValues = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['snowbabel'];
        $NewLocalconfValues = $LocalconfValues;
        if (2 === $submittedValues['displayoptions']) {
            $NewLocalconfValues['ShowSystemExtensions'] = 0;
            $NewLocalconfValues['ShowGlobalExtensions'] = 0;
            $NewLocalconfValues['ShowOnlyLoadedExtensions'] = 0;
            $NewLocalconfValues['ShowTranslatedLanguages'] = 0;
            $NewLocalconfValues['ShowLocalExtensions'] = 0;
        } elseif (4 === $submittedValues['editing']) {
            $NewLocalconfValues['CopyDefaultLanguage'] = 0;
        }

        // Write Defined Extjs Values To Database
        foreach ($this->StandartValues as $StandartKey => $StandartValue) {
            // New Value From Submit
            if (isset($formValues[$StandartKey])) {
                $Value = $formValues[$StandartKey];

                $NewLocalconfValues[$StandartKey] = $Value;
            } // Already Defined in Localconf
        }

        // Set Languages If Added
        $Languages = $formValues['selectedLanguages'];
        if ($Languages) {
            $NewLocalconfValues['AvailableLanguages'] = $Languages;
        }

        // Set Approved Extensions If Added
        $ApprovedExtensions = $formValues['selectedExtensions'];
        if ($ApprovedExtensions) {
            $NewLocalconfValues['ApprovedExtensions'] = $ApprovedExtensions;
        }

        // Mark Configuration Changes As 'CHANGED'
        $NewLocalconfValues['ConfigurationChanged'] = 1;

        // Write Localconf
        $this->writeLocalconfArray($NewLocalconfValues);
    }

    /**
     * setSchedulerCheckAndChangedConfiguration.
     *
     * @return void
     */
    public function setSchedulerCheckAndChangedConfiguration()
    {
        // Get Localconf Values
        $LocalconfValues = $this->loadApplicationConfiguration(false);
        if (1 !== $LocalconfValues['SchedulerCheck'] || 0 !== $LocalconfValues['ConfigurationChanged']) {
            // Set Scheduler Check
            $LocalconfValues['SchedulerCheck'] = 1;
            // Set Scheduler Check
            $LocalconfValues['ConfigurationChanged'] = 0;
            // Write To Localconf
            $this->writeLocalconfArray($LocalconfValues);
        }
    }

    /**
     * getDb.
     *
     * @return Database
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * getLL.
     *
     * @param $LabelName
     *
     * @return string
     */
    public function getLL($LabelName)
    {
        // use typo3 system function
        return $this->getLanguageService()->sL('LLL:EXT:'.$this->xmlPath.':'.$LabelName);
    }

    /**
     * getConfiguration.
     *
     * @return
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param  $name
     *
     * @return null
     */
    public function getApplicationConfiguration($name)
    {
        if (isset($name)) {
            return $this->configuration['Application'][$name];
        }

        return null;
    }

    /**
     * @param  $name
     *
     * @return null
     */
    public function getUserConfiguration($name)
    {
        if (isset($name)) {
            return $this->configuration['User'][$name];
        }

        return null;
    }

    /**
     * @return
     */
    public function getUserConfigurationColumns()
    {
        return $this->configuration['User']['Columns'];
    }

    /**
     * @param  $name
     *
     * @return null
     */
    public function getUserConfigurationColumn($name)
    {
        if (isset($name)) {
            return $this->configuration['User']['Columns'][$name];
        }

        return null;
    }

    /**
     * @return
     */
    public function getUserConfigurationId()
    {
        return $this->configuration['User']['Id'];
    }

    /**
     * @return
     */
    public function getUserConfigurationIsAdmin()
    {
        return $this->configuration['User']['IsAdmin'];
    }

    /**
     * @param  $name
     *
     * @return null
     */
    public function getExtensionConfiguration($name)
    {
        if (isset($name)) {
            return $this->configuration['Extension'][$name];
        }

        return null;
    }

    /**
     * @return
     */
    public function getExtensionConfigurationLoadedExtensions()
    {
        return $this->configuration['Extension']['LoadedExtensions'];
    }

    /**
     * @return null
     */
    public function getExtjsConfigurations()
    {
        if (\count($this->configuration['Extjs']) > 0) {
            return $this->configuration['Extjs'];
        }

        return null;
    }

    /**
     * @param  $name
     *
     * @return null
     */
    public function getExtjsConfiguration($name)
    {
        if (isset($name)) {
            return $this->configuration['Extjs'][$name];
        }

        return null;
    }

    /**
     * @return
     */
    public function getExtjsConfigurationListViewStart()
    {
        if ($this->configuration['Extjs']['start']) {
            return $this->configuration['Extjs']['start'];
        }

        return $this->configuration['Extjs']['ListViewStart'];
    }

    /**
     * @return array
     */
    public function getExtjsConfigurationFormSettings()
    {
        $ExtjsParams['LocalExtensionPath'] = $this->configuration['Extjs']['LocalExtensionPath'];
        $ExtjsParams['SystemExtensionPath'] = $this->configuration['Extjs']['SystemExtensionPath'];
        $ExtjsParams['GlobalExtensionPath'] = $this->configuration['Extjs']['GlobalExtensionPath'];

        $ExtjsParams['ShowLocalExtensions'] = $this->configuration['Extjs']['ShowLocalExtensions'];
        $ExtjsParams['ShowSystemExtensions'] = $this->configuration['Extjs']['ShowSystemExtensions'];
        $ExtjsParams['ShowGlobalExtensions'] = $this->configuration['Extjs']['ShowGlobalExtensions'];

        $ExtjsParams['ShowOnlyLoadedExtensions'] = $this->configuration['Extjs']['ShowOnlyLoadedExtensions'];
        $ExtjsParams['ShowTranslatedLanguages'] = $this->configuration['Extjs']['ShowTranslatedLanguages'];

        $ExtjsParams['XmlFilter'] = $this->configuration['Extjs']['XmlFilter'];

        $ExtjsParams['AutoBackupEditing'] = $this->configuration['Extjs']['AutoBackupEditing'];
        $ExtjsParams['AutoBackupCronjob'] = $this->configuration['Extjs']['AutoBackupCronjob'];

        $ExtjsParams['CopyDefaultLanguage'] = $this->configuration['Extjs']['CopyDefaultLanguage'];

        foreach ($ExtjsParams as $Key => $Param) {
            if (null === $Param) {
                $ExtjsParams[$Key] = 0;
            }
            if ('on' === $Param) {
                $ExtjsParams[$Key] = 1;
            }
        }

        return $ExtjsParams;
    }

    /**
     * @return
     */
    public function getExtjsConfigurationListViewLimit()
    {
        if ($this->configuration['Extjs']['limit']) {
            return $this->configuration['Extjs']['limit'];
        }

        return $this->configuration['Extjs']['ListViewLimit'];
    }

    /**
     * @param bool $AvailableLanguagesDiff
     *
     * @return array
     */
    public function getLanguages($AvailableLanguagesDiff = false)
    {
        $Languages = $this->db->getStaticLanguages();

        if ($AvailableLanguagesDiff) {
            $AvailableLanguages = $this->getApplicationConfiguration('AvailableLanguages');

            if (\is_array($Languages)) {
                $LanguagesDiff = [];

                foreach ($Languages as $Language) {
                    if (!\in_array($Language, $AvailableLanguages, true)) {
                        array_push($LanguagesDiff, $Language);
                    }
                }

                $Languages = $LanguagesDiff;
            }
        }

        return $Languages;
    }

    /**
     * @param  $LanguageId
     *
     * @return void
     */
    public function actionUserConfSelectedLanguages($LanguageId)
    {
        $SelectedLanguages = $this->getUserConfiguration('SelectedLanguages');

        // Add
        if (!GeneralUtility::inList($SelectedLanguages, $LanguageId)) {
            if (!$SelectedLanguages) {
                $SelectedLanguages = $LanguageId;
            } else {
                $SelectedLanguages .= ','.$LanguageId;
            }
        } // Remove
        else {
            $SelectedLanguages = GeneralUtility::rmFromList($LanguageId, $SelectedLanguages);
        }

        // Write Changes To Database
        $this->db->setUserConf('SelectedLanguages', $SelectedLanguages, $this->getUserConfigurationId());

        // Reset Configuration Array
        $this->setUserConfiguration($SelectedLanguages, 'SelectedLanguages');
    }

    /**
     * @param  $ColumnId
     *
     * @return void
     */
    public function actionUserConfigurationColumns($ColumnId)
    {
        $ColumnsConfiguration = $this->getUserConfigurationColumns();

        // Reverse Value
        $ColumnsConfiguration[$ColumnId] = !$ColumnsConfiguration[$ColumnId];

        // Write Changes To Database
        $this->db->setUserConf($ColumnId, $ColumnsConfiguration[$ColumnId], $this->getUserConfigurationId());

        // Reset Configuration Array
        $this->setUserConfiguration($ColumnsConfiguration, 'Columns');
    }

    /**
     * @param array $LocalconfValues
     *
     * @return void
     */
    private function writeLocalconfArray(array $LocalconfValues)
    {
        if (!$this->configurationManager instanceof ConfigurationManager) {
            $this->configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Configuration\\ConfigurationManager');
        }

        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        // $this->configurationManager->setLocalConfigurationValueByPath('EXT/extConf/snowbabel', serialize($LocalconfValues));
        $this->configurationManager->setLocalConfigurationValueByPath('EXTENSIONS/snowbabel', $LocalconfValues);

        $cacheManager->flushCaches();
    }

    /**
     * @return void
     */
    private function loadConfiguration()
    {
        // load db object
        $this->initDatabase();

        // load extension configuration
        $this->loadExtensionConfiguration();

        // load application configuration
        $this->loadApplicationConfiguration();

        // load user configuration
        $this->loadUserConfiguration();

        // load actions
        $this->loadActions();
    }

    /**
     * @return void
     */
    private function loadExtensionConfiguration()
    {
        $this->packageManager = GeneralUtility::makeInstance(PackageManager::class);

        $this->setExtensionConfiguration(ExtensionManagementUtility::extPath('snowbabel'), 'ExtPath');

        $this->setExtensionConfigurationLoadedExtensions($this->packageManager->getActivePackages());

        $this->setExtensionConfiguration(Environment::getPublicPath().'/', 'SitePath');

        $this->setExtensionConfiguration('typo3conf/l10n/', 'L10nPath');
    }

    /**
     * @param bool $SetConfiguration
     *
     * @return mixed
     */
    private function loadApplicationConfiguration($SetConfiguration = true)
    {
        $LocalconfValues = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['snowbabel'];

        // Check Configuration
        if (!\is_array($LocalconfValues) || \count($this->StandartValues) > \count($LocalconfValues)) {
            // Otherwise Set StandartValue
            foreach ($this->StandartValues as $StandartKey => $StandartValue) {
                if (!isset($LocalconfValues[$StandartKey])) {
                    $LocalconfValues[$StandartKey] = $StandartValue;
                }
            }

            // Write Configuration
            $this->writeLocalconfArray($LocalconfValues);
        }

        if ($SetConfiguration) {
            // local extension path
            $this->setApplicationConfiguration($LocalconfValues['LocalExtensionPath'], 'LocalExtensionPath');
            // system extension path
            $this->setApplicationConfiguration($LocalconfValues['SystemExtensionPath'], 'SystemExtensionPath');
            // global extension path
            $this->setApplicationConfiguration($LocalconfValues['GlobalExtensionPath'], 'GlobalExtensionPath');

            // show local extension
            $this->setApplicationConfiguration($LocalconfValues['ShowLocalExtensions'], 'ShowLocalExtensions');
            // show system extension
            $this->setApplicationConfiguration($LocalconfValues['ShowSystemExtensions'], 'ShowSystemExtensions');
            // show global extension
            $this->setApplicationConfiguration($LocalconfValues['ShowGlobalExtensions'], 'ShowGlobalExtensions');

            // show only loaded extension
            $this->setApplicationConfiguration($LocalconfValues['ShowOnlyLoadedExtensions'], 'ShowOnlyLoadedExtensions');
            // show translated languages
            $this->setApplicationConfiguration($LocalconfValues['ShowTranslatedLanguages'], 'ShowTranslatedLanguages');

            // approved extensions
            $this->setApplicationConfiguration(explode(',', $LocalconfValues['ApprovedExtensions']), 'ApprovedExtensions');

            // xml filter
            $this->setApplicationConfiguration($LocalconfValues['XmlFilter'], 'XmlFilter');

            // auto backup during editing
            $this->setApplicationConfiguration($LocalconfValues['AutoBackupEditing'], 'AutoBackupEditing');
            // auto backup during cronjob
            $this->setApplicationConfiguration($LocalconfValues['AutoBackupCronjob'], 'AutoBackupCronjob');

            // copy default language to english (en)
            $this->setApplicationConfiguration($LocalconfValues['CopyDefaultLanguage'], 'CopyDefaultLanguage');

            // load available languages
            $this->setApplicationConfiguration(
                $this->db->getAppConfAvailableLanguages(
                    $LocalconfValues['AvailableLanguages'],
                    $this->getApplicationConfiguration('ShowTranslatedLanguages')
                ),
                'AvailableLanguages'
            );

            // Scheduler Check
            $this->setApplicationConfiguration($LocalconfValues['SchedulerCheck'], 'SchedulerCheck');

            // Configuration Changed
            $this->setApplicationConfiguration($LocalconfValues['ConfigurationChanged'], 'ConfigurationChanged');
        }

        return $LocalconfValues;
    }

    /**
     * @return void
     */
    private function loadUserConfiguration()
    {
        // set admin mode
        $this->setUserConfiguration($GLOBALS['BE_USER']->user['admin'], 'IsAdmin');

        // set user id
        $this->setUserConfiguration($GLOBALS['BE_USER']->user['uid'], 'Id');

        // set user permitted extensions
        $this->setUserConfiguration($this->getPermittedExtensions($GLOBALS['BE_USER']->user['tx_snowbabel_extensions'], $GLOBALS['BE_USER']->userGroups), 'PermittedExtensions');

        // set user permitted languages
        $this->setUserConfiguration($this->getPermittedLanguages($GLOBALS['BE_USER']->user['tx_snowbabel_languages'], $GLOBALS['BE_USER']->userGroups), 'PermittedLanguages');

        // checks if database record already written
        $this->db->getUserConfCheck($this->getUserConfigurationId());

        // get selected languages
        $this->setUserConfiguration($this->db->getUserConfSelectedLanguages($this->getUserConfigurationId()), 'SelectedLanguages');

        // get "showColumn" values from database
        $this->setUserConfigurationColumn($this->db->getUserConfShowColumnLabel($this->getUserConfigurationId()), 'ShowColumnLabel');
        $this->setUserConfigurationColumn($this->db->getUserConfShowColumnDefault($this->getUserConfigurationId()), 'ShowColumnDefault');
    }

    /**
     * @return void
     */
    private function loadActions()
    {
        $ActionKey = $this->getExtjsConfiguration('ActionKey');
        $LanguageId = $this->getExtjsConfiguration('LanguageId');
        $ColumnId = $this->getExtjsConfiguration('ColumnId');

        if (!empty($LanguageId) && 'LanguageSelection' === $ActionKey) {
            $this->actionUserConfSelectedLanguages($LanguageId);
        }

        if (!empty($ColumnId) && 'ColumnSelection' === $ActionKey) {
            $this->actionUserConfigurationColumns($ColumnId);
        }
    }

    /**
     * @param  $PermittedExtensions
     * @param  $AllocatedGroups
     *
     * @return string
     */
    private function getPermittedExtensions($PermittedExtensions, $AllocatedGroups)
    {
        $AllowedExtensions1 = [];
        $AllowedExtensions2 = [];

        if ($PermittedExtensions) {
            $Values = explode(',', $PermittedExtensions);
            foreach ($Values as $Extension) {
                array_push($AllowedExtensions1, $Extension);
            }
        }

        // Get Allocated Groups -> Group/User Permissions
        if (\is_array($AllocatedGroups)) {
            foreach ($AllocatedGroups as $group) {
                if ($group['tx_snowbabel_extensions']) {
                    $Values = explode(',', $group['tx_snowbabel_extensions']);
                    foreach ($Values as $Extension) {
                        array_push($AllowedExtensions2, $Extension);
                    }
                }
            }
        }

        // Merge Both Together
        $AllowedExtensions = array_unique(array_merge($AllowedExtensions1, $AllowedExtensions2));

        return implode(',', $AllowedExtensions);
    }

    /**
     * @param  $PermittedLanguages
     * @param  $AllocatedGroups
     *
     * @return string
     */
    private function getPermittedLanguages($PermittedLanguages, $AllocatedGroups)
    {
        $AllowedLanguages1 = [];
        $AllowedLanguages2 = [];

        if ($PermittedLanguages) {
            $Values = explode(',', $PermittedLanguages);
            foreach ($Values as $Extension) {
                array_push($AllowedLanguages1, $Extension);
            }
        }

        // Get Allocated Groups -> Group/User Permissions
        if (\is_array($AllocatedGroups)) {
            foreach ($AllocatedGroups as $group) {
                if ($group['tx_snowbabel_languages']) {
                    $Values = explode(',', $group['tx_snowbabel_languages']);
                    foreach ($Values as $Extension) {
                        array_push($AllowedLanguages2, $Extension);
                    }
                }
            }
        }

        // Merge Both Together
        $AllowedLanguages = array_unique(array_merge($AllowedLanguages1, $AllowedLanguages2));

        return implode(',', $AllowedLanguages);
    }

    /**
     * @return void
     */
    private function initDatabase()
    {
        if (!\is_object($this->db) && !($this->db instanceof Database)) {
            $this->db = GeneralUtility::makeInstance('PITS\\Snowbabel\\Service\\Database');
        }
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    private static function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
