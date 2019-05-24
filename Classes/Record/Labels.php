<?php
namespace PITS\Snowbabel\Record;

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
use PITS\Snowbabel\Service\Configuration;
use PITS\Snowbabel\Service\Database;
use PITS\Snowbabel\Service\Translations;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Labels.
 */
class Labels
{
    /**
     * @var Configuration
     */
    protected $confObj;

    /**
     * @var Languages
     */
    protected $langObj;

    /**
     * @var Database
     */
    protected $Db;

    /**
     * @var Translations
     */
    protected $SystemTranslation;

    /**
     * @var
     */
    protected $CurrentTableId;

    /**
     * @var array
     */
    protected $Languages;

    protected $ColumnsConfiguration;

    protected $ShowColumnLabel;

    protected $ShowColumnDefault;

    protected $IsAdmin;

    protected $PermittedExtensions;

    protected $Labels;

    protected $SearchMode;

    protected $SearchString;

    protected $ExtensionId;

    protected $ListViewStart;

    protected $ListViewLimit;

    /**
     * @param Configuration $confObj
     */
    public function __construct($confObj)
    {
        $this->confObj = $confObj;
        $this->Db = $this->confObj->getDb();

        // Get Current TableId
        $this->CurrentTableId = $this->Db->getCurrentTableId();

        // get User params
        $this->ColumnsConfiguration = $this->confObj->getUserConfigurationColumns();

        $this->ShowColumnLabel = $this->ColumnsConfiguration['ShowColumnLabel'];
        $this->ShowColumnDefault = $this->ColumnsConfiguration['ShowColumnDefault'];

        $this->IsAdmin = $this->confObj->getUserConfigurationIsAdmin();
        $this->PermittedExtensions = $this->confObj->getUserConfiguration('PermittedExtensions');

        // extjs params
        $this->SearchString = $this->confObj->getExtjsConfiguration('SearchString');
        $this->ExtensionId = $this->confObj->getExtjsConfiguration('ExtensionId');

        $this->Dir = $this->confObj->getExtjsConfiguration('dir');
        $this->Sort = $this->confObj->getExtjsConfiguration('sort');

        $this->ListViewStart = $this->confObj->getExtjsConfigurationListViewStart();
        $this->ListViewLimit = $this->confObj->getExtjsConfigurationListViewLimit();

        $this->TranslationId = $this->confObj->getExtjsConfiguration('TranslationId');
        $this->TranslationValue = $this->confObj->getExtjsConfiguration('TranslationValue');

        // get language object
        $this->getLanguageObject();

        // get available languages
        $this->Languages = $this->langObj->getLanguages();
    }

    /**
     * @return void
     */
    public function setMetaData()
    {
        // Set metadata to configure grid properties
        $MetaData['metaData']['idProperty'] = 'RecordId';
        $MetaData['metaData']['root'] = 'LabelRows';

        // Set field for totalcounts -> paging
        $MetaData['metaData']['totalProperty'] = 'ResultCount';

        // Set standard sorting
        $MetaData['metaData']['sortInfo']['field'] = $this->Sort ? $this->Sort : 'LabelName';
        $MetaData['metaData']['sortInfo']['direction'] = $this->Dir ? $this->Dir : 'ASC';

        // Set fields
        $MetaData['metaData']['fields'] = [];
        array_push($MetaData['metaData']['fields'], 'LabelId');
        array_push($MetaData['metaData']['fields'], 'LabelName');
        array_push($MetaData['metaData']['fields'], 'LabelDefault');

        // Add fields for selected languages
        if (\is_array($this->Languages)) {
            foreach ($this->Languages as $Language) {
                if ($Language['LanguageSelected']) {
                    array_push($MetaData['metaData']['fields'], 'TranslationId_'.$Language['LanguageKey']);
                    array_push($MetaData['metaData']['fields'], 'TranslationValue_'.$Language['LanguageKey']);
                }
            }
        }

        // Set columns
        $MetaData['columns'] = [
            [
                'header' => 'LabelId',
                'dataIndex' => 'LabelId',
                'hidden' => true,
            ],

            [
                'header' => 'Label',
                'dataIndex' => 'LabelName',
                'sortable' => true,
                'hidden' => !$this->ShowColumnLabel,
            ],

            [
                'header' => 'Default',
                'dataIndex' => 'LabelDefault',
                'sortable' => true,
                'hidden' => !$this->ShowColumnDefault,
            ],
        ];

        // Add Columns For Selected Languages
        if (\is_array($this->Languages)) {
            foreach ($this->Languages as $Language) {
                if ($Language['LanguageSelected']) {
                    // Translation Id
                    $addColumn = [
                        'header' => 'TranslationId_'.$Language['LanguageKey'],
                        'dataIndex' => 'TranslationId_'.$Language['LanguageKey'],
                        'hidden' => true,
                    ];

                    array_push($MetaData['columns'], $addColumn);

                    // Translation Value
                    $addColumn = [
                        'header' => $Language['LanguageName'],
                        'dataIndex' => 'TranslationValue_'.$Language['LanguageKey'],
                        'sortable' => true,
                        'editor' => [
                            'xtype' => 'textarea',
                            'multiline' => true,
                            'grow' => true,
                            'growMin' => 30,
                            'growMax' => 200,
                        ],
                        'renderer' => 'CellPreRenderer',
                    ];

                    array_push($MetaData['columns'], $addColumn);
                }
            }
        }

        // Add MetaData
        $this->Labels = $MetaData;

        // Add Data Array
        $this->Labels['LabelRows'] = [];
    }

    /**
     * @return
     */
    public function getSearchGlobal()
    {
        $this->SearchMode = 'global';

        return $this->getLabels();
    }

    /**
     * getSearchExtension.
     *
     * @return
     */
    public function getSearchExtension()
    {
        $this->SearchMode = 'extension';

        return $this->getLabels();
    }

    /**
     * getLabels.
     *
     * @param mixed $data
     *
     * @return
     */
    public function getLabels($data)
    {
        if (!$this->IsAdmin && '' === $this->PermittedExtensions) {
            $this->Labels['LabelRows'] = null;
        } else {
            $Languages = [];

            if (\is_array($this->Languages)) {
                foreach ($this->Languages as $Language) {
                    if ($Language['LanguageSelected']) {
                        array_push($Languages, $Language['LanguageKey']);
                    }
                }
            }

            $Conf = [
                'ExtensionId' => 'global' === $this->SearchMode ? '' : $data['ExtensionId'],
                'Sort' => $this->Sort ? $this->Sort : 'LabelName',
                'Dir' => $this->Dir ? $this->Dir : 'ASC',
                'Limit' => $data['ListViewLimit'].','.$data['ListViewLimit'],
                'Search' => !$data['SearchString'] ? '' : $data['SearchString'],
                'Languages' => $Languages,
                'Debug' => '0',
            ];

            $Translations = $this->Db->getTranslations($this->CurrentTableId, $Conf, $Languages);

            // Add Result To Array
            $this->Labels['LabelRows'] = $Translations;
        }

        return $this->Labels;
    }

    /**
     * updateTranslation.
     *
     * @param $translationId
     * @param $translationValue
     *
     * @return void
     */
    public function updateTranslation($translationId, $translationValue)
    {
        if ($translationId) {
            // DATABASE

            $this->Db->setTranslation($translationId, $translationValue, $this->CurrentTableId);

            // SYSTEM

            $Conf = [
                'TranslationId' => $translationId,
            ];

            // Get Full Translation Data From DB
            $Translation = $this->Db->getTranslation($this->CurrentTableId, $Conf);

            // Init SystemTranslations
            $this->initSystemTranslations();

            // Update SystemTranslations With DB Values
            $this->SystemTranslation->updateTranslation($Translation[0]);
        }
    }

    /**
     * getLanguageObject.
     *
     * @return void
     */
    private function getLanguageObject()
    {
        if (!\is_object($this->langObj) && !($this->langObj instanceof Languages)) {
            $this->langObj = GeneralUtility::makeInstance('PITS\\Snowbabel\\Record\\Languages', $this->confObj);
        }
    }

    /**
     * initSystemTranslations.
     *
     * @return void
     */
    private function initSystemTranslations()
    {
        if (!\is_object($this->SystemTranslation) && !($this->SystemTranslation instanceof Translations)) {
            $this->SystemTranslation = GeneralUtility::makeInstance('PITS\\Snowbabel\\Service\\Translations');
            $this->SystemTranslation->init($this->confObj);
        }
    }
}
