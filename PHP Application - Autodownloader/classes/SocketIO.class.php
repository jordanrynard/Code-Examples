<?

namespace MediaCenter;

include (ROOT_DIR.'/libraries/ElephantIO/Client.php');
use ElephantIO\Client as Elephant;

class SocketIO {

	static function emit($action, $data=array()){

		$elephant = new Elephant(SOCKETIO_URL, 'socket.io', 1, false, true, true);

		$elephant->init();
		$elephant->send(
		    Elephant::TYPE_EVENT,
		    null,
		    null,
		    json_encode(array('name' => $action, 'args' => $data))
		);
		$elephant->close();

		Debug::msg("Sent Socket.IO data to action '".$action."': ".json_encode($data));
	}

}

?>