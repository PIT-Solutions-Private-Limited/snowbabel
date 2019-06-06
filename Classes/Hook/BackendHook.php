<?php

namespace PITS\Snowbabel\Hook;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Ebin C Mathew | Chinnu L | HOJA MUSTAFFA ABDUL LATHEEF, PIT Solutions Pvt. Ltd.
 *
 *  You may not remove or change the name of the author above. See:
 *  http://www.gnu.org/licenses/gpl-faq.html#IWantCredit
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendHook
{

    /**
     * @var string
     */
    public $queryBuilder = null;

    /**
     * Description
     * @return type
     */
    public function __construct()
    {
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * Execute PreRenderHook for possible manipulation:
     * Add deepl.css,overrides localization.js and recordlist.js
     */
    public function executePreRenderHook(&$hook)
    {
        //override Localization.js
        $flag = 0;

        if (is_array($hook['cssFiles'])){
            foreach ($hook['cssFiles'] as $key => $value) {
                if (is_int(strpos($key,"angular-material.min.css"))){
                    $flag = 1;
                    break;
                }
            }
        }
        
        if ($flag == 1){
            unset($hook['cssFiles']['sysext/backend/Resources/Public/Css/backend.css']);
        }        

    }
}
