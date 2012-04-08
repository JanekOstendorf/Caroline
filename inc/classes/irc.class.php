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
    public $config;
    
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
     * Channels
     * @var array 
     */
    public $channels = array();
    
    /**
     * SQLite object
     * @var object
     */
    public $db;
    
    
    
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
     * Opens our SQLite database
     * @param string $file SQLite file
     * @return boolean 
     */
    public function open_database($file) {
        
        $this->db = new SQLite3($file);
        
        if(!$this->db)
            return false;
        
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
    
    /**
     * Joins all channels in the database 
     */
    private function join_channels() {
        
        $result = $this->db->query('SELECT * FROM channels ORDER BY name');
        
        while($line = $result->fetchArray()) {
            
            $this->channels[$line['name']] = new channel($line['name'], $this, true);
            usleep(500);
            
        }
        
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
     * QUITs and closes the socket connection opened by connect()
     * @param string $message Quit message
     * @return boolean Success
     */
    public function disconnect($message) {
        
        $this->raw('QUIT :'.$message);
        
        sleep(1);
        
        if(!fclose($this->socket)) {
            $this->write_log('Failed to disconnect - '.$this->error_string);
            return false;
        }
        
        die('QUIT: '.$message);
        
        return true;
        
    }
    
    /**
     * Sends RAW to the IRC server
     * @param string $raw 
     * @return boolean Success
     */
    public function raw($raw) {
        
        if(!fputs($this->socket, $raw."\n")) {
            
            if($this->verbose_log)
                $this->output('Failed to write in the socket: \''.$raw.'\' - '.$this->error_string);
            
            return false;
        }
        
        
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
    
    /**
     * Sends a PRIVMSG zu $target
     * @param string $target User or channel
     * @param string $message Message to be sent
     */
    public function privmsg($target, $message) {
        $this->raw('PRIVMSG '.$target.' :'.$message);
    }
    
    /**
     * Sends a NOTICE to $target
     * @param string $target User or channel
     * @param string $message Message to be sent
     */
    public function notice($target, $message) {
        $this->raw('NOTICE '.$target.' :'.$message);
    }
    
    /**
     * PRIVMSGs an ACTION to $target
     * @param string $target User or channel
     * @param string $message Message to be sent
     */
    public function action($target, $message) {
        $this->raw('PRIVMSG '.$target.' :'.chr(1).'ACTION '.$message.chr(1));
    }
    
    /**#@-*/
    
    /**
     * Runs the bot 
     */
    public function run() {
        
        $this->call_event(EV_BCONNECT);
        
        $this->connect($this->config->get('general.server'), $this->config->get('general.port'));
        $this->login($this->config->get('general.nick'), $this->config->get('general.realname'), $this->config->get('general.ident'));  
        
        sleep(4);
        
        $this->join_channels();
        
        $this->call_event(EV_ACONNECT);
        
        while($this->socket) {
        
            // Fetch line from the server
            $data = fgets($this->socket, 256);

            $this->output($data);

            flush();
            
            // Remove the linebreak
            $data = substr($data, 0, strlen($data) - 2);
            
            // Store the line
            $this->msg = explode(' ', $data);
            
            // Play ping pong
            if($this->msg[0] == 'PING') {
                $this->raw('PONG');
            }
          
            
            // Is it a channel message?
            // style: :nick!ident@hostname PRIVMSG #channel :message
            if($this->msg[1] == 'PRIVMSG' && in_array(substr($this->msg[2], 0, 1), array('#', '&'))) {
                
                // Split up the hostmask
                $tmp = explode('!', substr($this->msg[0], 1));
                
                $sender = new user($this, $tmp[0]);
                
                if(!isset($this->channels[$this->msg[2]])) {
                    $this->channels[$this->msg[2]] = new channel($this->msg[2], $this, false);
                }
                
                // Channel
                $channel = $this->channels[$this->msg[2]];
                
                // Command
                $command = substr($this->msg[3], 1);
                
                // Arguments
                $arg = array_slice($this->msg, 4);
                
                $this->call_event(EV_CHANNEL, $channel, $sender, $command, $arg);
                
            }
        
        }
        
        $this->disconnect('Socket invalid.');
        
        return;
        
    }
    
    
    
}

?>
