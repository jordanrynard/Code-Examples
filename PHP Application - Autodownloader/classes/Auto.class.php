<?

namespace MediaCenter;

class Auto {

	static function setShow($tvdb_id, $setting){
		$anything_after = -1;
		if ($setting == 'new'){
			$anything_after = time();
		}
		if ($setting == 'all'){
			$anything_after = 0;
		}
		$result = DB::query("INSERT INTO `auto` SET data_id=:tvdb_id, anything_after=:anything_after ON DUPLICATE KEY UPDATE anything_after=:anything_after", array(':tvdb_id'=>$tvdb_id, ':anything_after'=>$anything_after));
		Debug::msg("[".$tvdb_id."] ".Data::getTitle($tvdb_id)." has been added to auto_shows. Set to: ".ucwords($setting));
		return $result;
	}

	static function removeShow($tvdb_id, $setting){
		$result = DB::query("DELETE FROM `auto` WHERE data_id=:tvdb_id", array(':tvdb_id'=>$tvdb_id));
		return $result;
	}



/*===============================================================================================================================*/
	static function checkAutoShows(){
		$auto_shows = DB::query("SELECT * FROM `auto`");
		if (empty($auto_shows)){
			Error::quit("There are no Auto Shows in the DB");
		}
		foreach ($auto_shows as $auto_show){
			$title = Data::getTitle($auto_show['data_id']);
			Debug::msg("Checking for next episode: ".$title);
			// First get all the initially qualified episodes
			$episodes = DB::query("SELECT * FROM `data_episodes` WHERE data_episodes.parent_tvdb_id=:data_id AND data_episodes.first_aired>=:anything_after ORDER BY data_episodes.first_aired ASC;", array(':data_id'=>$auto_show['data_id'], ':anything_after'=>$auto_show['anything_after']));
			if (empty($episodes)){
				Debug::msg("[".$auto_show['data_id']."] There is no Episode for ".$title." in the DB to download based on Auto setting.");
				continue;
			}
			// Make an or string for each qualifying episode to check against existing torrents with
			$or_string = '';
			foreach ($episodes as $episode){
				$or_string .= "torrents.data_id=".$episode['tvdb_id']." OR ";
			}
			$or_string = trim($or_string, " OR ");
			// Check for currently downloading episodes (as we only want to download one episode at a time; so the 2nd episode isn't done before the 1st episode)
			$currently_downloading_torrents = DB::query("SELECT * FROM `torrents` WHERE (".$or_string.") AND downloaded=0;"); // 
			if (!empty($currently_downloading_torrents)){ 
				Debug::msg("There is already an episode of ".$title." currently downloading.");
				continue;
			}
			// Check against existing torrents for any of the episode ids (return any that are unwatched)
			// $unwatched_torrents = DB::query("SELECT * FROM `torrents` LEFT JOIN `files` ON torrents.data_id=files.data_id WHERE (".$or_string.") AND ((files.play_count=0 AND files.resume_time=0) OR (files.play_count IS NULL AND files.resume_time IS NULL));"); // 
			$unwatched_torrents = DB::query("SELECT * FROM `torrents` LEFT JOIN ".DB_XBMC_NAME.".episodeview AS episodeview ON episodeview.c20=torrents.data_id WHERE (".$or_string.") AND ((episodeview.playCount=0 AND episodeview.resumeTimeInSeconds=0) OR (episodeview.playCount IS NULL AND episodeview.resumeTimeInSeconds IS NULL));"); // 			if (count($unwatched_torrents) >= 3){ // We use 3 because a show could be short (maybe check the run time of a show here and if it's <> than 30 minutes...)
			if (count($unwatched_torrents) >= 3){ // We use 3 because a show could be short (maybe check the run time of a show here and if it's <> than 30 minutes...)
				Debug::msg("There are already 3 unwatched/in progess Torrents for ".$title);
				continue;
			}
			// Looks like we're good to go // This query is really important and well thought out - don't mess with it - seriously, there is no other way to do this - this checks against ALL torrents and will find episodes that fall in between (if such a gap did exist for some reason)
			$episodes = DB::query("SELECT * FROM `data_episodes` LEFT JOIN `torrents` ON torrents.data_id=data_episodes.tvdb_id WHERE data_episodes.parent_tvdb_id=:data_id AND data_episodes.first_aired>=:anything_after AND torrents.data_id IS NULL ORDER BY data_episodes.first_aired ASC;", array(':data_id'=>$auto_show['data_id'], ':anything_after'=>$auto_show['anything_after']));
			$episode = $episodes[0];
			// Check and see if the Next episode is too far in the future to Download
			if ($episode['first_aired']+TV_SHOW_WAIT_TIME_BEFORE_DOWNLOADING > time()){ // 1 hour and a half (allow sufficient time since episode aired to start looking for it to prevent shitty torrents)
				Debug::msg("The next episode for ".$title." hasn't aired yet | ".date("M d Y / g:ia", $episode['first_aired']));
				continue;
			} 
			Debug::msg("An episode of ".$title." has been selected for Download");
			Debug::msg(\print_r($episode,true));
			Torrents::add($episode['tvdb_id']);
		}

	}


}

?>