<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add Scheduler Configuration For Indexing
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['PITS\\Snowbabel\\Task\\Indexing'] = [
    'extension' => $_EXTKEY,
    'title' => 'Snowbabel - Indexing',
    'description' => 'Indexes all translation on current installation',
    'additionalFields' => '',
];

//commenting out temporarily as the exact behaviour of the wizard need to be analysed.
//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['InitOverrideLanguageKey'] = \PITS\Snowbabel\Updates\InitOverrideLanguageKey::class;

//hook for overriding localization.js,recordlist.js and including deepl.css
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['snowbabel'] = 'PITS\\Snowbabel\\Hook\\BackendHook->executePreRenderHook';
