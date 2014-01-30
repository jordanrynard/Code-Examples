<?

namespace MediaCenter;

class Torrents {
	
	static function add($data_id=false){
		if (empty($data_id)){
			Error::fatal("Could not 'Add' Torrent because data_id not set"); 
		}
		DB::insert('torrents', array('data_id'=>$data_id,'selected'=>time()));
		Debug::msg("[".$data_id."] Torrent has been added to the DB and marked as Selected");
		return true;
	}

	static function remove($data_id=false){
		if (empty($data_id)){
			Error::fatal("Could not 'Remove' Torrent because data_id not set"); 
		}
		DB::remove("torrents",array('data_id'=>$data_id));
		Debug::msg("[".$data_id."] Torrent has been UnSelected");
		return true;
	}

	static function parseTorrentHash($magnet_link){
		$torrent_hash = end(explode(":", reset(explode("&", $magnet_link)) ));
		if (empty($torrent_hash)){
			Error::warning("Problem parsing a Torrent hash from the link: ".$link);
			return false;
		} 
		return $torrent_hash;
	}

// Get
/*===================================================== STATUS QUERIES ================================================================*/
	static function getNotFoundTorrents(){
		// This one is specific, don't change it. If it's marked as finding=0 then it gets picked up by the first getSelectedTorrents
		$results = DB::query("SELECT * FROM `torrents` WHERE torrents.not_found!=0 AND torrents.queued=0 AND (UNIX_TIMESTAMP(NOW()) - torrents.finding > 7200) AND torrents.not_found_gave_up=0 ORDER BY torrents.selected ASC;"); // Rechecks every two hours 
		if (empty($results)){
			Debug::msg("No Not Found Torrents found");
		}
		return $results;
	}

	static function getDownloadedTorrents(){
		$results = DB::query("SELECT * FROM `torrents` WHERE torrents.downloaded!=0 ORDER BY torrents.selected ASC;");
		if (empty($results)){
			Debug::msg("No Downloaded Torrents found");
		}
		return $results;
	}

	static function getDownloadingCompleteTorrents(){
		$results = DB::query("SELECT * FROM `torrents` WHERE torrents.percent_done=100 AND torrents.downloaded=0 ORDER BY torrents.selected ASC;");
		if (empty($results)){
			Debug::msg("No Downloading Completed Torrents found");
		}
		return $results;
	}

	static function getDownloadingTorrents(){
		$results = DB::query("SELECT * FROM `torrents` WHERE torrents.downloading!=0 AND torrents.downloaded=0 ORDER BY torrents.selected ASC;"); 
		if (empty($results)){
			Debug::msg("No Currently Downloading Torrents found");
		}
		return $results;
	}

	static function getQueuedTorrents(){
		$results = DB::query("SELECT * FROM `torrents` WHERE torrents.queued!=0 AND torrents.downloading=0 ORDER BY torrents.selected ASC;"); 
		if (empty($results)){
			Debug::msg("No Queued Torrents found");
		}
		return $results;
	}

	static function getFoundTorrents(){
		$results = DB::query("SELECT * FROM `torrents` WHERE torrents.finding!=0 AND torrents.torrent_link!='' AND torrents.queued=0 ORDER BY torrents.selected ASC;"); 
		if (empty($results)){
			Debug::msg("No Found Torrents to Queue for Download");
		}
		return $results;
	}

	static function getSelectedTorrents(){
		$results = DB::query("SELECT * FROM `torrents` WHERE torrents.finding=0 ORDER BY torrents.selected ASC;"); 
		if (empty($results)){
			Debug::msg("No Selected Torrents found");
		}
		return $results;
	}

// Do
/*===================================================== FIND ================================================================*/
	static function findTorrents(){
		$torrents = array_merge(self::getSelectedTorrents(), self::getNotFoundTorrents());
		foreach ($torrents as $torrent){
			Debug::msg("[".$torrent['data_id']."] ".Data::getTitle($torrent['data_id'])." torrent is being searched for");
			if (Data::isMovie($torrent['data_id'])){
				TorrentsMovie::findTorrent($torrent['data_id']);
			}
			if (Data::isEpisode($torrent['data_id'])){
				TorrentsEpisode::findTorrent($torrent['data_id']);
			}
		}
	}


/*=================================================== FINDING (SUBFUNCTIONS) ==================================================================*/

	static function determine_best_torrent_from_files($torrents){
		foreach ($torrents as $key => $torrent){
			$torrents[$key]['file_score'] = -100; // Set a baseline for sorting. If a file is found that is good in a torrent, this will be set to at least 0
			$torrents[$key]['wanted_file_indexes'] = array();
			foreach ($torrent['files'] as $index => $file){
				if (self::is_video_file($file['path'])){ // IS VIDEO FILE
					if (self::titles_match($file['path'], $torrent['actual_title'], true)){ // IS TITLE IN FILENAME (remove this and move it with file count if it's too picky... So far it's really good!)
						if (!self::contains_avoided_keywords(self::clean_up_torrent_name($file['path']), $torrent['actual_title'])){
							$score = self::score_file($file['path']);
							$torrents[$key]['file_score'] = $score;
							$torrents[$key]['wanted_file_indexes'][] = (int)$index;
						}
					}
				}
			}
			// Not a fan of having more than 1 video file
			if (count($torrents[$key]['wanted_file_indexes']) > 1){ // If it's empty it'll already have a file score of -100
				$torrents[$key]['file_score'] -= 1;
			}
		}
		usort($torrents, function($a, $b) {
		    $rdiff = $b['file_score'] - $a['file_score'];
		    // If File score is a tie, revert to Torrent score
		    // Really we should be combining the torrent score with the file score... (Let's see how this works out in BETA) Theoretically the filename is important?
		    if ($rdiff) return $rdiff;
		    $rdiff = $b['score'] - $a['score'];
		    if ($rdiff) return $rdiff;
		    return $b['seeds'] - $a['seeds'];
		});
		Debug::msg(\print_r($torrents,true));
//		if (empty($torrents[0]) || empty($torrents[0]['file_score']) || empty($torrents[0]['wanted_file_indexes'])){
		if (empty($torrents[0]) || !isset($torrents[0]['file_score']) || empty($torrents[0]['wanted_file_indexes'])){
			return false;
		}
		return $torrents[0];
	}

	static function make_filename_safe($string){
	   	$string = preg_replace('/[^A-Za-z0-9]/', ' ', $string); // Replace everything with spaces, except for letters/numbers/periods (for easy title comparison), and hyphens/pluses (for e01-e02 occurences) 
	    $string = preg_replace('/( )+/', ' ', $string); // Remove excess spaces
		return $string;
	}

	static function clean_up_torrent_name($string){
	   	$string = preg_replace('/[^A-Za-z0-9\.\-\+]/', ' ', $string); // Replace everything with spaces, except for letters/numbers/periods (for easy title comparison), and hyphens/pluses (for e01-e02 occurences) 
	    $string = preg_replace('/( )+/', ' ', $string); // Remove excess spaces
	   	$string = str_ireplace(array('the complete series', 'the complete', 'complete series', 'complete'), "", $string); // Remove specific phrases / words
	   	// Don't remove brackets or years here, that should be done in the titles_match function
	   	return $string;
	}

	static function is_video_file($file_name){
		// Let's see if string ends with extension
		$exts = array(
			'.mp4',
			'.mkv',
			'.avi',
			'.m4v'
		);
		foreach ($exts as $ext){
			if (stripos(strrev($file_name), strrev($ext)) === 0){
				return true;
			}
		}
		return false;
	}
	
	static function score_file($file){
		Debug::msg(\print_r($file,true));
		$score = 0;
		$good = array(
			'.mp4'
		);
		$bad = array(
			'CD',
			'Part'
		);
		if (!empty($file['path'])){
			foreach ($good as $good_value){
				$score += stristr($file['path'], $good_value) ? 1 : 0;
			}
			foreach ($bad as $bad_value){
				$score -= stristr($file['path'], $bad_value) ? 1 : 0;
			}
		}
		if (!empty($file['size'])){
			if ($file['size'] < 1500000000){
				$score += 3;
			}
			if ($file['size'] > 1500000000){
				$score -= 3;
			}
		}
		return $score;
	}

	static function has_seeds($seeds){
		if ($seeds >= MIN_SEEDS){
			return true;
		}
		return false;
	}

	static function score_torrent($torrent){
		$score = 0;
		$good = array(
			'dvdrip',
			'brrip',
			'bdrip',
			'bluray'
		);
		$bad = array(
			// '720p',
			'5.1',
			'dvdscr',
			'webrip'
		);
		foreach ($good as $good_value){
			$score += stristr($torrent['title'], $good_value) ? 1 : 0;
		}
		foreach ($bad as $bad_value){
			$score -= stristr($torrent['title'], $bad_value) ? 1 : 0;
		}
		if ($torrent['seeds'] > 100){
			$score += 1;
		}
		if ($torrent['seeds'] > 500){
			$score += 1;
		}
		if ($torrent['seeds'] > 1000){
			$score += 1;
		}
		if ($torrent['seeds'] < 20){
			$score -= 1;
		}
		if ($torrent['seeds'] < 5){
			$score -= 2;
		}
		/*
		if (!empty($torrent['files'])){
			$score += 1;
		}
		*/
		return $score;
	}

	static function contains_avoided_keywords($string, $title){
		$array = array(
			"subtitulado", "espanol", "latino", "spanish",
			"swedish", "german", "italian", "french", "swiss", "russian",
			"sub",
			"dub",
			"3D",
			"sample",
			"cam",
			"ts",
			"camrip",
			"1080p",
			"r5",
			"r6"
		);
		// For TV be sure to consider episode title as well...
		foreach ($array as $value){
			if (stristr($string, $value)) {
				if (preg_match("/[A-Za-z0-9](".$value.")/i", $string)){ // It occurs in a word
					continue; // The title still has the word in it...
				}
				return $value;
			}
		}
		return false;
	}

	static function titles_match($torrent_title, $download_title, $loose=false){
		$torrent_title_minimized = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $torrent_title));
		$download_title_minimized = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $download_title));
		if ($loose && stristr($torrent_title_minimized, $download_title_minimized)){
			return true;
		}
		if (stripos($torrent_title_minimized, $download_title_minimized) === 0){
			return true;
		}
		return false;
	}

/*===================================================== DOWNLOAD ================================================================*/
	static function downloadTorrents(){
		$torrents = self::getFoundTorrents();
		foreach ($torrents as $torrent){
			$torrent['title'] = Data::getTitle($torrent['data_id']);
			Debug::msg("[".$torrent['data_id']."] ".$torrent['title']." is being Queued for Download");
			$torrent_hash = Transmission::addTorrent($torrent);
			if (empty($torrent_hash)){
				Error::warning("[".$torrent['title']."] There was a problem adding the torrent link to Transmission: ".$torrent['torrent_link']);
				continue;
			}
			DB::update("torrents", array('queued'=>time(), 'torrent_hash'=>$torrent_hash), array('data_id'=>$torrent['data_id']));
			Status::updateStatus($torrent['data_id'], 30);
		}
	}

/*===================================================== QUEUED ================================================================*/
	static function updateQueuedTorrents(){
		$torrents = self::getQueuedTorrents();
		foreach ($torrents as $torrent){
			$torrent_status = Transmission::getTorrentStatus($torrent['torrent_hash']);
			$percent_done = intval($torrent_status['percentDone'] * 100);
			if ($percent_done > 0){ // Still Queued or not
				DB::update("torrents", array('downloading'=>time(), 'percent_done'=>$percent_done), array('data_id'=>$torrent['data_id']));
				Status::updateStatus($torrent['data_id'], 40, $percent_done);
			}
		}
	}

/*===================================================== DOWNLOADING ================================================================*/
	static function updateDownloadingTorrents(){
		// I know there's redundancy here with above, but it's more organized this way
		$torrents = self::getDownloadingTorrents();
		foreach ($torrents as $torrent){
			$torrent_status = Transmission::getTorrentStatus($torrent['torrent_hash']);
			$percent_done = intval($torrent_status['percentDone'] * 100);
			$percent = intval($torrent_status['percentDone'] * 100);
			DB::update("torrents", array('percent_done'=>$percent), array('data_id'=>$torrent['data_id']));			
			Status::updateStatus($torrent['data_id'], 40, $percent_done);
		}
	}

/*===================================================== DOWNLOADED ================================================================*/	
	static function processDownloadedTorrents(){
		// self::updateDownloadingTorrents(); // Just so percentages are fresh (removed this because there should be crossover of function here
		$torrents = self::getDownloadingCompleteTorrents();
		foreach ($torrents as $torrent){
			$details = Data::getDetails($torrent['data_id']);
			$torrent['title'] = $details['title']; // Required to pass to processDownloadedTorrentFiles()
			Debug::msg($details['title']." has completed downloading. Moving its files to the media folder now.");		
			$success = Files::processDownloadedTorrentFiles($torrent);
			if ($success){
				DB::update("torrents", array("downloaded"=>time()), array('data_id'=>$torrent['data_id']));
				Transmission::removeTorrent($torrent['torrent_hash']);
				Status::updateStatus($torrent['data_id'], 100);
				XBMC::rpc('VideoLibrary.Scan');
				if (Data::isMovie($torrent['data_id'])){
					Notifications::sms($details['title'],"In Library.");
				} elseif (Data::isEpisode($torrent['data_id'])){
					Notifications::sms($details['title']." S".str_pad($details['season'],2,'0',STR_PAD_LEFT)."E".str_pad($details['episode'],2,'0',STR_PAD_LEFT),"In Library.");
				}
			}
		}
	}


/*
	static function getTorrent($item){
		$results = DB::select("torrents", array(), array("data_id"=>$data_id));
		if (empty($results)){
			return false;
		}
		return $results[0];
	}
*/

}

?>