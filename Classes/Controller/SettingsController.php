<?php

namespace Snowflake\Snowbabel\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Snowflake\Snowbabel\Service\Configuration;

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

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class SettingsController
 *
 * @package Snowflake\Snowbabel\Controller
 */
class SettingsController extends ActionController
{

    public $pageRenderer;

    /**
     * @var Configuration
     */
    private $confObj;

    public function __construct() {
        $this->pageRenderer = GeneralUtility::makeInstance('TYPO3\CMS\Core\Page\PageRenderer');
        $this->confObj      = GeneralUtility::makeInstance('Snowflake\Snowbabel\Service\Configuration');
    }

    /**
     * Show general information and the installed modules
     *
     * @return void
     */
    public function indexAction()
    {
        $compatibility = 1;
        $snowbabel_style = 'Settings.css';
        $this->view->assignMultiple(array(
            'compatibility' => $compatibility,
            'snowbabel_style' => $snowbabel_style, 
        ));
    }

    /**
     * Loaded when a submit is performed
     *
     * @return void
     */
    public function submitAction()
    {
        $submittedValues = $this->request->getArguments();
        $this->confObj->saveFormSettings($submittedValues);
        $this->view->assignMultiple(array(
            'currentTab' => end($submittedValues),
            'snowbabel_style' => 'Settings.css', 
        ));
        $this->pageRenderer->addJsInlineCode("success", "top.TYPO3.Notification.success('Saved', 'Values Saved Successfully');");
        $this->view->setTemplatePathAndFileName(ExtensionManagementUtility::extPath('snowbabel').'Resources/Private/Templates/Settings/Index.html');
    }

}
