<?php
namespace PITS\Snowbabel\Service;

/***************************************************************
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
 ***************************************************************/

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Database
 *
 * @package PITS\Snowbabel\Service
 */
class Database
{


    /**
     * @var string
     */
    public $queryBuilder = null;


    /**
     */
    public function __construct() {
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class);
    }


    /**
     * @param      $LocalconfValue
     * @param bool $ShowTranslatedLanguages
     * @return array
     */
    public function getAppConfAvailableLanguages($LocalconfValue, $ShowTranslatedLanguages = false)
    {

        $Languages = array();
        $TempLanguages = explode(",", $LocalconfValue);

        if (is_array($TempLanguages)) {
            foreach ($TempLanguages as $TempLanguageId) {

                $Language = $this->getStaticLanguages($TempLanguageId, $ShowTranslatedLanguages);

                if (!empty($Language)) {
                    array_push($Languages, $Language);
                }

            }
        }

        return $Languages;
    }


    /**
     * @param  $BeUserId
     * @return null
     */
    public function getUserConfSelectedLanguages($BeUserId)
    {

        // set configuration
        $name = 'SelectedLanguages';

        // get value
        return $this->getUserConf($name, $BeUserId);

    }


    /**
     * @param  $BeUserId
     * @return null
     */
    public function getUserConfShowColumnLabel($BeUserId)
    {

        // set configuration
        $name = 'ShowColumnLabel';

        // get value
        return $this->getUserConf($name, $BeUserId);

    }


    /**
     * @param  $BeUserId
     * @return null
     */
    public function getUserConfShowColumnDefault($BeUserId)
    {

        // set configuration
        $name = 'ShowColumnDefault';

        // get value
        return $this->getUserConf($name, $BeUserId);

    }


    /**
     * @param  $name
     * @param  $BeUserId
     * @return null
     */
    public function getUserConf($name, $BeUserId)
    {
        if(isset($name, $BeUserId)) {

            $select = $this->queryBuilder->getQueryBuilderForTable('tx_snowbabel_users')
                ->select($name)
                ->from('tx_snowbabel_users')
                ->where('deleted=0 AND be_users_uid=' . $BeUserId)
                ->setMaxResults(1)
                ->execute()->fetchAll();

            // return value
            return $select[0][$name];

        } else {
            return null;
        }
    }


    /**
     * @param  $BeUserId
     * @return void
     */
    public function getUserConfCheck($BeUserId)
    {

        if($BeUserId > 0) {
            $select = $this->queryBuilder->getQueryBuilderForTable('tx_snowbabel_users')
                ->select('uid')
                ->from('tx_snowbabel_users')
                ->where('deleted=0 AND be_users_uid=' . $BeUserId)
                ->setMaxResults(1)
                ->execute()->fetchAll();

            if(!$select) {

                // insert database row
                $this->insertUserConfCheck($BeUserId);
            }
        }

    }


    /**
     * @param bool $LanguageId
     * @param bool $ShowTranslatedLanguages
     * @return array|null
     */
    public function getStaticLanguages($LanguageId = false, $ShowTranslatedLanguages = false)
    {

        $WhereClause = '';

        // search single language
        if (is_numeric($LanguageId)) {
            $WhereClause = 'uid=' . $LanguageId;
        }

        // sort by english or local
        if (!$ShowTranslatedLanguages) {
            $OrderBy = 'lg_name_en';
        } else {
            $OrderBy = 'lg_name_local';
        }

        $Select = $this->queryBuilder->getQueryBuilderForTable('static_languages')
                ->select('*')
                ->from('static_languages')
                ->where($WhereClause)
                ->orderBy($OrderBy)
                ->execute()->fetchAll();

        if (!count($Select)) {

            return null;

        } else {

            if (is_array($Select)) {

                $Languages = array();

                foreach ($Select as $Key => $Language) {

                    $Languages[$Key]['LanguageId'] = $Language['uid'];

                    // check if languages should be displayed in english or local
                    if (!$ShowTranslatedLanguages) {
                        $Languages[$Key]['LanguageName'] = $Language['lg_name_en'];
                    } else {
                        $Languages[$Key]['LanguageName'] = $Language['lg_name_local'];
                    }

                    $Languages[$Key]['LanguageNameEn'] = $Language['lg_name_en'];
                    $Languages[$Key]['LanguageNameLocal'] = $Language['lg_name_local'];

                    $Languages[$Key]['LanguageKey'] = $Language['tx_snowbabel_override_language_key'] ?: strtolower($Language['lg_iso_2']);

                    if ($LanguageId) {
                        return $Languages[$Key];
                    }
                }

                return $Languages;
            } else {
                return null;
            }

        }

    }


    /**
     * @param  $Name
     * @param  $Value
     * @param  $BeUserId
     * @return void
     */
    public function setUserConf($Name, $Value, $BeUserId)
    {
        $this->queryBuilder->getQueryBuilderForTable('tx_snowbabel_users')
        ->update('tx_snowbabel_users')
        ->where('deleted=0 AND be_users_uid=' . $BeUserId)
        ->set('tstamp', time())
        ->set($Name, $Value)
        ->execute();
    }


    /**
     * @param  $BeUserId
     * @return void
     */
    public function insertUserConfCheck($BeUserId)
    {

        $insert = $this->queryBuilder->getQueryBuilderForTable('tx_snowbabel_users')
                   ->insert('tx_snowbabel_users')
                   ->values([
                        'tstamp' => time(),
                        'crdate' => time(),
                        'be_users_uid' => $BeUserId,
                   ])
                   ->execute();

    }

    /*********/
    /** API **/
    /*********/

    /**
     * @return int
     */
    public function getCurrentTableId()
    {

        $Select = $this->queryBuilder->getQueryBuilderForTable('tx_snowbabel_temp')
                ->select('TableId')
                ->from('tx_snowbabel_temp')
                ->execute()->fetchAll();

        if(count($Select) > 0) {
            return $Select[0]['TableId'];
        }

        $insert = $this->queryBuilder->getQueryBuilderForTable('tx_snowbabel_users')
                   ->insert('tx_snowbabel_temp')
                   ->values([
                        'TableId' => 0,
                   ])
                   ->execute();
        return 0;

    }


    /**
     * @param  $TableId
     * @return void
     */
    public function setCurrentTableId($TableId)
    {

        // Update Field
        $this->queryBuilder->getQueryBuilderForTable('tx_snowbabel_temp')
        ->update('tx_snowbabel_temp')
        ->set('TableId', $TableId)
        ->execute();
    }


    /**
     * @param       $CurrentTableId
     * @param array $Conf
     * @return null
     */
    public function getExtensions($CurrentTableId, $Conf = array())
    {

        $Table = 'tx_snowbabel_indexing_extensions_' . $CurrentTableId;
        $Fields = 'uid AS ExtensionId,pid,tstamp,crdate,ExtensionKey,ExtensionTitle,ExtensionDescription,ExtensionCategory,ExtensionIcon,ExtensionLocation,ExtensionPath,ExtensionLoaded';
        $Where = '';
        $OrderBy = '';
        $GroupBy = '';
        $Limit = '';

        if (is_array($Conf) && count($Conf) > 0) {

            // FIELDS
            if ($Conf['Fields']) {
                $Fields = $Conf['Fields'];
            }

            // WHERE
            $Where = array(
                'OR' => array(),
                'AND' => array(),
            );

            if ($Conf['Local']) {
                array_push($Where['OR'], 'ExtensionLocation=\'Local\'');
            }
            if ($Conf['System']) {
                array_push($Where['OR'], 'ExtensionLocation=\'System\'');
            }
            if ($Conf['Global']) {
                array_push($Where['OR'], 'ExtensionLocation=\'Global\'');
            }

            if ($Conf['OnlyLoaded']) {
                array_push($Where['AND'], 'ExtensionLoaded=1');
            }

            if (!empty($Conf['ApprovedExtensions'])) {

                $ApprovedExtensions = $this->prepareCommaSeparatedString($Conf['ApprovedExtensions'], $Table);
                if (!empty($ApprovedExtensions)) {
                    array_push($Where['AND'], 'ExtensionKey IN (' . $ApprovedExtensions . ')');
                }

            }

            if (!empty($Conf['PermittedExtensions'])) {

                $PermittedExtensions = $this->prepareCommaSeparatedString($Conf['PermittedExtensions'], $Table);
                if (!empty($PermittedExtensions)) {
                    array_push($Where['AND'], 'ExtensionKey IN (' . $PermittedExtensions . ')');
                }

            }

            $Where = $this->where($Where);

            // GROUP BY
            if ($Conf['GroupBy']) {
                $GroupBy = $Conf['GroupBy'];
            }

            // ORDER BY
            if ($Conf['OrderBy']) {
                $OrderBy = $Conf['OrderBy'];
            }

            // LIMIT
            if ($Conf['Limit']) {
                $OrderBy = $Conf['Limit'];
            }
        }

        $select_fields = str_replace(",","','",$Fields);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($Table);
        if($Where!=''){
            $sqlStatement = "SELECT ".$Fields." FROM ".$Table." WHERE ".$Where;
        }else{
            $sqlStatement = "SELECT ".$Fields." FROM ".$Table;
        }

        $statement = $connection->executeQuery($sqlStatement);
        $statement->execute();
        $Select = $statement->fetchAll();

        return $Select;

    }


    /**
     * @param  $Extensions
     * @param  $CurrentTableId
     * @return void
     */
    public function setExtensions($Extensions, $CurrentTableId)
    {

        // Define Table
        $Table = 'tx_snowbabel_indexing_extensions_' . $CurrentTableId;

        // Empty Table
        $this->truncate($Table);

        // Add Records To Table
        $this->insert($Table, $Extensions);

    }


    /**
     * @param      $CurrentTableId
     * @param bool $Conf
     * @return null
     */
    public function getFiles($CurrentTableId, $Conf = false)
    {

        $Table1 = 'tx_snowbabel_indexing_extensions_' . $CurrentTableId;
        $Table2 = 'tx_snowbabel_indexing_files_' . $CurrentTableId;
        $Table = $Table1 . ',' . $Table2;
        $Fields = $Table1 . '.ExtensionKey,' . $Table1 . '.ExtensionTitle,' . $Table1 . '.ExtensionDescription,' . $Table1 . '.ExtensionCategory,'
            . $Table1 . '.ExtensionIcon,' . $Table1 . '.ExtensionLocation,' . $Table1 . '.ExtensionPath,' . $Table1 . '.ExtensionLoaded,'
            . $Table2 . '.uid AS FileId,' . $Table2 . '.ExtensionId,' . $Table2 . '.FileKey';
        $Where = array(
            'OR' => array(),
            'AND' => array(),
        );
        $OrderBy = '';
        $GroupBy = '';
        $Limit = '';

        array_push($Where['AND'], $Table1 . '.uid=' . $Table2 . '.ExtensionId');

        if (is_array($Conf)) {

            // FIELDS
            if ($Conf['Fields']) {
                $Fields = $Conf['Fields'];
            }

            // WHERE
            if ($Conf['ExtensionId']) {
                array_push($Where['AND'], $Table1 . '.uid=' . intval($Conf['ExtensionId']));
            }

            // GROUP BY
            if ($Conf['GroupBy']) {
                $GroupBy = $Conf['GroupBy'];
            }

            // ORDER BY
            if ($Conf['OrderBy']) {
                $OrderBy = $Conf['OrderBy'];
            }

            // LIMIT
            if ($Conf['Limit']) {
                $Limit = $Conf['Limit'];
            }
        }

        // WHERE
        $Where = $this->where($Where);

        $sql = $this->queryBuilder->getQueryBuilderForTable($Table1)
           ->select($Table1 . '.ExtensionKey' , $Table1 . '.ExtensionTitle' , $Table1 . '.ExtensionDescription' , $Table1 . '.ExtensionCategory'
            , $Table1 . '.ExtensionIcon' , $Table1 . '.ExtensionLocation' , $Table1 . '.ExtensionPath' , $Table1 . '.ExtensionLoaded'
            , $Table2 . '.uid AS FileId' , $Table2 . '.ExtensionId' , $Table2 . '.FileKey')
           ->from($Table1)
           ->leftJoin(
              $Table1,
              $Table2,
              $Table2,
              $Where
           )
           ->execute()->fetchAll();

        return $sql;

    }


    /**
     * @param  $FileArray
     * @param  $CurrentTableId
     * @return void
     */
    public function setFiles($FileArray, $CurrentTableId)
    {

        // Define Table
        $Table = 'tx_snowbabel_indexing_files_' . $CurrentTableId;
        $InsertFiles = array();

        // Empty Table
        $this->truncate($Table);

        if (count($FileArray)) {

            foreach ($FileArray as $Files) {

                if (count($Files)) {

                    foreach ($Files as $File) {
                        array_push($InsertFiles, $File);
                    }

                }

            }

            // Add Records To Table
            $this->insert($Table, $InsertFiles);
        }

    }


    /**
     * @param       $CurrentTableId
     * @param array $Conf
     * @param bool $Count
     * @return null
     */
    public function getLabels($CurrentTableId, $Conf = array(), $Count = false)
    {

        $Table1 = 'tx_snowbabel_indexing_extensions_' . $CurrentTableId;
        $Table2 = 'tx_snowbabel_indexing_files_' . $CurrentTableId;
        $Table3 = 'tx_snowbabel_indexing_labels_' . $CurrentTableId;
        $Table = $Table1 . ',' . $Table2 . ',' . $Table3;
        $Fields = $Table1 . '.ExtensionKey,' . $Table1 . '.ExtensionTitle,' . $Table1 . '.ExtensionDescription,' . $Table1 . '.ExtensionCategory,'
            . $Table1 . '.ExtensionIcon,' . $Table1 . '.ExtensionLocation,' . $Table1 . '.ExtensionPath,' . $Table1 . '.ExtensionLoaded,'
            . $Table2 . '.uid AS FileId,' . $Table2 . '.ExtensionId,' . $Table2 . '.FileKey,'
            . $Table3 . '.uid AS LabelId,' . $Table3 . '.LabelName,' . $Table3 . '.LabelDefault';
        $Where = array(
            'OR' => array(),
            'AND' => array(),
            'SEARCH_AND' => array(),
            'SEARCH_OR' => array(),
        );
        $OrderBy = '';
        $GroupBy = '';
        $Limit = '';

        array_push($Where['AND'], $Table1 . '.uid=' . $Table2 . '.ExtensionId');
        array_push($Where['AND'], $Table2 . '.uid=' . $Table3 . '.FileId');

        if (is_array($Conf) && count($Conf) > 0) {

            // FIELDS
            if ($Conf['Fields']) {
                $Fields = $Conf['Fields'];
            }

            // WHERE
            if ($Conf['ExtensionId']) {
                array_push($Where['AND'], $Table1 . '.uid=' . intval($Conf['ExtensionId']));
            }

            // GROUP BY
            if ($Conf['GroupBy']) {
                $GroupBy = $Conf['GroupBy'];
            }

            // ORDER BY
            if ($Conf['OrderBy']) {
                $OrderBy = $Conf['OrderBy'];
            }

            // LIMIT
            if ($Conf['Limit'] && !$Count) {
                $Limit = $Conf['Limit'];
            }

            // SEARCH
            if ($Conf['Search']) {
                array_push($Where['SEARCH_OR'], $Table3 . '.LabelName LIKE \'%' . $Conf['Search'] . '%\'');
                array_push($Where['SEARCH_OR'], $Table3 . '.LabelDefault LIKE \'%' . $Conf['Search'] . '%\'');
            }
        }

        // WHERE
        $Where = $this->where($Where);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($Table2);
        $sqlStatement = "SELECT ".$Fields." FROM ".$Table." WHERE ".$Where;

        $statement = $connection->executeQuery($sqlStatement);
        $statement->execute();
        $Select = $statement->fetchAll();

        return $Select;

    }


    /**
     * @param  $LabelArray
     * @param  $CurrentTableId
     * @return void
     */
    public function setLabels($LabelArray, $CurrentTableId)
    {

        // Define Table
        $Table = 'tx_snowbabel_indexing_labels_' . $CurrentTableId;
        $InsertLabels = array();

        // Empty Table
        $this->truncate($Table);

        if (count($LabelArray)) {

            foreach ($LabelArray as $Labels) {

                if (count($Labels)) {

                    foreach ($Labels as $LabelRow) {
                        array_push($InsertLabels, $LabelRow);
                    }

                }

            }

            // Add Records To Table
            $this->insert($Table, $InsertLabels);
        }

    }


    /**
     * @param       $CurrentTableId
     * @param array $Conf
     * @param array $Languages
     * @param bool $Count
     * @return array|int|null|string
     */
    public function getTranslations($CurrentTableId, $Conf = array(), $Languages = array(), $Count = false)
    {

        $Table1 = 'tx_snowbabel_indexing_extensions_' . $CurrentTableId;
        $Table2 = 'tx_snowbabel_indexing_files_' . $CurrentTableId;
        $Table3 = 'tx_snowbabel_indexing_labels_' . $CurrentTableId;
        $Table3_Alias = 'Labels';
        $Table4 = 'tx_snowbabel_indexing_translations_' . $CurrentTableId;
        $Table = $Table1 . ',' . $Table2 . ',' . $Table3 . ' AS ' . $Table3_Alias; // Needed For Subqueries!!!
        $Fields = $Table3_Alias . '.LabelName,' . $Table3_Alias . '.LabelDefault';

        $Where = array(
            'OR' => array(),
            'AND' => array(),
            'SEARCH_AND' => array(),
            'SEARCH_OR' => array(),
        );
        $OrderBy = '';
        $GroupBy = '';
        $Limit = '';

        array_push($Where['AND'], $Table1 . '.uid=' . $Table2 . '.ExtensionId');
        array_push($Where['AND'], $Table2 . '.uid=' . $Table3_Alias . '.FileId');

        if (is_array($Conf) && count($Conf) > 0) {

            // FIELDS
            if ($Conf['Fields']) {
                $Fields = $Conf['Fields'];
            }

            // WHERE
            if ($Conf['ExtensionId']) {
                array_push($Where['AND'], $Table1 . '.uid=' . intval($Conf['ExtensionId']));
            }

            // GROUP BY
            if ($Conf['GroupBy']) {
                $GroupBy = $Conf['GroupBy'];
            }

            // ORDER BY
            if ($Conf['OrderBy']) {
                $OrderBy = $Conf['OrderBy'];
            }

            if ($Conf['Sort']) {

                // Translations
                if (strpos($Conf['Sort'], 'TranslationValue_') !== false) {
                    $OrderBy = $Conf['Sort'] . ' ' . $Conf['Dir'];
                } // Label-Table
                else {
                    $OrderBy = $Table3_Alias . '.' . $Conf['Sort'] . ' ' . $Conf['Dir'];
                }

            }

            // LIMIT
            if ($Conf['Limit'] && !$Count) {
                $Limit = $Conf['Limit'];
            }

        }

        // LANGUAGES
        if (count($Languages)) {
            foreach ($Languages as $Language) {

                // FORM
                $Table .= ',' . $Table4 . ' `trans_' . $Language . '`';

                // SELECT
                $Fields .= ',`trans_' . $Language . '`.uid as `TranslationId_' . $Language . '`';
                $Fields .= ',`trans_' . $Language . '`.TranslationValue as `TranslationValue_' . $Language . '`';

                // WHERE
                array_push($Where['AND'], 'Labels.uid = `trans_' . $Language . '`.LabelId');
                array_push($Where['AND'], '`trans_' . $Language . '`.TranslationLanguage = \'' . $Language . '\'');

                // SEARCH
                // if ($Conf['Search']) {
                //     array_push($Where['SEARCH_OR'], '`trans_' . $Language . '`.TranslationValue LIKE \'%' . $Conf['Search'] . '%\'');
                // }

            }
        }

        // WHERE
        $Where = $this->where($Where);
        // $WhereClause = explode(' AND',$Where);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($Table2);
        $sqlStatement = "SELECT ".$Fields." FROM ".$Table." WHERE ".$Where;

        $statement = $connection->executeQuery($sqlStatement);
        $statement->execute();
        $Select = $statement->fetchAll();

        $sortedArray = []; 
        $i = 0; 
        $key_array = []; 
        
        foreach($Select as $val) { 
            if (!in_array($val['LabelName'], $key_array)) { 
                $key_array[$i] = $val['LabelName']; 
                $sortedArray[$i] = $val; 
            } 
            $i++; 
        } 

        return array_values($sortedArray);

    }


    /**
     * @param       $CurrentTableId
     * @param array $Conf
     * @return array|int|null|string
     */
    public function getTranslation($CurrentTableId, $Conf = array())
    {
        $Table1 = 'tx_snowbabel_indexing_extensions_' . $CurrentTableId;
        $Table2 = 'tx_snowbabel_indexing_files_' . $CurrentTableId;
        $Table3 = 'tx_snowbabel_indexing_labels_' . $CurrentTableId;
        $Table4 = 'tx_snowbabel_indexing_translations_' . $CurrentTableId;
        $Table = $Table1 . ',' . $Table2 . ',' . $Table3 . ',' . $Table4;
        $Fields = $Table1 . '.ExtensionKey,' . $Table1 . '.ExtensionTitle,' . $Table1 . '.ExtensionDescription,' . $Table1 . '.ExtensionCategory,'
            . $Table1 . '.ExtensionIcon,' . $Table1 . '.ExtensionLocation,' . $Table1 . '.ExtensionPath,' . $Table1 . '.ExtensionLoaded,'
            . $Table2 . '.uid AS FileId,' . $Table2 . '.ExtensionId,' . $Table2 . '.FileKey,'
            . $Table3 . '.uid AS LabelId,' . $Table3 . '.LabelName,' . $Table3 . '.LabelDefault,'
            . $Table4 . '.uid AS TranslationId,' . $Table4 . '.TranslationValue,' . $Table4 . '.TranslationLanguage,' . $Table4 . '.TranslationEmpty';

        $Where = array(
            'OR' => array(),
            'AND' => array(),
            'SEARCH_AND' => array(),
            'SEARCH_OR' => array(),
        );
        $OrderBy = '';
        $GroupBy = '';
        $Limit = '';

        array_push($Where['AND'], $Table1 . '.uid=' . $Table2 . '.ExtensionId');
        array_push($Where['AND'], $Table2 . '.uid=' . $Table3 . '.FileId');
        array_push($Where['AND'], $Table3 . '.uid=' . $Table4 . '.LabelId');

        if (is_array($Conf) && count($Conf) > 0) {

            // FIELDS
            if ($Conf['Fields']) {
                $Fields = $Conf['Fields'];
            }

            // WHERE
            if ($Conf['TranslationId']) {
                array_push($Where['AND'], $Table4 . '.uid=' . intval($Conf['TranslationId']));
            }

            // GROUP BY
            if ($Conf['GroupBy']) {
                $GroupBy = $Conf['GroupBy'];
            }

            // ORDER BY
            if ($Conf['OrderBy']) {
                $OrderBy = $Conf['OrderBy'];
            }

        }

        // WHERE
        $Where = $this->where($Where);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($Table2);
        $sqlStatement = "SELECT ".$Fields." FROM ".$Table." WHERE ".$Where;

        $statement = $connection->executeQuery($sqlStatement);
        $statement->execute();
        $Select = $statement->fetchAll();

        return $Select;

    }


    /**
     * @param  $TranslationArray
     * @param  $CurrentTableId
     * @return void
     */
    public function setTranslations($TranslationArray, $CurrentTableId)
    {

        // Define Table
        $Table = 'tx_snowbabel_indexing_translations_' . $CurrentTableId;
        $InsertTranslations = array();

        // Empty Table
        $this->truncate($Table);

        if (count($TranslationArray)) {

            foreach ($TranslationArray as $Translations) {

                if (count($Translations)) {

                    foreach ($Translations as $Translation) {
                        array_push($InsertTranslations, $Translation);
                    }

                }

            }

            // Add Records To Table
            $this->insert($Table, $InsertTranslations);
        }

    }


    /**
     * @param  $TranslationId
     * @param  $TranslationValue
     * @param  $CurrentTableId
     * @return void
     */
    public function setTranslation($TranslationId, $TranslationValue, $CurrentTableId) {

        $this->queryBuilder->getQueryBuilderForTable('tx_snowbabel_indexing_translations_' . $CurrentTableId)
            ->update('tx_snowbabel_indexing_translations_' . $CurrentTableId)
            ->where('uid=' . intval($TranslationId))
            ->set('tstamp', time())
            ->set('TranslationValue', $TranslationValue)
            ->execute();
    }


    /**
     * @param        $Fields
     * @param        $Table
     * @param string $Where
     * @param string $GroupBy
     * @param string $OrderBy
     * @param string $Limit
     * @param bool $Debug
     * @param bool $Count
     * @return array|int|null|string
     */
    private function select($Fields, $Table, $Where = '', $GroupBy = '', $OrderBy = '', $Limit = '', $Debug = false, $Count = false) {

        $result = $this->queryBuilder->getQueryBuilderForTable($Table)
                ->select($Fields)
                ->from($Table);
                    if($Where != ''){
                        $result->where($Where);
                    }
                    if(is_integer($Limit)){
                        $result->setMaxResults($Limit);
                    }
                    if($OrderBy != ''){
                        $result->orderBy($OrderBy);
                    }
                    if($GroupBy != ''){
                        $result->groupBy($GroupBy);
                    }
        $Select = $result->execute()->fetchAll();

        if(empty($Select)) {

            if($Count) {
                return 0;
            }

            return null;

        } else {

            if(is_array($Select)) {

                if($Count) {
                    return count($Select);
                }

                return $Select;

            } else {
                if($Count) {
                    return 0;
                }

                return null;
            }
        }

    }


    /**
     * @param  $Table
     * @param  $DataArray
     * @return bool|null
     *
     * Simply add table name and data array. The function will take care of inserting data
     * depending on typo3 version (single insert / all at once)
     *
     * data array format:
     * $DataArray[0]['Field1'] = Value1 // First row
     * $DataArray[0]['Field2'] = Value2
     * $DataArray[1]['Field1'] = Value3 // Second row
     * $DataArray[1]['Field2'] = Value4
     *
     */
    private function insert($Table, $DataArray)
    {

        if (is_array($DataArray) && count($DataArray) > 0) {

            $FieldNames = array();
            $FieldValues = array();

            foreach ($DataArray as $Row) {

                $Row['tstamp'] = strval(time());
                $Row['crdate'] = strval(time());

                // TODO: exec_INSERTmultipleRows -> something's wrong in big inserts
                if (1 < 0) {
                    //if(version_compare(TYPO3_version, '4.3.99', '>')) {

                    // Reset Records-Array
                    $Records = array();

                    // Create Field Names On First Run
                    if (count($FieldNames) == 0) {
                        $FieldNames = array_keys($Row);
                    }

                    // Prepare Data
                    foreach ($FieldNames as $FieldName) {
                        $Records[] = $Row[$FieldName];
                    }

                    // Add Data
                    array_push($FieldValues, $Records);

                } else {

                    $this->queryBuilder->getQueryBuilderForTable($Table)
                       ->insert($Table)
                       ->values($Row)
                       ->execute();

                }

            }

            // Success
            return true;

        } else {
            return null;
        }

    }


    /**
     * @param  $Table
     * @return void
     */
    private function truncate($Table)
    {
        $this->queryBuilder->getConnectionForTable($Table)
                           ->truncate($Table);
    }


    /**
     * @param  $Where
     * @return string
     */
    private function where($Where)
    {

        if (is_array($Where['OR']) && count($Where['OR'])) {
            $Where['OR'] = '(' . implode($Where['OR'], ' OR ') . ')';
        } else {
            unset($Where['OR']);
        }

        if (is_array($Where['AND']) && count($Where['AND'])) {
            $Where['AND'] = '(' . implode($Where['AND'], ' AND ') . ')';
        } else {
            unset($Where['AND']);
        }

        if (is_array($Where['SEARCH_OR']) && count($Where['SEARCH_OR'])) {
            $Where['SEARCH_OR'] = '(' . implode($Where['SEARCH_OR'], ' OR ') . ')';
        } else {
            unset($Where['SEARCH_OR']);
        }

        if (is_array($Where['SEARCH_AND']) && count($Where['SEARCH_AND'])) {
            $Where['SEARCH_AND'] = '(' . implode($Where['SEARCH_AND'], ' AND ') . ')';
        } else {
            unset($Where['SEARCH_AND']);
        }

        $Where = implode($Where, ' AND ');

        return $Where;
    }


    /**
     * @param  $CommaSeparatedString
     * @param  $Table
     * @return string
     */
    private function prepareCommaSeparatedString($CommaSeparatedString, $Table)
    {
        if(is_string($CommaSeparatedString) && strpos($CommaSeparatedString, ',') !== false) {
            $CommaSeparatedString = explode(',', $CommaSeparatedString);
        }

        return "'" . implode ( "', '", $CommaSeparatedString ) . "'";
    }

}
