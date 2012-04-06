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
     * Error string for the socket connection
     * @var string
     */
    private $error_string;
    
    /**
     * Error number for the socket connection
     * @var int
     */
    private $error_no;
    
    /**
     * Verbose logging?
     * @var boolean
     */
    private $verbose_log = false;
    
    /**
     * Received line
     * @var array
     */
    private $msg;
    
    
    
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
    
    /**
     * Writes $message in our logfile
     * @param string $message Message to be logged
     * @return boolean Success
     */
    private function write_log($message) {
        
        $fp = fopen($this->config->get('misc.logfile'), 'a');
        
        if(!$fp) 
            return false;
        
        // Write our content
        $content = '['.date('Y-m-d H:i:s e').'] '.$message.'\n';
        
        if(!fwrite($fp, $content))
            return false;
        
        return true;
        
    }
    
    /**
     * Sends $message to the def output
     * @param string $message
     * @return boolean Success 
     */
    private function output($message) {
        
        if(!$this->write_log($message))
            return false;
        
        if(!print('['.date('Y-m-d H:i:s e').'] '.$message))
            return false;
        
    }
    
    
    /**#@+
     * Basic IRC functions
     */
    
    /**
     * Opens a socket connection to the IRC server
     * @param string $server IRC server to connect to
     * @param int $port IRC port
     * @return boolean Success
     */
    private function connect($server, $port) {
        
        $this->socket = fsockopen($server,
                                  $port,
                                  $this->error_no, 
                                  $this->error_string, 
                                  5);
        
        if(!$this->socket) {
            
            $this->write_log('Failed to connect to '.$this->config->get('general.server').':'.$this->config->get('general.port').' - '.$this->error_string."\n");
            return false;
            
        }
        
        return true;
        
    }
    
    /**
     * Closes the socket connection opened by connec()
     * @return boolean Success
     */
    private function disconnect() {
        
        if(!fclose($this->socket)) {
            $this->write_log('Failed to disconnect - '.$this->error_string);
            return false;
        }
        
        return true;
        
    }
    
    /**
     * Sends RAW to the IRC server
     * @param string $raw 
     * @return boolean Success
     */
    private function raw($raw) {
        
        if(!fputs($this->socket, $raw."\n")) {
            
            if($this->verbose_log)
                $this->output('Failed to write in the socket: \''.$raw.'\' - '.$this->error_string);
            
            return false;
        }
        
        if($this->verbose_log)
                $this->output('[Sending] '.$raw."\n");
        
        return true;
        
    }
    
    /**
     * Logs us in on the IRC server
     * @param string $nick Nickname
     * @param string $realname Realname
     * @param string $ident Ident (no effect on machines with an identd)
     * @param string $password Server password/SASL password
     * @param boolean $sasl Use SASL?
     */
    private function login($nick, $realname, $ident, $password = NULL, $sasl = false) {
        
        // SASL to be added later
        /*if($sasl) {
            $this->raw('CAP REQ :sasl');
        }*/
        if($password != NULL) {
            $this->raw('PASS '.$password);
        }
        
        $this->raw('USER '.$ident.' 8 * :'.$realname);
        $this->raw('NICK '.$nick);
        
    }
    
    /**#@-*/
    
    public function run() {
        
        $this->connect($this->config->get('general.server'), $this->config->get('general.port'));
        $this->login($this->config->get('general.nick'), $this->config->get('general.realname'), $this->config->get('general.ident'), '***', true);
        
        while(true) {
        
            // Fetch line from the server
            $data = fgets($this->socket, 256);

            $this->output($data);

            flush();
            
            // Store the line
            $this->msg = explode(' ', $data);
            
            // Play ping pong
            if($this->msg[0] == 'PING') {
                $this->raw('PONG');
            }
            
        
        }
        
    }
    
    
    
}

?>
