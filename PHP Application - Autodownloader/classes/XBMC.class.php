<?

namespace MediaCenter;

class XBMC {

	function _construct(){
	}

	static function rpc($method='', $id=1, $params=array()){
		// Example: \MediaCenter\XBMC::rpc('GUI.ShowNotification', 1, array('title'=>'Hi','message'=>'Holla'));
		$params = json_encode((object)$params);
		if (empty($id)){
			$results = file_get_contents(XBMC_RPC_URL.'?request='.urlencode('{"jsonrpc": "2.0", "method": "'.$method.'", "params": '.$params.'}'));
		} else {
			$results = file_get_contents(XBMC_RPC_URL.'?request='.urlencode('{"jsonrpc": "2.0", "id": '.$id.', "method": "'.$method.'", "params": '.$params.'}'));
		}
		$result = json_decode($results, true);
		if ($result['result'] == 'OK' || is_array($result['result'])){
			return $result;
		}
		Error::warning("There was a problem communicating via the XBMC Json RPC: ".$results);
	}

	static function play($data_id=0){
		if (Data::isMovie($data_id)){
			$result = Data::getMovieFromXBMC($data_id);
			$movie_id = $result['idMovie'];
			self::rpc('Player.Open', 1, array('item'=>array('movieid'=>(int)$movie_id), 'options'=>array('resume'=>true))); 
			$type = "idMovie";
		} elseif (Data::isEpisode($data_id)){
			$result = Data::getEpisodeFromXBMC($data_id);
			$episode_id = $result['idEpisode'];
			self::rpc('Player.Open', 1, array('item'=>array('episodeid'=>(int)$episode_id), 'options'=>array('resume'=>true))); 
			$type = "idEpisode";
		}
		return array("msg"=>"Now Playing ".$type.": ".$data_id);
	}

	static function nowPlaying(){
		$result = \MediaCenter\XBMC::rpc('Player.GetActivePlayers', 1, array());
		if (empty($result['result'])){
			$result['result']['item']['label'] = "";
			return $result['result'];
		}
		$result = \MediaCenter\XBMC::rpc('Player.GetItem', 1, array('properties'=>array("title", "album", "artist", "season", "episode", "duration", "showtitle", "tvshowid", "thumbnail", "file", "fanart", "streamdetails"), 'playerid'=>1)); 
		// Get play state (if 0 then paused)
		$other_result = \MediaCenter\XBMC::rpc('Player.GetProperties', 1, array('properties'=>array('speed', 'time', 'totaltime'), 'playerid'=>1)); 
		// Need this here not sure why yet??
		if (is_array($other_result['result'])){
			$result['result']['item'] = array_merge($result['result']['item'], $other_result['result']);
		}
		// If a TV Show lets redefine the label (we could do this in the javascript but whatevs
		if ($result['result']['item']['episode'] > 0){
			$result['result']['item']['label'] = $result['result']['item']['showtitle']." S".str_pad($result['result']['item']['season'],2,"0",STR_PAD_LEFT)."E".str_pad($result['result']['item']['episode'],2,"0",STR_PAD_LEFT)." - ".$result['result']['item']['label'];
		}
		// Debug::msg("Now Playing: ".json_encode($result));
		return $result['result'];
	}


}

?>