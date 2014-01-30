<?

namespace MediaCenter;

class Settings {

    function __construct() {
        $settings = get_settings();
    	foreach ($settings as $key => $value){
    		$this->{$key} = $value;
    	}
    }

    function get_settings(){
		$rows = DB::select('_settings',array(),array());
		$settings = array();
		foreach ($rows as $row){
			$settings[$row['name']] = $row['value'];
		}
		return $settings;
    }

    static function get($name){
        $result = DB::select("_settings", array("value"), array("name"=>$name));
        /*
        // Don't need this becaue we check if empty in the DB function
        if (empty($result)){
            \MediaCenter\Error::fatal("Could not get Settings: ".$name);
        }
        */
        return !empty($result[0]['value']) ? $result[0]['value'] : false;
    }

    static function set($name, $value){
        $result = DB::update("_settings", array("value"=>$value), array("name"=>$name));
        /*
        // Don't need this becaue we check if empty in the DB function
        if (empty($result)){
            \MediaCenter\Error::fatal("Could not update Settings: ".$name." = ".$value);
        }
        */
    }

    static function checkResources(){
        $free_space = Transmission::getFreeSpace();

        /*
        // Use PHP to get the DOWNLOADS_DIR freespace
        $session_stats = Transmission::getSessionStats();
        $settings =  Transmission::getSettings();
        // 'free-space' Method doesn't work for some reason
        // $free_space =  Transmission::getFreeSpace();
        $free_space = $settings['download-dir-free-space'];
        */
        if ($free_space < MIN_DISK_SPACE * 1000000){ // MB converted to bytes
            Error::fatal("You don't have enough Disk Space left.");
        }
    }


}
?>