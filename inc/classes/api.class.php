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
        
        // directly after connecting
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
                return $this->bind_channel(func_get_arg(1), func_get_arg(2));
                
                break;
            
            
        }
        
    }
    
   /**
    * Binds to a channel event
    * @param string $listen String to search for
    * @param callback $function Function to be executed
    * @param boolean $regex Is $listen a regex expression?
    * @return boolean Success?
    */
    private final function bind_channel($listen, $function) {
        
        // Check types
        if(!is_string($listen) || !is_callable($function))
            return false;
        
        // Append the function to the event array
        $this->events[EV_CHANNEL][] = array('listen' => $listen, 
                                            'function' => $function);
        
        return true;
        
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
                    
                    var_dump($cur_event);
                    
                    if(trim(strtolower($cur_event['listen'])) == trim(strtolower($command))) {                        
                        call_user_func_array($cur_event['function'], array(&$this, &$channel, &$sender, $arg));
                    }
                    
                }
                
                break;
            
            
        }
        
    }
    
}

?>
