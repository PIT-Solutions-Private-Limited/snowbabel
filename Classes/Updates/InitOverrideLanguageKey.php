<?php
namespace PITS\Snowbabel\Updates;

/*
 *  Copyright notice
 *
 *  (c) 2019 Guillaume Germain <guillaume@germain.bzh>
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

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\AbstractUpdate;

/**
 * Class InitOverrideLanguageKey.
 */
class InitOverrideLanguageKey extends AbstractUpdate
{
    /**
     * @var string
     */
    protected $title = 'Snowbabel - Initializing the field to override the language key';

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get description.
     *
     * @return string Longer description of this updater
     */
    public function getDescription(): string
    {
        return 'Snowbabel must initialize a field in database to avoid conflicts between some '.
            'languages (de, de_CH and de_AT for example).';
    }

    /**
     * @return string Unique identifier of this updater
     */
    public function getIdentifier(): string
    {
        return 'InitOverrideLanguageKey';
    }

    /**
     * Checks if an update is needed.
     *
     * @param string &$description The description for the update
     *
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $description = 'Snowbabel must initialize a field in database to avoid conflicts between some '.
            'languages (de, de_CH and de_AT for example).';

        return \count($this->listLanguagesToUpgrade());
    }

    /**
     * Performs the accordant updates.
     *
     * @param array  &$dbQueries     Queries done in this update
     * @param string &$customMessage Custom message
     *
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessage)
    {
        $languages = $this->listLanguagesToUpgrade();
        $multipleLanguages = [];
        foreach ($languages as $lang) {
            $multipleLanguages[] = $lang['lg_iso_2'];
        }

        try {
            // update all multiple languages
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('static_languages');
            $queryBuilder
                ->update('static_languages')
                ->where(
                    $queryBuilder->expr()->in('lg_iso_2', $queryBuilder->createNamedParameter($multipleLanguages, Connection::PARAM_STR_ARRAY)),
                    $queryBuilder->expr()->neq('lg_country_iso_2', $queryBuilder->createNamedParameter('', Connection::PARAM_STR))
                )
                ->set('tx_snowbabel_override_language_key', 'CONCAT(LOWER(lg_iso_2), "_", UPPER(lg_country_iso_2))', false)
                ->execute()
            ;

            $dbQueries[] = $queryBuilder->getSQL();

            return true;
        } catch (DBALException $e) {
            $customMessage = 'SQL-ERROR: '.htmlspecialchars($e->getPrevious()->getMessage());

            return false;
        }
    }

    protected function listLanguagesToUpgrade()
    {
        // list lg_iso_2 that are used in multiple languages
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('static_languages');

        return $queryBuilder->select('lg_iso_2')
            ->from('static_languages')
            ->where($queryBuilder->expr()->isNull('tx_snowbabel_override_language_key'))
            ->groupBy('lg_iso_2')
            ->having('COUNT(*) > 1')
            ->execute()
            ->fetchAll()
        ;
    }
}
