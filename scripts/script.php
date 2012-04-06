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
$irc->bind(EV_CHANNEL, '!test', 'chan_test');

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

function chan_test(&$irc, &$channel, &$sender, $arg) {
    
    $channel->say($sender->nick.': '.implode(' ',$arg));
    
}

?>
