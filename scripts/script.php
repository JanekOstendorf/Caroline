<?php

/**
 * @author ozzy <ozzy@skyirc.net>
 * @copyright Copyright (c) ozzy
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package Caroline
 */

// After login
$irc->bind(EV_ACONNECT, 'acon_id');

// Channel binds
$irc->bind(EV_CHANNEL, '!php', 'chan_php');
$irc->bind(EV_CHANNEL, '!join', 'chan_join');
$irc->bind(EV_CHANNEL, '!part', 'chan_part');

function acon_id(&$irc) {
    
    $irc->privmsg('NickServ', 'id Caroline ***');
    
}

function chan_php(&$irc, &$channel, &$sender, $arg) {
    
    if($sender->nick == 'ozzy') {
        
        $return = eval(implode(' ', $arg));
        if(!is_null($return))
            $channel->say('Return value: '.print_r($return, true));
        
    }
    
}

function chan_join(&$irc, &$channel, &$sender, $arg) {

    if(!in_array(substr($arg[0], 0, 1), array('#', '&'))) {
        $sender->notice($arg[0].' is an invalid channel name!');
        return false;
    }
    
    if(isset($irc->channels[$arg[0]])) {
        $sender->notice($arg[0].' is alreay in the channel database!');
        return false;
    }
    
    $irc->channels[$arg[0]] = new channel($arg[0], $irc, true);
            
    if(!$irc->channels[$arg[0]]) {
        $sender->notice('Error while adding/joining '.$arg[0].'!');
        return false;
    }
    
    $sender->notice('Added '.$arg[0].' to the database and joined it.');
    
}

function chan_part(&$irc, &$channel, &$sender, $arg) {
    
    if(!in_array(substr($arg[0], 0, 1), array('#', '&'))) {
        $sender->notice($arg[0].' is an invalid channel name!');
        return false;
    }
    
    if(!isset($irc->channels[$arg[0]])) {
        $sender->notice($arg[0].' is not in the channel database!');
        return false;
    }
    
    unset($irc->channels[$arg[0]]);
    
    $sender->notice('Left '.$arg[0].' and removed it from the database.');
    
}
?>
