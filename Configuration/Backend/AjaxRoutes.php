<?php
return [
    // Retrieve general settings
    'fetch_general_settings' => [
        'path' => '/general/settings/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getGeneralSettings',
    ],

    // Retrieve extensions list
    'fetch_ext_list' => [
        'path' => '/extensions/list/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getExtensionMenu',
    ],

    // Retrieve selected extensions list
    'fetch_trans_ext_list' => [
        'path' => '/translations/extensions/list/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getExtensionsList',
    ],

    // Retrieve selected languages list
    'fetch_trans_lang_list' => [
        'path' => '/translations/languages/list/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getLanguageSelection',
    ],

    // Retrieve selected column list
    'fetch_trans_col_list' => [
        'path' => '/translations/colums/list/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getColumnSelection',
    ],

    // Retrieve added extensions list
    'fetch_added_ext_list' => [
        'path' => '/extensions/addedlist/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getApprovedExtensionsAdded',
    ],

    // Retrieve languages list
    'fetch_lang_list' => [
        'path' => '/languages/list/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getGeneralSettingsLanguages',
    ],

    // Retrieve added languages list
    'fetch_added_lang_list' => [
        'path' => '/languages/addedlist/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getGeneralSettingsLanguagesAdded',
    ],

    // Retrieve label list
    'fetch_label_listing' => [
        'path' => '/label/listing/',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::getListView',
    ],

    // Retrieve label list from language selection
    'fetch_label_listing_language' => [
        'path' => '/label/listing/languages',
        'target' => PITS\Snowbabel\Utility\ModuleUtility::class.'::ActionController',
    ],
];
