<?

header("Content-Type: text/html; charset=utf-8"); // Required because of Trakt data

include ('config.php');
include ('functions.php');

// AUTOLOAD CLASSES
spl_autoload_register(function($class){
    $class = str_replace("MediaCenter\\","",$class);
    require_once(CLASS_DIR.'/'.$class.'.class.php');
});

// CHECK DEPENDENCIES
// SocketIO 
$result = @file_get_contents(SOCKETIO_URL);
if (empty($result)) {
	\MediaCenter\Error::fatal("The SOCKET.IO Server isn't running. [no_live_debug]"); // Keyword in Debug; don't Emit causing an Exception
}
// MySQL
$conn = mysql_connect(DB_HOST, DB_USER, DB_PASS);
if (empty($conn)){
	\MediaCenter\Error::fatal("MySQL isn't running.");
}
// Transmission
$result = MediaCenter\Transmission::api_request("session-get");
if (empty($result)) {
	\MediaCenter\Error::fatal("The Transmission Deamon isn't running.");
}

?>
