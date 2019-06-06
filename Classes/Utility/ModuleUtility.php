<?php
namespace PITS\Snowbabel\Utility;

/***
 *
 * This file is part of the "Snowbabel" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 Ebin C Mathew | Chinnu L | HOJA MUSTAFFA ABDUL LATHEEF, PIT Solutions Pvt. Ltd.
 *
 ***/

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PITS\Snowbabel\Service\Configuration;
use PITS\Snowbabel\Service\Translations;
use TYPO3\CMS\Core\Http\AjaxRequestHandler;

class ModuleUtility
{

    /**
     * @var Configuration
     */
    private $confObj;

    /**
     * @var Extensions
     */
    private $extObj;

    /**
     * @var Languages
     */
    private $langObj;

    /**
     * @var Columns
     */
    private $colObj;

    /**
     * @var Translations
     */
    private $systemTranslationObj;

    /**
     * @var Labels
     */
    private $labelsObj;

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
        $this->queryBuilder       = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function ActionController(ServerRequestInterface $request, ResponseInterface $response) {
        // get configuration object
        $this->getConfigurationObject();

        $action = $request->getQueryParams()['ActionKey'];
        $languageId = $request->getQueryParams()['LanguageId'];
        $columnId = $request->getQueryParams()['ColumnId'];

        if(!empty($languageId) && $action == 'LanguageSelection') {
            $this->confObj->actionUserConfSelectedLanguages($languageId);
        } elseif( $action == 'ListView_Update') {
            $action = $request->getQueryParams()['ActionKey'];
            $translationId = $request->getQueryParams()['TranslationId'];
            $translationValue = $request->getQueryParams()['TranslationValue'];

            // Get Label Object
            $this->getLabelsObject();

            // Update Translation
            $this->labelsObj->updateTranslation($translationId,$translationValue);
        } elseif(!empty($columnId) && $action == 'ColumnSelection') {
            $this->confObj->actionUserConfigurationColumns($columnId);
        } elseif( $action == 'ConfigurationChanged') {

            // Did Configuration Changed?
            if(!$this->confObj->getApplicationConfiguration('ConfigurationChanged')) {
                $response->getBody()->write(json_encode('success'));
                return $response;
            } else {
                $response->getBody()->write(json_encode('failure'));
                return $response;
            }

        }

        $this->getListView($request,$response);
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getExtensionsList(ServerRequestInterface $request, ResponseInterface $response) {

        // get configuration object
        $this->getConfigurationObject();

        // get extension object
        $this->getExtensionsObject();

        // get all extensions for this user
        $Extensions = $this->extObj->getExtensions();

        $response->getBody()->write(json_encode($Extensions));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getLanguageSelection(ServerRequestInterface $request, ResponseInterface $response) {

        // get configuration object
        $this->getConfigurationObject();

        // get language object
        $this->getLanguageObject();

        // get available languages
        $Languages = $this->langObj->getLanguages();

        $response->getBody()->write(json_encode($Languages));
        return $response;
    }


    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getColumnSelection(ServerRequestInterface $request, ResponseInterface $response) {

        // get configuration object
        $this->getConfigurationObject();

        // get column object
        $this->getColumnObject();

        // get available columns
        $Columns = $this->colObj->getColumns();

        $response->getBody()->write(json_encode($Columns));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getGeneralSettings(ServerRequestInterface $request, ResponseInterface $response) {

        // get configuration object
        $this->getConfigurationObject();

        // Set Values
        $FormData['success'] = true;

        // Get All Values From Configuration
        $FormData['data']['LocalExtensionPath'] = $this->confObj->getApplicationConfiguration('LocalExtensionPath');
        $FormData['data']['SystemExtensionPath'] = $this->confObj->getApplicationConfiguration('SystemExtensionPath');
        $FormData['data']['GlobalExtensionPath'] = $this->confObj->getApplicationConfiguration('GlobalExtensionPath');

        $FormData['data']['ShowLocalExtensions'] = $this->confObj->getApplicationConfiguration('ShowLocalExtensions') ? 1 : 0;
        $FormData['data']['ShowSystemExtensions'] = $this->confObj->getApplicationConfiguration('ShowSystemExtensions') ? 1 : 0;
        $FormData['data']['ShowGlobalExtensions'] = $this->confObj->getApplicationConfiguration('ShowGlobalExtensions') ? 1 : 0;

        $FormData['data']['ShowOnlyLoadedExtensions'] = $this->confObj->getApplicationConfiguration('ShowOnlyLoadedExtensions') ? 1 : 0;
        $FormData['data']['ShowTranslatedLanguages'] = $this->confObj->getApplicationConfiguration('ShowTranslatedLanguages') ? 1 : 0;

        $FormData['data']['XmlFilter'] = $this->confObj->getApplicationConfiguration('XmlFilter') ? 1 : 0;

        $FormData['data']['AutoBackupEditing'] = $this->confObj->getApplicationConfiguration('AutoBackupEditing') ? 1 : 0;
        $FormData['data']['AutoBackupCronjob'] = $this->confObj->getApplicationConfiguration('AutoBackupCronjob') ? 1 : 0;

        $FormData['data']['CopyDefaultLanguage'] = $this->confObj->getApplicationConfiguration('CopyDefaultLanguage') ? 1 : 0;

        $response->getBody()->write(json_encode($FormData));

        return $response;

    }

    /**
     * Renders complete list of available extensions
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getExtensionMenu(ServerRequestInterface $request, ResponseInterface $response) {

        // Todo: check logic
        $ExtensionArray = [];

        // Get Configuration Object
        $this->getConfigurationObject();

        // Get System Translation Object
        $this->getSystemTranslationObject();

        // Init System Translation Object
        $this->systemTranslationObj->init($this->confObj);

        // Get All Available Extensions
        $Extensions = $this->systemTranslationObj->getDirectories();

        // Get Approved Extensions
        $approvedExtensions = $this->confObj->getApplicationConfiguration('ApprovedExtensions');

        // Prepare For Output
        if(is_array($Extensions) && count($Extensions) > 0) {
            foreach($Extensions as $Extension) {

                // Do Not Add Extension If Already Approved
                if(!in_array($Extension, $approvedExtensions)) {
                    array_push($ExtensionArray, array('ExtensionKey' => $Extension));
                }
            }
        }

        $i = 0;
        foreach ($ExtensionArray as $key => $value) {
            $extArray[$i] = $value['ExtensionKey'];
            $i++;
        }

        $response->getBody()->write(json_encode($extArray));
        return $response;
    }

    /**
     * Renders list of selected extensions
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getApprovedExtensionsAdded(ServerRequestInterface $request, ResponseInterface $response) {

        // todo: check logic
        $ApprovedExtensionsArray = [];

        // Get Configuration Object
        $this->getConfigurationObject();

        // Set Values
        $ApprovedExtensions = $this->confObj->getApplicationConfiguration('ApprovedExtensions');

        // Prepare For Output
        if(is_array($ApprovedExtensions) && count($ApprovedExtensions) > 0) {
            foreach($ApprovedExtensions as $ApprovedExtension) {
                array_push($ApprovedExtensionsArray, array('ExtensionKey' => $ApprovedExtension));
            }
        }

        $i = 0;
        foreach ($ApprovedExtensionsArray as $key => $value) {
            $extArray[$i] = $value['ExtensionKey'];
            $i++;
        }

        $response->getBody()->write(json_encode($extArray));
        return $response;
    }

    /**
     * Renders complete list of available languages
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getGeneralSettingsLanguages(ServerRequestInterface $request, ResponseInterface $response) {

        // Get Configuration Object
        $this->getConfigurationObject();

        // Set Values
        $Languages = $this->confObj->getLanguages(true);

        $response->getBody()->write(json_encode($Languages));
        return $response;

    }

    /**
     * Renders list of selected languages
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getGeneralSettingsLanguagesAdded(ServerRequestInterface $request, ResponseInterface $response) {

        // todo: check logic
        $extjsParams = [];

        // Get Configuration Object
        $this->getConfigurationObject();

        // Set Values
        $Languages = $this->confObj->getApplicationConfiguration('AvailableLanguages');

        $response->getBody()->write(json_encode($Languages));
        return $response;
    }

    /**
     * Renders list of labels for selected extensions and languages
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
    */
    public function getListView(ServerRequestInterface $request, ResponseInterface $response) {

        $params = [];

        // get configuration object
        $this->getConfigurationObject();

        if(isset($request->getQueryParams()['uid'])){
            $params['ExtensionId'] = $request->getQueryParams()['uid'];
        }elseif(isset($request->getQueryParams()['ExtensionId'])){
            $params['ExtensionId'] = $request->getQueryParams()['ExtensionId'];
        }else{
            $params['ExtensionId'] = 1;
        }
        
        $params['ListViewStart'] = 0;
        $params['ListViewLimit'] = 50;
        $params['SearchGlobal'] = FALSE;
        $params['SearchString'] = '';

        // Get Label Object
        $this->getLabelsObject();

        $this->confObj->setExtjsConfiguration($params);

        // Set Metadata For Extjs
        $this->labelsObj->setMetaData();

        // Get Labels From Selected Extension
        $Labels = $this->labelsObj->getLabels($params);

        foreach ($Labels['columns'] as $key => $value) {
            if(count($value)>3){
                $Labels['headings'][]= $value;
            }
            if($value['header']=='Label' && $value['hidden']==TRUE){
                $Labels['ShowLabels'] = FALSE;
                foreach ($Labels['LabelRows'] as $key => $result) {
                    unset($Labels['LabelRows'][$key]['LabelName']);
                }
            } elseif ($value['header']=='Label' && $value['hidden']==FALSE){
                $Labels['ShowLabels'] = TRUE;
            }
            if($value['header']=='Default' && $value['hidden']==TRUE){
                $Labels['ShowDefaults'] = FALSE;
                foreach ($Labels['LabelRows'] as $key => $result) {
                    unset($Labels['LabelRows'][$key]['LabelDefault']);
                }
            } elseif ($value['header']=='Default' && $value['hidden']==FALSE){
                $Labels['ShowDefaults'] = TRUE;
            }
        }

        $response->getBody()->write(json_encode($Labels));
        return $response;
    }

    /**
     * Creates a configuration object
     * @return void
     */
    private function getConfigurationObject() {
        if(!is_object($this->confObj) && !($this->confObj instanceof Configuration)) {
            $this->confObj = GeneralUtility::makeInstance('PITS\\Snowbabel\\Service\\Configuration');
        }
    }

    /**
     * Creates an object of Labels
     * @return void
     */
    private function getSystemTranslationObject() {
        if(!is_object($this->systemTranslationObj) && !($this->systemTranslationObj instanceof Translations)) {
            $this->systemTranslationObj = GeneralUtility::makeInstance('PITS\\Snowbabel\\Service\\Translations');
        }
    }

    /**
     * Creates an object of Extensions
     * @return void
     */
    private function getExtensionsObject() {
        if(!is_object($this->extObj) && !($this->extObj instanceof Extensions)) {
            $this->extObj = GeneralUtility::makeInstance('PITS\\Snowbabel\\Record\\Extensions', $this->confObj);
        }
    }

    /**
     * Creates an object of Languages
     * @return void
     */
    private function getLanguageObject() {
        if(!is_object($this->langObj) && !($this->langObj instanceof Languages)) {
            $this->langObj = GeneralUtility::makeInstance('PITS\\Snowbabel\\Record\\Languages', $this->confObj);
        }
    }


    /**
     * Creates an object of Columns
     * @return void
     */
    private function getColumnObject() {
        if(!is_object($this->colObj) && !($this->colObj instanceof Columns)) {
            $this->colObj = GeneralUtility::makeInstance('PITS\\Snowbabel\\Record\\Columns', $this->confObj);
        }
    }

    /**
     * Creates an object of Labels
     * @return void
     */
    private function getLabelsObject() {
        if(!is_object($this->labelsObj) && !($this->labelsObj instanceof Labels)) {
            $this->labelsObj = GeneralUtility::makeInstance('PITS\\Snowbabel\\Record\\Labels', $this->confObj);
        }
    }

}
