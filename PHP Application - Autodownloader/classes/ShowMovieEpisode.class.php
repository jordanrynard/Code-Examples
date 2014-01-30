<?

class ShowMovieEpisode {

	public $details = array();
	public $download_status = array();

	function __construct($show_movie_episode){
		$this->details = $show_movie_episode;
		$this->download_status = \MediaCenter\Status::getStatus($this->details);
	}

	function get_thumbnail(){
		$image_url = $this->details['thumbnail'];
		$image_path_info = pathinfo($image_url);
		// Also the show might not have an image, so we should maybe check for that?
		$image_url = $image_path_info['dirname']."/".$image_path_info['filename']."-138.".$image_path_info['extension']; 
		return $image_url;
	}

	function get_thumbnail_episode(){
		$image_url = $this->details['thumbnail'];
		$image_path_info = pathinfo($image_url);
		// Also the show might not have an image, so we should maybe check for that?
		$image_url = $image_path_info['dirname']."/".$image_path_info['filename']."-218.".$image_path_info['extension']; 
		return $image_url;
	}

	function hasBeenWatched(){
		if (!empty($this->details['playCount']) || !empty($this->details['resumeTimeInSeconds'])){
			return true;
		}
		return false;
	}

	function getRottenTomatoesScoreURL(){
		$score = \MediaCenter\RottenTomatoes::getCriticsScoreURL($this->details['imdb_id']);
		return $score;
	}

	function getAutoShowsSetting(){
		if ($this->details['anything_after'] == '0'){
			return 'all';
		}
		if ($this->details['anything_after'] > 0){
			return 'new';
		}
		return '';
	}

	function setSeasons(){
		$episodes = \MediaCenter\Data::getShowsEpisodes($this->details['tvdb_id']);
		$seasons = array();
		foreach ($episodes as $episode){
			$s = $episode['season'];
			$e = $episode['episode'];
			if (empty($seasons[$s])){
				$seasons[$s] = array(
					'season'=>$s,
					'episodes'=>array()
				);
			}
			$seasons[$s]['episodes'][$e] = $episode;
		}
		usort($seasons, function($a, $b) {
		    return $a['season'] - $b['season'];
		});
		$this->seasons = $seasons;
	}

	function getSeason(){
		$season = array_shift($this->seasons);
		$this->episodes = $season['episodes'];	
		if (empty($season)){
			return false;
		}
		return $season;
	}

	function getEpisode(){
		$episode = array_shift($this->episodes);	
		if (empty($episode)){
			return false;
		}
		$episode = new ShowMovieEpisode($episode);
		return $episode;
	}

	function isCaughtUp(){
		foreach ($this->seasons as $season){
			foreach ($season['episodes'] as $episode){
				if (!empty($episode['playCount']) || !empty($episode['resumeTimeInSeconds'])){ // Find a way to use the Class method instead?
					continue;
				}
				// Check if there's something to be watched
				if ($episode['first_aired'] > $this->details['anything_after'] + TV_SHOW_WAIT_TIME_BEFORE_DOWNLOADING){
					// Now check if it has aired yet
					if ($episode['first_aired'] + TV_SHOW_WAIT_TIME_BEFORE_DOWNLOADING <= time()){
						/*
						echo $episode['first_aired'] + TV_SHOW_WAIT_TIME_BEFORE_DOWNLOADING;
						echo " : ";
						echo time();
						*/
						return false;
					}
				}
			}
		}
		return true;
	}

}

?>