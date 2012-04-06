<?php

/**
 * @author ozzy <ozzy@skyirc.net>
 * @copyright Copyright (c) ozzy
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package Caroline
 */

class api {
    
    /**
     * Storage variable for event functions
     * @var array
     */
    private $events = array();
    
    /**
     * Initializes the events
     */
    function __construct() {
        
        // Channel messages
        $this->events[EV_CHANNEL] = array();
        
        // PRIVMSGs
        $this->events[EV_PRIVMSG] = array();
        
        // NOTICEs
        $this->events[EV_NOTICE] = array();
        
        // before connecting
        $this->events[EV_BCONNECT] = array();
        
        // after logging in to the IRC server
        $this->events[EV_ACONNECT] = array();
       
    }
    
    /**
     * Binds to an event
     * @param int $type Event type
     * @return boolean Success 
     */
    public final function bind($type) {
        
        switch($type) {
            
            /*
             * CHANNEL EVENT
             */
            case EV_CHANNEL:

                // Check arguments
                if(func_num_args() != 3) {
                    return false;
                }
                
                // Call bind function
                // Check types
                if(!is_string(func_get_arg(1)) || !is_callable(func_get_arg(2)))
                    return false;

                // Append the function to the event array
                $this->events[EV_CHANNEL][] = array('listen' => func_get_arg(1), 
                                                    'function' => func_get_arg(2));
                
                return true;
                
                break;
                
            /*
             * BEFORE CONNECT EVENT
             */
            case EV_BCONNECT:
                
                // Check arguments
                if(func_num_args() != 2) {
                    return false;
                }
                
                // Call bind function
                // Check types
                if(!is_callable(func_get_arg(1)))
                    return false;

                // Append the function to the event array
                $this->events[EV_BCONNECT][] = array('function' => func_get_arg(1));
                
                return true;
                
            /*
             * AFTER CONNECT EVENT
             */
            case EV_ACONNECT:
                
                // Check arguments
                if(func_num_args() != 2) {
                    return false;
                }
                
                // Call bind function
                // Check types
                if(!is_callable(func_get_arg(1)))
                    return false;

                // Append the function to the event array
                $this->events[EV_ACONNECT][] = array('function' => func_get_arg(1));
                
                return true;
            
            
        }
        
    }
    
    public final function call_event($type) {
        
        switch($type) {
            
            /*
             * CHANNEL EVENT
             */
            case EV_CHANNEL:

                // Parse arguments
                $channel = func_get_arg(1);
                $sender = func_get_arg(2);
                $command = func_get_arg(3);
                $arg = func_get_arg(4);
                
                // Is there a function to call?
                foreach($this->events[EV_CHANNEL] as $cur_event) {
                    
                    if(trim(strtolower($cur_event['listen'])) == trim(strtolower($command))) {                        
                        call_user_func_array($cur_event['function'], array(&$this, &$channel, &$sender, $arg));
                    }
                    
                }
                
                break;
                
            /*
             * BEFORE CONNECT EVENT
             */
            case EV_BCONNECT:
                
                // Call all functions
                foreach ($this->events[EV_BCONNECT] as $cur_event) {
                    
                    call_user_func_array($cur_event['function'], array(&$this));
                
                }
                
                break;
                
            /*
             * AFTER LOGIN EVENT
             */
            case EV_ACONNECT:
                
                // Call all functions
                foreach ($this->events[EV_ACONNECT] as $cur_event) {
                    
                    call_user_func_array($cur_event['function'], array(&$this));
                
                }
                
                break;
            
            
        }
        
    }
    
}

?>
