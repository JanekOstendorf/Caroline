<?php

/**
 * @author ozzy <ozzy@skyirc.net>
 * @copyright Copyright (c) ozzy
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package Caroline
 */

class config {

    /**
     * Array containing all data
     * @var array
     */
    public $array;
    
    /**
     * Reads the config file
     * @param stirng $file Path of the config file
     * @return boolean Success
     * @uses Spyc::YAMLLoad()
     */
    public function __construct($file) {
        
        // Check for file existance
        if(!file_exists($file)) {
            return false;
        }
        
        // Read the information
        $array = Spyc::YAMLLoad($file);
        
        // Errors?
        if($array === false) {
            return false;
        }
        
        // Save data
        $this->array = $array;
        
    }
    
    /**
     * Outputs the content of $var
     * @param string $var Config node
     */
    public function get($var) {
        
        if(!is_string($var)) {
            return false;
        }
        
        // Explode
        $var_arr = explode(".", $var);
        
        // temporary array
        $tmp = $this->array;
        
        // Try to get the string, recurse deeper and deeper ...
        foreach($var_arr as $cur) {
            
            if(isset($tmp[$cur])) {
                $tmp = $tmp[$cur];
            }
            else {
                $tmp = null;
                break;
            }
        }
        
        if(empty($tmp) || is_null($tmp)) {
            return $var;
        }
        
        return $tmp;
        
    }
    
}
?>