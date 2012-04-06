<?php

/**
 * @author ozzy <ozzy@skyirc.net>
 * @copyright Copyright (c) ozzy
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package Caroline
 */

class user {
    
    /**
     * IRC contect
     * @var object
     */
    private $irc;
    
    /**
     * Name of the channel
     * @var string
     */
    public $nick;
    
    /**
     * Creates a new channel
     * @param object $irc IRC context (IRC class)
     */
    public function __construct(&$irc, $nick) {
        
        // Store name
        $this->nick = $nick;
        
        // Create IRC contect reference
        $this->irc =& $irc;
        
    }
    
    /**
     * Say to this user (PRIVMSG)
     * @param string $message Message to say
     */
    public function say($message) {
        
        $this->irc->privmsg($this->nick, $message);
        
    }
    
    /**
     * Perform an ACTION (/me)
     * @param string $message 
     */
    public function action($message) {
        
        $this->irc->action($this->nick, $message);
        
    }
    
    /**
     * Notices the user
     * @param string $message
     */
    public function notice($message) {
        
        $this->irc->notice($this->nick, $message);
        
    }
    
    
}

?>
