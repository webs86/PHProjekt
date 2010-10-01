<?php
/**
 * Phprojekt Module Action
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    PHProjekt
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
/**
 * The Phprojekt Module Action encapuslate methods that need to be implemented
 * by every controller that want's to use Phprojekts Module Magic.
 *
 * @category   PHProjekt
 * @package    PHProjekt
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Module_Action extends Zend_Controller_Action
{
    /**
     * Gets the class model of the module or the default one.
     *
     * @return Phprojekt_Model_Interface An instance of Phprojekt_Model_Interface.
     */
    protected function getModelObject()
    {
        $moduleName = $this->getRequest()->getModuleName();
        $object     = Phprojekt_Loader::getModel($moduleName, $moduleName);
        if (null === $object) {
            /* @todo throw error */
            $object = Phprojekt_Loader::getModel('Default', 'Default');
        }

        return $object;
    }
}
