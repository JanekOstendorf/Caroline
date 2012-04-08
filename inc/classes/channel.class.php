<?php

/**
 * @author ozzy <ozzy@skyirc.net>
 * @copyright Copyright (c) ozzy
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package Caroline
 */

class channel {
    
    /**
     * ID in the database
     * @var int 
     */
    private $id;
    
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
     * @return boolean Success
     */
    public function __construct($name, &$irc, $join = false) {
        
        // Store name
        $this->name = $name;
        
        // Create IRC contect reference
        $this->irc =& $irc;
        
        var_dump('SELECT * FROM channels WHERE name = \''.SQLite3::escapeString($name).'\' LIMIT 1');
        
        // Fetch info from the database
        if(!$result = $this->irc->db->query('SELECT * FROM channels WHERE name = \''.SQLite3::escapeString($name).'\' LIMIT 1')) {
            return false;            
        }
        
        // Channel does not exist
        if($result->fetchArray() === false || count($result->fetchArray()) != 1) {
            
            // Create channel
            $this->irc->db->exec('INSERT INTO channels (name, joined) VALUES(\''.SQLite3::escapeString($name).'\', 0)');
            $result = $this->irc->db->query('SELECT * FROM channels WHERE name = \''.SQLite3::escapeString($name).'\' LIMIT 1');
            
        }
        
        // Get ID
        $data = $result->fetchArray(SQLITE3_ASSOC);
        
        $this->id = $data['id'];
        
        if($join)
            $this->join();
        
        return true;
        
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
        $this->irc->db->exec('UPDATE channels SET joined = 1 WHERE id = '.$this->id);
        
    }
    
    /**
     * Part this channel
     * @param string $message Part message
     */
    public function part($message) {
        
        $this->irc->raw('PART '.$this->name.' :'.$message);
        $this->irc->db->exec('UPDATE channels SET joined = 0 WHERE id = '.SQLite3::escapeString($this->id));
        
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
    
    /**
     * Removes a channel from the database
     * @return boolean Success
     */
    public function delete() {
        
        if(!$this->irc->db->exec('DELETE FROM channels WHERE id = '.SQLite3::escapeString($this->id)))
            return false;
        
        $this->__destruct();
        
        return true;
        
    }
    
}

?>
