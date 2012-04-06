<?php

/**
 * @author ozzy <ozzy@skyirc.net>
 * @copyright Copyright (c) ozzy
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package Caroline
 */

class channel {
    
    /**
     * IRC contect
     * @var object
     */
    private $irc;
    
    /**
     * Name of the channel
     * @var string
     */
    private $name;
    
    /**
     * Creates a new channel
     * @param object $irc IRC context (IRC class)
     */
    public function __construct($name, &$irc, $join = false) {
        
        // Store name
        $this->name = $name;
        
        // Create IRC contect reference
        $this->irc =& $irc;
        
        if($join)
            $this->join();
        
    }
    
    /**
     * Deletes the channel 
     */
    public function __destruct() {
        
        $this->part('Parting '.$this->name);
        
    }
    
    /**
     * Join this channel 
     */
    public function join() {
        
        $this->irc->raw('JOIN '.$this->name);
        
    }
    
    /**
     * Part this channel
     * @param string $message Part message
     */
    public function part($message) {
        
        $this->irc->raw('PART '.$this->name.' :'.$message);
        
    }
    
    /**
     * Say to this channel (PRIVMSG)
     * @param string $message Message to say
     */
    public function say($message) {
        
        $this->irc->privmsg($this->name, $message);
        
    }
    
    /**
     * Perform an ACTION (/me)
     * @param string $message 
     */
    public function action($message) {
        
        $this->irc->action($this->name, $message);
        
    }
    
    /**
     * Notices the channel
     * @param string $message
     */
    public function notice($message) {
        
        $this->irc->notice($this->name, $message);
        
    }
    
    
}

?>
