<?

// https://trac.transmissionbt.com/wiki/ConfigurationParameters
// https://trac.transmissionbt.com/browser/trunk/extras/rpc-spec.txt

namespace MediaCenter;

class Transmission {

	static function api_request($method="", $arguments=array()){
		$postdata = array(
	        'method' => $method,
	        'arguments' => $arguments
		);
		connect:
		$handle = curl_init();
		curl_setopt( $handle, CURLOPT_URL, TRANSMISSION_RPC_URL );
		curl_setopt( $handle, CURLOPT_POST, true );
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $handle, CURLOPT_HEADER, true );
    	curl_setopt( $handle, CURLOPT_POSTFIELDS, json_encode($postdata) );		
		curl_setopt( $handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $handle, CURLOPT_USERPWD, TRANSMISSION_USER . ':' . TRANSMISSION_PASS );
		if (!empty($transmission_session_id)) {
	    	curl_setopt( $handle, CURLOPT_HTTPHEADER, array('X-Transmission-Session-Id: ' . $transmission_session_id) );
	    }

		$raw_response = curl_exec($handle);
		if ($raw_response === false) {
	    	Error::fatal("Could not connect to the Transmission URL");
		}
		list($header, $body) = explode("\r\n\r\n", $raw_response, 2);
	    $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

	    curl_close($handle);

		if ($http_code == "409" && empty($transmission_session_id)){ // We should initially get a 409 error here - so let's make sure we get it before continuing on
			if (preg_match( "/X-Transmission-Session-Id: ([A-z0-9]*)/", $header, $matches)){
				$transmission_session_id = $matches[1];
				goto connect;
			}
		    Error::fatal("Didn't receive a X-Transmission-Session-Id in the headers.");
		}

		if ($http_code == "200"){
		    $data = json_decode($body, true);
			if ($data['result'] != 'success'){
				Error::fatal("There was a problem in the response from the Transmission RPC...\n".\print_r($data,true));
			}
		    return $data;
		}

		return false;
	}

	static function getSettings(){
		// Get Transmission's Settings
		$transmission_settings = self::api_request('session-get', array());
		return $transmission_settings['arguments'];
	}

	static function getSetting($name){
		$settings = self::getSettings();
		return $settings[$name];
	}

	static function getSessionStats(){
		$session_stats = self::api_request('session-stats', array());
		return $session_stats['arguments'];
	}

	static function getFreeSpace(){
		$free_space = self::api_request('free-space', array('path'=>DOWNLOADS_DIR));
		return $free_space['arguments'];
	}

	static function getAllTorrentsInfo(){
		$session_stats = self::api_request('torrent-get', array('fields'=>array('id','hashString','doneDate','downloadedEver','isFinished','percentDone','status')));
		return $session_stats['arguments']['torrents'];
	}

	static function getTorrentStatus($torrent_hash){
		// IDs need to be INT casted
		$result = self::api_request('torrent-get', array('ids'=>array(0=>$torrent_hash), 'fields'=>array('id','doneDate','downloadedEver','isFinished','percentDone','status')));
		return @$result['arguments']['torrents'][0];
	}

	static function getTorrentDetails($torrent_hash){
		// IDs need to be INT casted
		$result = self::api_request('torrent-get', array('ids'=>array(0=>$torrent_hash), 'fields'=>array('id','doneDate','downloadedEver','isFinished','percentDone','status', 'files', 'fileStats', 'wanted')));
		return @$result['arguments']['torrents'][0];
	}

	static function addTorrent($torrent){
		// ! Important: Files get created even when files-unwanted is selected. This is ok, it's just the piece data that is in the way that it has to download
		$indexes = unserialize($torrent['file_indexes']);
		$result = self::api_request('torrent-add', array('filename'=>$torrent['torrent_link'], 'paused'=>true, 'download-dir'=>self::getTorrentDownloadDirectory($torrent['title']))); // File index file-wanted/file-unwanted doesn't work for some reason here )
		$torrent_hash = @$result['arguments']['torrent-added']['hashString']; // Switched to hashString because Transmission reused IDs
		$result = self::api_request('torrent-set', array('ids'=>array(0=>$torrent_hash), 'files-wanted'=>$indexes, 'files-unwanted'=>array())); // File index file-unwanted doesn't work for some reason here )
		$result = self::api_request('torrent-start', array('ids'=>array(0=>$torrent_hash))); // File index file-unwanted doesn't work for some reason here )
		return $torrent_hash;
	}

	static function stopTorrent($torrent_hash){
		$result = self::api_request('torrent-stop', array('ids'=>array(0=>$torrent_hash))); // File index file-unwanted doesn't work for some reason here )
		Debug::msg("Torrent Stopped: ".json_encode($result));
	}

	static function removeTorrent($torrent_hash){
		$result = self::api_request('torrent-stop', array('ids'=>array(0=>$torrent_hash))); // File index file-unwanted doesn't work for some reason here )
		$result = self::api_request('torrent-remove', array('ids'=>array(0=>$torrent_hash))); // File index file-unwanted doesn't work for some reason here )
		Debug::msg("Torrent Removed: ".json_encode($result));
	}

	static function getTorrentDownloadDirectory($title){
		Debug::msg("Torrent Download Directory: ".DOWNLOADS_DIR.'/'.clean_string($title));
		return DOWNLOADS_DIR.'/'.clean_string($title);
	}

}

?>