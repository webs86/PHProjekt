<?php
/**
 * Engine class
 * 
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    CVS: $Id$
 * @license    
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @since      File available since Release 1.0
 * 
 */

/**
 * Cleaner Engine
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: <package_version>
 * @license    
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 1.0
 */
class Cleaner_Engine
{    
    /**
     * Class Name for Messages
     *
     * @var string
     */
    protected $_messagesClass = 'Cleaner_Messages';
    
    /**
     * Class Name for Sanitizer
     *
     * @var string
     */
    protected $_sanitizerClass = 'Cleaner_Sanitizer';
    
    /**
     * Class Name for Escaper
     *
     * @var string
     */
    protected $_escaperClass = 'Cleaner_Escaper';
    
    /**
     * Default Class Name for Validator
     *
     * @var string
     */
    protected $_validatorClass = 'Cleaner_Validator';

    /**
     * Instance of Cleaner_Engine (Singleton)
     *
     * @var Cleaner_Engine
     * 
     */
    protected static $_instance = null;

    /**
     * Returns singleton
     *
     * @return object singleton
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance  = new Cleaner_Engine();
        }
        
        return self::$_instance;
    }
    
    /**
     * Returns new Messages-Instance (Factory)
     *
     * @return object
     */
    public static function getMessages()
    {
        $name = self::getInstance()->_messagesClass;
        if (class_exists($name)) {
            return new $name();
        } else {
            throw new Exception('Message class not found');
        }
    }
    
    /**
     * Returns new Sanitizer-Instance (Factory)
     *
     * @return object
     */
    public static function getSanitizer()
    {
        $name = self::$_instance->_sanitizerClass;
        return new $name();
    }
    
    /**
     * Returns new Validator-Instance (Factory)
     *
     * @return object
     */
    public static function getValidator()
    {
        $name = self::$_instance->_validatorClass;
        return new $name();
    }
    
    /**
     * Returns new Escaper-Instance (Factory)
     *
     * @return object
     */
    public static function getEscaper()
    {
        $name = self::$_instance->_escaperClass;
        return new $name();
    }
        
    /**
     * Set classname of individual Sanitizer implementation
     *
     * @param string $className name of the class
     * 
     * @return void
     */
    public static function setSanitizerClass($className)
    {
        self::$_instance->_sanitizerClass = $className;
    }
    
    /**
     * Set classname of individual Validator implementation
     *
     * @param string $className name of the class
     * 
     * @return void
     */    
    public static function setValidatorClass($className)
    {
        self::$_instance->_validatorClass = $className;
    }

    /**
     * Set classname of individual Escaper implementation
     *
     * @param string $className name of the class
     * 
     * @return void
     */    
    public static function setEscaperClass($className)
    {
        self::$_instance->_escaperClass = $className;
    }

    /**
     * Set classname of individual Messages implementation
     *
     * @param string $className name of the class
     * 
     * @return void
     */
    public static function setMessagesClass($className)
    {
        self::$_instance->_messagesClass = $className;
    }
        
    /**
     * Escapes a value by a certain scope and type
     *
     * @param string $scope Scope, where value will be used (HTML, SQL, ..)
     * @param string $type  Subtype/Subscope where value will be used
     * @param mixed  $value Value to escape
     * 
     * @return mixed Escaped value
     */
    public static function escape($scope, $type, $value)
    {
        $scope = strtolower($scope);
        $type  = strtolower($type);
        
        $escaper = self::getEscaper();
        
        if (!array_key_exists($scope, $escaper->escapers)) {
            throw new Cleaner_Exception('Escaper for Scope '.$scope.
            ' does not exist');
        }
        
        $scopeTypes = $escaper->escapers[$scope];
        
        if (!array_key_exists($type, $scopeTypes)) {
            throw new Cleaner_Exception('Escaper of Type '.$type.
            ' does not exist');
        }
        
        return call_user_method('escape' . $scopeTypes[$type], $escaper,
         $value);        
    }
    
    /**
     * Sanitized a value to a certain type
     *
     * @param string $type      Type value should be sanitized to
     * @param mixed  $value     Value to sanitize
     * @param object &$messages Mesages generated by Sanitizer
     * 
     * @return mixed Sanitized value
     */
    public static function sanitize($type, $value, &$messages)
    {
        $type = strtolower($type);
        
        $sanitizer = self::getSanitizer();
        
        if (!array_key_exists($type, $sanitizer->sanitizers)) {
            throw new Cleaner_Exception('Sanitizer of Type '.$type.
            ' does not exist');
        }
        
        return call_user_method_array('sanitize' . 
        $sanitizer->sanitizers[$type], $sanitizer, array($value, $messages));        
    }
    
    /**
     * Validates a value against a certain type
     *
     * @param string $type      Type to validate against
     * @param mixed  $value     Value to validate
     * @param object &$messages Messages generated by Validator
     * 
     * @return bool true=>valid, false=>invalid
     */
    public static function validate($type, $value, &$messages)
    {
        $type = strtolower($type);
        
        $validator = self::getValidator();
        
        if (!array_key_exists($type, $validator->validators)) {
            throw new Cleaner_Exception('Validator of Type ' . $type . 
            ' does not exist');
        }
        
        return call_user_method_array('validate' . 
        $validator->validators[$type], $validator, 
        array($value, $messages));        
    }    
}