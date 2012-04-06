<?php

/**
 * @author ozzy <ozzy@skyirc.net>
 * @copyright Copyright (c) ozzy
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package Caroline
 */

// Are we on windows or on *NIX?
if(substr(php_uname(), 0, 7) == "Windows") {
    define('WINDOWS', true);
}
else {
    define('WINDOWS', false);
}

date_default_timezone_set('UTC');

// Absolute path to our directory
define('PATH', dirname(__FILE__).'/');

// Include the includes
require_once PATH.'inc/common.inc.php';

$irc = new irc();

$irc->read_config(PATH.'config.yml');
$irc->run();

?>