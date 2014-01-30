<?

namespace MediaCenter;

class IPs {

	function __construct(){
	}

	static function updateAddresses(){
		$server_ip = self::getServerAddress();
		Settings::set('server_ip',$server_ip);

		$local_ip = self::getLocalAddress();
		Settings::set('local_ip',$local_ip);
	}

	static function getLocalAddress(){
		return getHostByName(getHostName());
	}
	
	static function getServerAddress() {
	    if(isset($_SERVER["SERVER_ADDR"]))
		    return $_SERVER["SERVER_ADDR"];
	    else {
		    // Running CLI
		    if(stristr(PHP_OS, 'WIN')) {
		        //  Rather hacky way to handle windows servers
		        exec('ipconfig /all', $catch);
		        foreach($catch as $line) {
		        if(eregi('IP Address', $line)) {
		            // Have seen exec return "multi-line" content, so another hack.
		            if(count($lineCount = split(':', $line)) == 1) {
			            list($t, $ip) = split(':', $line);
				            $ip = trim($ip);
			            } else {
				            $parts = explode('IP Address', $line);
				            $parts = explode('Subnet Mask', $parts[1]);
				            $parts = explode(': ', $parts[0]);
				            $ip = trim($parts[1]);
			            }
			            if(ip2long($ip > 0)) {
				            // echo 'IP is '.$ip."\n";
				            self::check_if_empty_ip($match[1], "Server");
				            return $ip;
			            } else {
			            	// TODO: Handle this failure condition.
			            } 
			        }
		        }
		    } else {
		        $ifconfig = shell_exec('/sbin/ifconfig eth0');
		        preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
		        self::check_if_empty_ip($match[1], "Local");
		        return $match[1];
		    }
	    }
	}

	static function check_if_empty_ip($ip, $which){
    	if (empty($ip)){
	    	mail(Settings::get('administrator_email'), $which." IP Address is unobtainable. (".$mediaCenter->nametag.")", "You better address this issue.");
	    	Error::fatal($which." IP Address is unobtainable.");
	    }
	}

}

?>