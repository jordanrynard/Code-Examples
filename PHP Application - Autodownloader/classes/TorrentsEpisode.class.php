<?

namespace MediaCenter;

class TorrentsEpisode extends Torrents {

	static function findTorrent($data_id){
		DB::update("torrents", array("finding"=>time()), array('data_id'=>$data_id));
		Status::updateStatus($data_id, 20);
		$details = Data::getDetails($data_id);
		// First Stage Search: The Simpsons S01E01
		$query = $details['title']." S".str_pad($details['season'],2,"0",STR_PAD_LEFT)."E".str_pad($details['episode'],2,"0",STR_PAD_LEFT);
		$viable_torrents = array();
		$tpbParser = new ThePirateBayParser($query);
		if ($tpbParser->error){
			DB::update("torrents", array("finding"=>0), array('data_id'=>$data_id)); // Something went wrong; we were never here
			Error::fatal("Error at The Pirate Bay");
		}
		foreach ($tpbParser->torrents as $tpb_torrent){
			$tpb_torrent_name_cleaned = self::clean_up_torrent_name($tpb_torrent['title']);
			if (!self::has_seeds($tpb_torrent['seeds'])){
				Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Not enough seeds: ".$tpb_torrent['seeds']);
				continue;
			}
			if ($contains = self::contains_avoided_keywords($tpb_torrent_name_cleaned, $details['title'])){
				Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Title contains: ".$contains);
				continue;
			}
			$parsed_torrent = self::parse_torrent_name_season_episode($tpb_torrent_name_cleaned, $details['episode']);
			if ($parsed_torrent && count($parsed_torrent) >= 4){
				$tpb_torrent['parsed']['name'] = $parsed_torrent[0];
				$tpb_torrent['parsed']['title'] = $parsed_torrent[1];
				$tpb_torrent['parsed']['season'] = $parsed_torrent[2];
				$tpb_torrent['parsed']['episode'] = $parsed_torrent[3];
			}
			if (!self::titles_match($tpb_torrent['parsed']['title'], $details['title'])){
				Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Title doesn't match");
				continue;
			}
			if (!self::seasons_match($tpb_torrent['parsed']['season'], $details['season'])){
				Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Season doesn't match");
				continue;
			}
			if (!self::episodes_match($tpb_torrent['parsed']['episode'], $details['episode'])){
				Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Episode doesn't match");
				continue;
			}
			$score = self::score_torrent($tpb_torrent);
			$viable_torrents[] = array_merge($tpb_torrent, array('score'=>$score,'actual_title'=>$details['title'],'data_id'=>$data_id));
		}
		$viable_titles = '';
		foreach($viable_torrents as $vt){$viable_titles.=$vt['title']."\n";}
		Debug::msg("[".$details['title']."] Viable Results as follows:\n".trim($viable_titles,"\n"));
		usort($viable_torrents, function($a, $b) {
		    $rdiff = $b['score'] - $a['score'];
		    if ($rdiff) return $rdiff;
		    return $b['seeds'] - $a['seeds'];
		});
		$torrent_to_download = self::determine_best_torrent_from_files($viable_torrents); // Don't use this on the Second search
		if (empty($viable_torrents) || empty($torrent_to_download)){
			Error::notice("[".$details['title']."] No Torrents Found.");
			$old_query = $query;
			// Second Stage Search: The Simpsons Season 1
			$query = $details['title']." Season ".$details['season'];
			Error::notice("[".$data_id."] Nothing using the query: ".$old_query." let's try searching for just the Season, and go inside to look at the files.");
			$viable_torrents = array();
			$tpbParser = new ThePirateBayParser($query);
			if ($tpbParser->error){
				DB::update("torrents", array("finding"=>0), array('data_id'=>$data_id)); // Something went wrong; we were never here
				Error::fatal("Error at The Pirate Bay");
			}
			foreach ($tpbParser->torrents as $tpb_torrent){
				$tpb_torrent_name_cleaned = self::clean_up_torrent_name($tpb_torrent['title']);
				if (!self::has_seeds($tpb_torrent['seeds'])){
					Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Not enough seeds: ".$tpb_torrent['seeds']);
					continue;
				}
				if ($contains = self::contains_avoided_keywords($tpb_torrent_name_cleaned, $details['title'])){
					Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Title contains: ".$contains);
					continue;
				}
				foreach ($tpb_torrent['files'] as $index => $file){
					$file['title'] = basename($file['path']);
					$file['seeds'] = $tpb_torrent['seeds']; // Required for the Scoring
					$tpb_file_name_cleaned = self::clean_up_torrent_name($file['title']);
					if ($contains = self::contains_avoided_keywords($tpb_file_name_cleaned, $details['title'])){
						Debug::msg("[".$tpb_file_name_cleaned."] Avoided. Name contains: ".$contains);
						continue;
					}
					// Parse the Torrent Details using REGEX
					$parsed_name = self::parse_torrent_name_season_episode($tpb_file_name_cleaned, $details['episode']);
					if (!$parsed_name || count($parsed_name) < 4){
						continue;
					}
					$file['parsed'] = array();
					$file['parsed']['name'] = $parsed_name[0];
					$file['parsed']['title'] = $parsed_name[1];
					$file['parsed']['season'] = $parsed_name[2];
					$file['parsed']['episode'] = $parsed_name[3];
					//
					// Maybe do a search for Title of Episode instead of season/episode
					//
					if (!self::titles_match($file['parsed']['title'], $details['title'])){
						Debug::msg("[".$tpb_file_name_cleaned."] Avoided. Title doesn't match"." [no_live_debug]");
						continue;
					}
					if (!self::seasons_match($file['parsed']['season'], $details['season'])){
						Debug::msg("[".$tpb_file_name_cleaned."] Avoided. Season doesn't match"." [no_live_debug]");
						continue;
					}
					if (!self::episodes_match($file['parsed']['episode'], $details['episode'])){
						Debug::msg("[".$tpb_file_name_cleaned."] Avoided. Episode doesn't match"." [no_live_debug]");
						continue;
					}
					$score = self::score_torrent($file);
					$viable_torrents[] = array_merge($tpb_torrent, array('score'=>$score,'actual_title'=>$details['title'],'data_id'=>$data_id, 'wanted_file_indexes'=>array(0=>$index)));
				}
			}
			$viable_titles = '';
			foreach($viable_torrents as $vt){$viable_titles.=$vt['title']."\n";}
			Debug::msg("[".$details['title']."] Viable Results as follows:\n".trim($viable_titles,"\n"));
			usort($viable_torrents, function($a, $b) {
			    $rdiff = $b['score'] - $a['score'];
			    if ($rdiff) return $rdiff;
			    return $b['seeds'] - $a['seeds'];
			});
			$torrent_to_download = @$viable_torrents[0];
			if (empty($viable_torrents) || empty($torrent_to_download)){
				Error::notice("[".$details['title']."] No Torrents Found.");
				$old_query = $query;
				// Third Stage Search: The Simpsons
				$query = $details['title'];
				Error::notice("[".$data_id."] Nothing using the query: ".$old_query." let's try searching for just the Show name, and go inside to look at the files.");
				$viable_torrents = array();
				$tpbParser = new ThePirateBayParser($query);
				if ($tpbParser->error){
					DB::update("torrents", array("finding"=>0), array('data_id'=>$data_id)); // Something went wrong; we were never here
					Error::fatal("Error at The Pirate Bay");
				}
				foreach ($tpbParser->torrents as $tpb_torrent){
					$tpb_torrent_name_cleaned = self::clean_up_torrent_name($tpb_torrent['title']);
					if (!self::has_seeds($tpb_torrent['seeds'])){
						Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Not enough seeds: ".$tpb_torrent['seeds']);
						continue;
					}
					if ($contains = self::contains_avoided_keywords($tpb_torrent_name_cleaned, $details['title'])){
						Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Title contains: ".$contains);
						continue;
					}
					foreach ($tpb_torrent['files'] as $index => $file){
						$file['title'] = basename($file['path']);
						$file['seeds'] = $tpb_torrent['seeds']; // Required for the Scoring
						$tpb_file_name_cleaned = self::clean_up_torrent_name($file['title']);
						if ($contains = self::contains_avoided_keywords($tpb_file_name_cleaned, $details['title'])){
							Debug::msg("[".$tpb_file_name_cleaned."] Avoided. Name contains: ".$contains);
							continue;
						}
						// Parse the Torrent Details using REGEX
						$parsed_name = self::parse_torrent_name_season_episode($tpb_file_name_cleaned, $details['episode']);
						if (!$parsed_name || count($parsed_name) < 4){
							continue;
						}
						$file['parsed'] = array();
						$file['parsed']['name'] = $parsed_name[0];
						$file['parsed']['title'] = $parsed_name[1];
						$file['parsed']['season'] = $parsed_name[2];
						$file['parsed']['episode'] = $parsed_name[3];
						//
						// Maybe do a search for Title of Episode instead of season/episode
						//
						if (!self::titles_match($file['parsed']['title'], $details['title'])){
							Debug::msg("[".$tpb_file_name_cleaned."] Avoided. Title doesn't match"." [no_live_debug]");
							continue;
						}
						if (!self::seasons_match($file['parsed']['season'], $details['season'])){
							Debug::msg("[".$tpb_file_name_cleaned."] Avoided. Season doesn't match"." [no_live_debug]");
							continue;
						}
						if (!self::episodes_match($file['parsed']['episode'], $details['episode'])){
							Debug::msg("[".$tpb_file_name_cleaned."] Avoided. Episode doesn't match"." [no_live_debug]");
							continue;
						}
						$score = self::score_torrent($file);
						$viable_torrents[] = array_merge($tpb_torrent, array('score'=>$score,'actual_title'=>$details['title'],'data_id'=>$data_id, 'wanted_file_indexes'=>array(0=>$index)));
					}
				}
				$viable_titles = '';
				foreach($viable_torrents as $vt){$viable_titles.=$vt['title']."\n";}
				Debug::msg("[".$details['title']."] Viable Results as follows:\n".trim($viable_titles,"\n"));
				usort($viable_torrents, function($a, $b) {
				    $rdiff = $b['score'] - $a['score'];
				    if ($rdiff) return $rdiff;
				    return $b['seeds'] - $a['seeds'];
				});
				$torrent_to_download = @$viable_torrents[0];
				if (empty($viable_torrents) || empty($torrent_to_download)){
					Error::notice("[".$details['title']."] No Torrents Found.");
					/*
					$old_query = $query;
					$query = $details['title'];
					*/
					Error::notice("[".$data_id."] Nothing using the query: ".$old_query." we're fresh outta search queries.");
					DB::update("torrents", array('not_found'=>time()), array('data_id'=>$data_id));
					Status::updateStatus($data_id, 120);
					return;
				}
			}
		}
/*
		print_r($torrent_to_download);
		die();
*/
		Debug::msg("[".$torrent_to_download['title']."] A Torrent has been selected based on its score.");
		Debug::msg(\print_r($torrent_to_download,true));
		DB::update("torrents", array('torrent_link'=>$torrent_to_download['torrent_link'],'file_indexes'=>serialize($torrent_to_download['wanted_file_indexes'])), array('data_id'=>$data_id));
		Debug::msg("[".$torrent_to_download['title']."] has been added to the Torrents DB.");
		Debug::spacer("");
	}

	static function parse_torrent_name_season_episode($string, $episode){ // We pass it the episode in case it's a double episode
		Debug::msg("Parsing Torrent Name: ".$string." [no_live_debug]");
		// Matches Basic Show/Season/Episode Pattern (Falling Skies s01e01, Falling Skies Season 1 Episode 01, Falling Skies S1Ep2)
		// Matches double episode Pattern (s01e01-02, S01e01+02, S01E01E02, The Office S09E24-25, The Office S09E22E23)
		// [] means match any of these chars; () means match exact contents; one of these followed by ? means that they're optional
		// Question mark following a dynamic sekector (.*, .+) is lazy, so it will stop as soon as it can... Otherwise a part of an expression is greedy, and keeps on going as far as it can!! (resulting in something like: 24.Sea)
		
		// Removed 'US' from the torrent title (because if I'm not searching with a country extension in the name, it's probably a US show, otherwise I'd specify UK, or CA)
		// Note that I used \. on each side of US cause I don't want it matching words that contain 'us', and all special chars have been replaced with a period.
		// I changed it to match anything not a letter or number, so could be a - or something
		// Had to make opening match not greedy -- can this have a negative result? there's nothing wildcard in between it and the season match... so that's good

		// Accomodate for 'PROPER' between show name and episode? 
		// Accomodate for multiple occurances in the same title like: Season 1 Episode 01 s01e01
		// Accomodate for Initials? (America's Next Top Model - ANTM)

		// $pattern = '/^(.+)(?:S|Season)[^0-9]?([0-9]+)[^0-9]?(?:E|Ep|Episode)[^0-9]?([0-9]+)(?:[-\+E]([0-9]+))?.*$/i'; // (?:foo) means that it won't capture! (http://php.net/manual/en/regexp.reference.subpatterns.php)
		$pattern = '/^(.+?)(?:[^a-zA-Z0-9]US[^a-zA-Z0-9])?(?:S|Season)[^0-9]?([0-9]+)[^0-9]?(?:E|Ep|Episode)[^0-9]?([0-9]+)(?:[-\+E]([0-9]+))?.*$/i'; // (?:foo) means that it won't capture! (http://php.net/manual/en/regexp.reference.subpatterns.php)
		if (preg_match($pattern, $string, $matches)){
			if (!empty($matches[4]) && intval($matches[4]) == $episode){ // In case it's a double episode, we get two Episode Matches
				$tmp = $matches[3]; 
				$matches[3] = $matches[4];
				$matches[4] = $tmp;
				unset($tmp); 
			}
			Debug::msg("RegEx matched."." [no_live_debug]");
			Debug::msg(json_encode($matches)." [no_live_debug]");
			return $matches;
		}
		Debug::msg("Nothing found using Standard parse RegEx; Trying the alternative."." [no_live_debug]");
		// Basic/Old Pattern (The Simpsons 1x01, The Simpsons [1.01])
		$pattern = '/^(.+)([0-9]+)[^0-9]([0-9]+)(?:[-\+E]([0-9]+))?.*$/i'; // (?:foo) means that it won't capture! (http://php.net/manual/en/regexp.reference.subpatterns.php)
		if (preg_match($pattern, $string, $matches)){
			if (!empty($matches[4]) && intval($matches[4]) == $episode){
				$tmp = $matches[3]; // We do this because we need a record of both Episode Numbers for the DB
				$matches[3] = $matches[4];
				$matches[4] = $tmp;
				unset($tmp); 
			}
			Debug::msg("RegEx matched."." [no_live_debug]");
			Debug::msg(json_encode($matches)." [no_live_debug]");
			return $matches;
		}
		Debug::msg("Nothing found using the alternative parse RegEx either."." [no_live_debug]");
		return false;
	}

	static function seasons_match($season_1, $season_2){
		return intval($season_1) == intval($season_2);
	}
	static function episodes_match($episode_1, $episode_2){
		return intval($episode_1) == intval($episode_2);
	}

}

?>