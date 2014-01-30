<?php

	// SITE
	// define('URL', 'http://162.243.58.222');
	define('URL', 'http://triangle-pi.local');

	// SOCKETIO
	define('SOCKETIO_URL', URL.':31070');
	
	// TRANSMISSION
	define('TRANSMISSION_RPC_URL', URL.':9091/transmission/rpc/');
	define('TRANSMISSION_WEB_GUI_URL', URL.':9091');
	define('TRANSMISSION_USER', 'transmission');
	define('TRANSMISSION_PASS', 'A1234a1234');

	// MYSQL
	define('DB_HOST', '127.0.0.1');
	define('DB_NAME', 'pi_media_center');
	define('DB_USER', 'root');
	define('DB_PASS', 'A1234a1234');
	// MYSQL (XBMC)
	define('DB_XBMC_HOST', '127.0.0.1');
	define('DB_XBMC_NAME', 'xbmcvideos75');
	define('DB_XMBC_USER', 'root');
	define('DB_XBMC_PASS', 'A1234a1234');
	
	// XBMC
	define('XBMC_PORT', '8080');
	define('XBMC_HOST', 'triangle-pi.local');
	define('XBMC_RPC_URL', 'http://'.XBMC_HOST.':'.XBMC_PORT.'/jsonrpc');

	// DIRS
	define('ROOT_DIR', dirname(__FILE__));
	define('STORAGE_DIR', realpath(ROOT_DIR."/../../"));
	define('CRON_DIR', ROOT_DIR."/crons");
	define('CLASS_DIR', ROOT_DIR."/classes");
	define('DOWNLOADS_DIR', STORAGE_DIR.'/Downloads');
	// define ('MEDIA_DIR', ROOT_DIR.'/media');
	define ('TV_DIR', STORAGE_DIR.'/TV');
	define ('MOVIES_DIR', STORAGE_DIR.'/Movies');

	// DEBUG
	// Ensure Log Errors, Track Errors are on; error_log, error_log_cli set in php.ini
	define('DEBUG_ON', 0);
	define('LIVE_DEBUG_ON', 1);
 	define('DEBUG_DIR', ROOT_DIR.'/_debug');
   	define('LOG_FILE', DEBUG_DIR."/debug_log.log");
    define('LOG_LOCK_FILE', DEBUG_DIR."/.log.lock");

    // SETTINGS
    define('MIN_SEEDS', 20); // This really should be 1...
    define('TV_SHOW_WAIT_TIME_BEFORE_DOWNLOADING', 3600+1800); // 1 hour and a half... maybe change this in the code to be the length of the show + whatever is here - there are two files to change it in

	// SYSTEM
	define('MIN_DISK_SPACE', 1500); // MB
	define('MYSQL_CONNECT_TIMEOUT', 60);
	define('DEFAULT_SOCKET_TIMEOUT', 120);
	ini_set('mysql.connect_timeout', MYSQL_CONNECT_TIMEOUT);
	ini_set('default_socket_timeout', DEFAULT_SOCKET_TIMEOUT);


?>