<?

namespace MediaCenter;

class TorrentsMovie extends Torrents {

	static function findTorrent($data_id){
		DB::update("torrents", array("finding"=>time()), array('data_id'=>$data_id));
		Status::updateStatus($data_id, 20);
		// First let's search for IMDB_ID (Great for sequels and such) -- if nothing found then do a title search (ex. Milo and Otis requires a title search) // Implement a skip for Episodes (like a type in torrents?)
		$first_search = true;
		$query = $data_id;
		$title = Data::getTitle($data_id);
		find_torrents:
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
			if ($contains = self::contains_avoided_keywords($tpb_torrent_name_cleaned, $title)){
				Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Title contains: ".$contains);
				continue;
			}
			if (!self::titles_match($tpb_torrent['title'], $title)){
				Debug::msg("[".$tpb_torrent_name_cleaned."] Avoided. Title doesn't match");
				continue;
			}
			$score = self::score_torrent($tpb_torrent);
			$viable_torrents[] = array_merge($tpb_torrent, array('score'=>$score,'actual_title'=>$title,'data_id'=>$data_id));
		}
		// Output The Viable Torrent titles
		$viable_titles = '';
		foreach($viable_torrents as $vt){$viable_titles.=$vt['title']."\n";}
		Debug::msg("[".$title."] Viable Results as follows:\n".trim($viable_titles,"\n"));
		// End Output
		usort($viable_torrents, function($a, $b) {
		    $rdiff = $b['score'] - $a['score'];
		    if ($rdiff) return $rdiff;
		    return $b['seeds'] - $a['seeds'];
		});
		$torrent_to_download = self::determine_best_torrent_from_files($viable_torrents);
		if (empty($viable_torrents) || empty($torrent_to_download)){
			Error::notice("[".$title."] No Torrents Found.");
			if ($first_search){
				// Now a hail Mary. Search TPB for the title instead of the imdb_id
				$query = $title;
				$first_search = false;
				Error::notice("[".$data_id."] Nothing using the DATA_ID (probably not a movie), now let's try searching using the title.");
				goto find_torrents;
			}
			DB::update("torrents", array('not_found'=>time()), array('data_id'=>$data_id));
			Status::updateStatus($data_id, 120);
			return;
		}
		Debug::msg("[".$torrent_to_download['title']."] A Torrent has been selected based on its score.");
		Debug::msg(\print_r($torrent_to_download,true));
		DB::update("torrents", array('torrent_link'=>$torrent_to_download['torrent_link'],'file_indexes'=>serialize($torrent_to_download['wanted_file_indexes'])), array('data_id'=>$data_id));
		Debug::msg("[".$torrent_to_download['title']."] has been added to the Downloads Table.");
		Debug::spacer("");
	}

}

?>