<?php

/**
 * @author ozzy <ozzy@skyirc.net>
 * @copyright Copyright (c) ozzy
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package Caroline
 */

class irc extends api {
    
    /**
     * IRC socket handler
     * @var resource
     */
    private $socket;
    
    /**
     * Configuration
     * @var object
     */
    private $config;
    
    
    /**
     * Initialize the API 
     */
    public function __construct() {
        
        parent::__construct();
        
    }
    
    /**
     * Reads the config
     * @param string $file Path to the configuration file 
     * @return boolean Success
     */
    public function read_config($file) {
        
        $this->config = new config($file);
        
        if($this->config === false)
            return false;
        else
            return true;
        
    }
    
    
    
    
}

?>
