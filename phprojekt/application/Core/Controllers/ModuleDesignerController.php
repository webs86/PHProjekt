<?php
/**
 * Module Designer Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Module Designer Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_ModuleDesignerController extends Core_IndexController
{
    /**
     * Returns the detail for a module in JSON.
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $id           = (int) $this->getRequest()->getParam('id');
        $data         = array();
        $data['data'] = array();
        if (!empty($id)) {
            $module          = Phprojekt_Module::getModuleName($id);
            $model           = Phprojekt_Loader::getModel($module, $module);
            $databaseManager = new Phprojekt_DatabaseManager($model);
            $data['data']    = $databaseManager->getDataDefinition();
        }

        echo Phprojekt_Converter_Json::convert($data);
    }

    /**
     * Saves the current desginer of the module
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id 
     * @requestparam string  designerData
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $translate  = Zend_Registry::get('translate');
        $id         = (int) $this->getRequest()->getParam('id');
        $data       = $this->getRequest()->getParam('designerData');
	    $model      = null;
        $module     = $this->getRequest()->getParam('name');
        if (empty($module)) {
            $module = $this->getRequest()->getParam('label');
        }
        $module = ucfirst(ereg_replace(" ", "", $module));
        if ($id > 0) {
            $model = Phprojekt_Loader::getModel($module, $module);
	    }

        $databaseManager = new Phprojekt_DatabaseManager($model);
        $data            = Zend_Json_Decoder::decode($data);

        // Validate
        if ($databaseManager->recordValidate($module, $data)) {
            // Update Table Structure
            $tableData = $this->_getTableData($data);
            $databaseManager->syncTable($data, $module, $tableData);

            // Update DatabaseManager Table
            $databaseManager->saveData($module, $data);

            if (empty($id)) {
                $message = $translate->translate('The table module was created correctly');
            } else {
                $message = $translate->translate('The table module was edited correctly');
            }
            $type = 'success';
        } else {
            $error   = $databaseManager->getError();
            $message = $error['field'].': '.$error['message'];
            $type    = 'error';
        }

        $return = array('type'    => $type,
                        'message' => $message,
                        'code'    => 0,
                        'id'      => $id);

        echo Phprojekt_Converter_Json::convert($return);
    }
        
    /**
     * Get the length and type from the values
     *
     * @param array $data Array $_POST
     *
     * @return array
     */
    private function _getTableData($data)
    {
        $tableData = array();

        foreach ($data as $field) {
            $tableData[$field['tableField']]            = array();
            $tableData[$field['tableField']]['null']    = true;
            $tableData[$field['tableField']]['default'] = null;
            foreach ($field as $key => $value) {
                if ($key == 'tableType') {
                    $tableData[$field['tableField']]['type'] = $field['tableType'];
                } else if ($key == 'tableLength') {
                    $tableData[$field['tableField']]['length'] = $field['tableLength'];
                }
            }
        }

        return $tableData;
    }
}