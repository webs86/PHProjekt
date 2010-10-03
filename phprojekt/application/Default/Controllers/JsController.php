<?php
/**
 * JavaScript Controller.
 * The controller will return all the js files for the modules.
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
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * JavaScript Controller.
 * The controller will return all the js files for the modules.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class JsController extends IndexController
{
    /**
     * Array with all the modules found.
     *
     * @var array
     */
    private $_modules = array();

    /**
     * Array with all the templates by module.
     *
     * @var array
     */
    private $_templates = array();

    /**
     * Collect all the js files and return it as one.
     *
     * @return void
     */
    public function indexAction()
    {
        // System files, must be parsed in this order
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/phpr.js');
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Component.js');
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/form.js');
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/grid.js');
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Store.js');
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Date.js');
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Url.js');
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Tree.js');
        echo file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/FrontendMessage.js');

        // Default Folder
        $scripts = scandir(PHPR_CORE_PATH . '/Default/Views/dojo/scripts');
        echo $this->_getModuleScripts(PHPR_CORE_PATH . DIRECTORY_SEPARATOR, $scripts, 'Default');

        // Core Folder
        $scripts = scandir(PHPR_CORE_PATH . '/Core/Views/dojo/scripts');
        echo $this->_getModuleScripts(PHPR_CORE_PATH . DIRECTORY_SEPARATOR, $scripts, 'Core');

        // Load all the system modules and make and array of it
        $modules = $this->_processModuleDirectory(PHPR_CORE_PATH . DIRECTORY_SEPARATOR);

        // Load all the user modules and make and array of it
        $modules+= array_merge($modules, $this->_processModuleDirectory(PHPR_USER_CORE_PATH));

        foreach ($modules['javascript'] as $name => $info) {
            foreach($info as $script) {
                echo $script['content'];
            }
        }

        // Preload all the templates and save them into __phpr_templateCache
        echo 'var __phpr_templateCache = {};';

        foreach ($modules['templates'] as $name => $info) {
            foreach($info as $template) {
                $content = str_replace("'", "\'", $template['content']);
                $content = str_replace("\r", "", str_replace("\n", "", $content));
                $content = str_replace("<", "<' + '", $content);
                echo ' __phpr_templateCache["phpr.' . $name . '.template.' . $template['tplname']. '"] = \'' . $content . '\';'. "\n";
            }
        }

        echo 'dojo.provide("phpr.Main");';

        echo '
        dojo.declare("phpr.Main", null, {
            constructor:function(/*String*/webpath, /*String*/currentModule, /*Int*/rootProjectId,/*String*/language) {
                phpr.module           = currentModule;
                phpr.submodule        = null;
                phpr.webpath          = webpath;
                phpr.rootProjectId    = rootProjectId;
                phpr.currentProjectId = rootProjectId ;
                phpr.currentUserId    = 0;
                phpr.language         = language;
                phpr.config           = new Array();
                phpr.serverFeedback   = new phpr.ServerFeedback();
                phpr.Date             = new phpr.Date();
                phpr.loading          = new phpr.loading();
                phpr.DataStore        = new phpr.DataStore();
                phpr.InitialScreen    = new phpr.InitialScreen();
                phpr.BreadCrumb       = new phpr.BreadCrumb();
                phpr.frontendMessage  = new phpr.FrontendMessage();
                phpr.Tree             = new phpr.Tree();
                phpr.regExpForFilter  = new phpr.regExpForFilter();
                phpr.globalModuleUrl  = webpath + "index.php/Core/module/jsonGetGlobalModules";
        ';

        foreach ($modules['submodules'] as $name => $info) {
            $submodules = array_keys($info);
            echo '
                this.' . $name . ' = new phpr.' . $name . '.Main([\'' . join("','", $submodules) . '\']);
            ';
        }

        // The load method of the currentModule is called
        echo '
                dojo.publish(phpr.module + ".load");
            }
        });
        ';
    }

    /**
     * Collect all the js files in the module folder, and return it as one.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>name</b> Name of the module to consult.
     * </pre>
     *
     * @return void
     */
    public function moduleAction()
    {
        $module = Cleaner::sanitize('alnum', $this->getRequest()->getParam('name', null));
        $module = ucfirst(str_replace(" ", "", $module));

        // Load the module
        if (is_dir(PHPR_USER_CORE_PATH . $module . '/Views/dojo/scripts/')) {
            $scripts = scandir(PHPR_USER_CORE_PATH . $module . '/Views/dojo/scripts/');
        } else {
            $scripts = array();
        }

        echo $this->_getModuleScripts(PHPR_USER_CORE_PATH, $scripts, $module);

        // Preload the templates and save them into __phpr_templateCache
        foreach ($this->_templates as $templateData) {
            $content = str_replace("'", "\\" . "'", $templateData['contents']);
            $content = str_replace("<", "<' + '", $content);
            echo '
                __phpr_templateCache["phpr.' . $templateData['module'] . '.template.' . $templateData['name']
                . '"] = \'' . $content . '\';' . "\n";
        }

        echo '
            this.' . $module . ' = new phpr.' . $module . '.Main();
        ';
    }

    /**
     * Get all the Modules scripts.
     * In the process also collect the templates.
     *
     * @param string $path    Path to the module directory.
     * @param array  $scripts All the modules into the Module folder.
     * @param string $module  The module name.
     *
     * @return string Content of the files.
     */
    private function _getModuleScripts($path, $scripts, $module)
    {
        $output = '';
        foreach ($scripts as $script) {
            if (substr($script, -3) == '.js') {
                $output .= file_get_contents($path . $module . '/Views/dojo/scripts/' . $script);
            } else if ('template' == $script) {
                if (strstr($module, '/')) {
                    $templateModule = substr(strrchr($module, '/'), 1);
                } else {
                    $templateModule = $module;
                }
//                $this->_getTemplates($path . $module . '/Views/dojo/scripts/template/', $templateModule);
            }
        }

        return $output;
    }

    /**
     * Collect all the templates found in the $path directory.
     * Also scan the sub directories.
     *
     * @param string $path   Path for scan.
     * @param string $module Module Name.
     *
     * @return void
     */
    private function _getTemplates($path, $module)
    {
        $templates = scandir($path);
        foreach ($templates as $item) {
            if (!is_dir($path . $item)) {
                if (substr($item, -5) == '.html') {
                    // The item is a valid file
                    $fileContents = file_get_contents($path . $item);
                    $fileContents = str_replace("\n", "", $fileContents);
                    $fileContents = str_replace("\r", "", $fileContents);

                    $this->_templates[] = array('module'   => $module,
                                                'name'     => $item,
                                                'contents' => $fileContents);
                }
            } else {
                // The item is a subdirectory
                if ($item != '.' && $item != '..') {
                    $subItemPath = $path . $item . DIRECTORY_SEPARATOR;
                    foreach (scandir($subItemPath) as $subItem) {
                        if (!is_dir($subItemPath . $subItem) && substr($subItem, -5) == '.html') {
                            // The subitem is a valid file
                            $fileContents = file_get_contents($subItemPath . $subItem);
                            $fileContents = str_replace("\n", "", $fileContents);
                            $fileContents = str_replace("\r", "", $fileContents);

                            $this->_templates[] = array('module'   => $module,
                                                        'name'     => $item . "." . $subItem,
                                                        'contents' => $fileContents);
                        }
                    }
                }
            }
        }
    }

    /**
     * Load all the scritps from a module directory.
     *
     * @param string $path Path to the module directory.
     *
     * @return void
     */
    private function _processModuleDirectory($mpath)
    {
        /* we do all file operations in one method, to avoid traversing
         * the tree on every request several times */
        $modules = array('templates' => array(), 'submodules' => array(), 'javascript' => array());

        $it = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($mpath), true),
            "@^.*?/+(\w*?)(?:/SubModules/(\w*?))?/Views/dojo/scripts/(.*js|template/(.*html))$@i", RegexIterator::GET_MATCH);
        foreach($it as $matches) {
            list($path, $module, $submodule, $filename, $tplfilename) = $matches;

            $isTemplate  = !empty($tplfilename);
            $isSubmodule = !empty($submodule);

            /* this is just a micro optimization. we do not need to get contents
             * of submodules */
            $content = (false === $isSubmodule) ? file_get_contents($path) : '';
            if (!isset($modules['templates'][$module])) {
                $modules['templates'][$module]  = array();
                $modules['submodules'][$module] = array();
                $modules['javascript'][$module] = array();
            }

            if ($isTemplate) {
                /* template handling */
                $modules['templates'][$module][] = array('tplname'  => $tplfilename,
                                                         'content' => $content);
            } else if ($isSubmodule) {
                $modules['submodules'][$module][$submodule] = array('');
            } else {
                $modules['javascript'][$module][] = array('scriptname' => $filename,
                                                          'content' => $content);
            }
        }

        return $modules;
    }
}
