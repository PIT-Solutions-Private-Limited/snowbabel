<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        if (TYPO3_MODE === 'BE') {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'PITS.Snowbabel',
                'snowbabel',
                '',
                '',
                [],
                [
                    'access' => 'user,group',
                    'labels' => 'LLL:EXT:snowbabel/Resources/Private/Language/locallang_module_snowbabel.xlf',
                ]
            );

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'PITS.Snowbabel',
                'snowbabel', // Make module a submodule of 'user'
                'translation', // Submodule key
                '', // Position
                [
                    'Translation' => 'index',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:snowbabel/ext_icon.svg',
                    'labels' => 'LLL:EXT:snowbabel/Resources/Private/Language/locallang_module_translation.xlf',
                ]
            );

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'PITS.Snowbabel',
                'snowbabel', // Make module a submodule of 'user'
                'settings', // Submodule key
                '', // Position
                [
                    'Settings' => 'index,submit',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:snowbabel/ext_icon.svg',
                    'labels' => 'LLL:EXT:snowbabel/Resources/Private/Language/locallang_module_settings.xlf',
                ]
            );
        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('snowbabel', 'Configuration/TypoScript', 'Snowbabel');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_snowbabel_domain_model_translation', 'EXT:snowbabel/Resources/Private/Language/locallang_csh_tx_snowbabel_domain_model_translation.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_snowbabel_domain_model_translation');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_snowbabel_domain_model_settings', 'EXT:snowbabel/Resources/Private/Language/locallang_csh_tx_snowbabel_domain_model_settings.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_snowbabel_domain_model_settings');

        // Todo: use autoinclude tca
        // Extend Beusers For Translation Access Control
        $tempColumns = [
            'tx_snowbabel_extensions' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:snowbabel/Resources/Private/Language/locallang_settings.xlf:settings_extended_extensions',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'itemsProcFunc' => 'PITS\Snowbabel\Hook\Tca->getExtensions',
                    'size' => 10,
                    'maxitems' => 9999,
                    'default' => '',
                ],
            ],
            'tx_snowbabel_languages' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:snowbabel/Resources/Private/Language/locallang_settings.xlf:settings_extended_languages',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'itemsProcFunc' => 'PITS\Snowbabel\Hook\Tca->getLanguages',
                    'size' => 10,
                    'maxitems' => 9999,
                    'default' => '',
                ],
            ],
        ];

        // Add be_groups fields
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
            'be_groups',
            $tempColumns,
            1
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'be_groups',
            'tx_snowbabel_extensions;;;;1-1-1'
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'be_groups',
            'tx_snowbabel_languages;;;;1-1-1'
        );

        // Add be_users fields
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
            'be_users',
            $tempColumns,
            1
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'be_users',
            'tx_snowbabel_extensions;;;;1-1-1'
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'be_users',
            'tx_snowbabel_languages;;;;1-1-1'
        );

        unset($tempColumns);
    }
);
