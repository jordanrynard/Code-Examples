<?

namespace MediaCenter;

class Data {

// Instead of joining with tables that may have changing names -- just retrieve data_movies here, torrents in Torrents, files in Files, and merge them here

// Get
	static function getMovies($extended=true){
		if ($extended){
			$movies = DB::select("data_movies");
		} else {
			$movies = DB::select("data_movies", array("imdb_id"));
		}
		return $movies;
	}

	static function getShows($extended=true){
		if ($extended){
			$movies = DB::select("data_shows");
		} else {
			$movies = DB::select("data_shows", array("tvdb_id"));
		}
		return $movies;
	}

	static function getTitle($data_id){
		$details = self::getDetails($data_id);
		return $details['title'];
	}

	static function getDetails($data_id){
		$results = DB::query("SELECT * FROM data_movies WHERE imdb_id=:data_id", array(":data_id"=>$data_id));
		if (empty($results))
			$results = DB::query("SELECT * from data_shows WHERE tvdb_id=:data_id", array(":data_id"=>$data_id));
		if (empty($results))
			$results = DB::query("SELECT *, data_episodes.tvdb_id AS tvdb_id, data_shows.title AS parent_title FROM data_episodes INNER JOIN data_shows ON data_episodes.parent_tvdb_id=data_shows.tvdb_id WHERE data_episodes.tvdb_id=:data_id;", array(":data_id"=>$data_id));
		if (empty($results)){
			Error::fatal("Could not find the Details for Item with ID: ".$data_id);
		}
		return $results[0];
	}


// Front End Gets
	// Figure out a way to use getFile and merge these queries?
  // Movies
	static function getMoviesMylist(){
		$results = DB::query("SELECT * FROM `torrents` INNER JOIN `data_movies` ON torrents.data_id=data_movies.imdb_id LEFT JOIN `files` ON files.data_id=data_movies.imdb_id LEFT JOIN ".DB_XBMC_NAME.".movieview AS movieview ON movieview.c09=data_movies.imdb_id ORDER BY torrents.selected DESC;");
		return $results;
	}
	static function getMoviesTrending(){
		$results = DB::query("SELECT * FROM `list_movies_trending` INNER JOIN `data_movies` ON list_movies_trending.imdb_id=data_movies.imdb_id LEFT JOIN `torrents` ON torrents.data_id=data_movies.imdb_id LEFT JOIN `files` ON files.data_id=data_movies.imdb_id LEFT JOIN ".DB_XBMC_NAME.".movieview AS movieview ON movieview.c09=data_movies.imdb_id;");
		return $results;
	}
	static function getMoviesNew(){
		$results = DB::query("SELECT *, list_movies_new.release_date as released FROM `list_movies_new` INNER JOIN `data_movies` ON list_movies_new.imdb_id=data_movies.imdb_id LEFT JOIN `torrents` ON torrents.data_id=data_movies.imdb_id LEFT JOIN `files` ON files.data_id=data_movies.imdb_id LEFT JOIN ".DB_XBMC_NAME.".movieview AS movieview ON movieview.c09=data_movies.imdb_id ORDER BY list_movies_new.release_date DESC;");
		return $results;
	}
	static function getMoviesSearch($query){
		if (empty($query)){
			return array();
		}
		Trakt::update_search_movies($query); // First we need to update the DB with the movies we just searched for
		$results = DB::query("SELECT * FROM `list_movies_search` INNER JOIN `data_movies` ON list_movies_search.imdb_id=data_movies.imdb_id LEFT JOIN `torrents` ON torrents.data_id=data_movies.imdb_id LEFT JOIN `files` ON files.data_id=data_movies.imdb_id LEFT JOIN ".DB_XBMC_NAME.".movieview AS movieview ON movieview.c09=data_movies.imdb_id ORDER BY data_movies.released DESC;");
		return $results;
	}
	// Used to get Movie ID for Player.Open purposes
	static function getMovieFromXBMC($imdb_id=0){
		$results = DB::query("SELECT idMovie FROM `movieview` WHERE c09=:imdb_id;", array('imdb_id'=>$imdb_id), true);
		return $results[0];
	}

  // TV
	static function getShowsMylist(){
		$results = DB::query("SELECT * FROM `auto` INNER JOIN `data_shows` ON auto.data_id=data_shows.tvdb_id;");
		return $results;
	}
	static function getShowsTrending(){
		$results = DB::query("SELECT * FROM `list_shows_trending` INNER JOIN `data_shows` ON list_shows_trending.tvdb_id=data_shows.tvdb_id LEFT JOIN `auto` ON auto.data_id=data_shows.tvdb_id;");
		return $results;
	}
	static function getShowsNew(){
		$results = DB::query("SELECT * FROM `list_shows_new` INNER JOIN `data_shows` ON list_shows_new.tvdb_id=data_shows.tvdb_id LEFT JOIN `auto` ON auto.data_id=data_shows.tvdb_id;");
		return $results;
	}
	static function getShowsSearch($query){
		if (empty($query)){
			return array();
		}
		Trakt::update_search_shows($query); // First we need to update the DB with the movies we just searched for
		$results = DB::query("SELECT * FROM `list_shows_search` INNER JOIN `data_shows` ON list_shows_search.tvdb_id=data_shows.tvdb_id LEFT JOIN `auto` ON auto.data_id=data_shows.tvdb_id;");
		return $results;
	}
	// A Sub Query to get all the Episodes for a Show
	static function getShowsEpisodes($data_id){
		if (empty($data_id)){
			return array();
		}
		$results = DB::query("SELECT * FROM `data_episodes` LEFT JOIN `torrents` ON torrents.data_id=data_episodes.tvdb_id LEFT JOIN `files` ON data_episodes.tvdb_id=files.data_id LEFT JOIN ".DB_XBMC_NAME.".episodeview AS episodeview ON episodeview.c20=data_episodes.tvdb_id WHERE data_episodes.parent_tvdb_id=:data_id;", array(':data_id'=>$data_id));
		return $results;
	}
	// Used to get Episode ID for Player.Open purposes
	static function getEpisodeFromXBMC($tvdb_id=0){
		$results = DB::query("SELECT idEpisode FROM `episodeview` WHERE c20=:tvdb_id;", array('tvdb_id'=>$tvdb_id), true);
		return $results[0];
	}



// Check
	static function needsUpdate($data_id, $last_updated){
		$result = DB::query("SELECT title FROM data_movies WHERE imdb_id=:data_id AND last_updated=:last_updated   UNION SELECT title FROM data_shows WHERE tvdb_id=:data_id AND last_updated=:last_updated;", array(":data_id"=>$data_id,":last_updated"=>$last_updated));		
		if (empty($result)){
			return true;
		}
		return false;
	}

	static function isMovie($data_id){
		if (strpos($data_id,"tt")===0){
			return true;
		}
		return false;
	}

	static function isEpisode($data_id){
		if (strpos($data_id,"tt")===false){
			return true;
		}
		return false;
	}

// Update
	static function updateMovies($movies){
		foreach ($movies as $movie){
			$_movie = array();
			$_movie['imdb_id'] = (string)$movie['imdb_id'];
			$_movie['title'] = (string)$movie['title'];
			$_movie['year'] = (string)$movie['year'];
			$_movie['released'] = (string)$movie['released'];
			$_movie['trailer'] = (string)$movie['trailer'];
			$_movie['runtime'] = (string)$movie['runtime'];
			$_movie['tagline'] = (string)$movie['tagline'];
			$_movie['overview'] = (string)$movie['overview'];
			$_movie['certification'] = (string)$movie['certification'];
			$_movie['thumbnail'] = (string)$movie['images']['poster'];
			$_movie['genres'] = (string)implode(", ", $movie['genres']);
			$_movie['last_updated'] = (string)$movie['last_updated'];
			DB::query("REPLACE INTO data_movies SET imdb_id=:imdb_id, title=:title, year=:year, released=:released, trailer=:trailer, runtime=:runtime, tagline=:tagline, overview=:overview, certification=:certification, thumbnail=:thumbnail, genres=:genres, last_updated=:last_updated", $_movie);
			Debug::msg("[".$_movie['imdb_id']."] ".$_movie['title']." has been updated [no_echo]");
		}
	}

	static function updateShows($shows){
		foreach ($shows as $show){
			$_show = array();
			$_show['tvdb_id'] = (string)$show['tvdb_id'];
			$_show['title'] = (string)$show['title'];
			$_show['year'] = (string)$show['year'];
			// $_show['first_aired'] = (string)$show['first_aired_iso']; // Only in detailed Summary
			$_show['first_aired'] = (string)$show['first_aired']-10800; // This looks like the right time difference?
			$_show['country'] = (string)$show['country'];
			$_show['overview'] = (string)$show['overview'];
			$_show['runtime'] = (string)$show['runtime'];
			$_show['status'] = @(string)$show['status']; // Only in detailed summary (DAMNIT!)
			$_show['network'] = (string)$show['network'];
			$_show['air_day'] = (string)$show['air_day'];
			$_show['air_time'] = (string)$show['air_time'];
			$_show['thumbnail'] = (string)$show['images']['poster'];
			$_show['genres'] = (string)implode(", ", $show['genres']);
			$_show['last_updated'] = (string)$show['last_updated'];
			DB::query("REPLACE INTO data_shows SET tvdb_id=:tvdb_id, title=:title, year=:year, first_aired=:first_aired, country=:country, overview=:overview, runtime=:runtime, status=:status, network=:network, air_day=:air_day, air_time=:air_time, thumbnail=:thumbnail, genres=:genres, last_updated=:last_updated", $_show);
			Debug::msg("[".$show['tvdb_id']."] ".$show['title']." has been updated [no_echo]");
		}
	}

	static function updateEpisodes($episodes, $parent_tvdb_id){
		$values_string = "";
		foreach ($episodes as $episode){
			$_episode = array();
			$_episode['parent_tvdb_id'] = (string)$parent_tvdb_id;
			$_episode['tvdb_id'] = (string)$episode['tvdb_id'];
			$_episode['title'] = (string)str_replace("'","",$episode['title']);
			$_episode['season'] = (int)$episode['season'];
			$_episode['episode'] = (int)$episode['episode'];
			$_episode['first_aired'] = (string)$episode['first_aired']-10800; // This looks like the right time difference?
			$_episode['overview'] = (string)str_replace("'","",$episode['overview']);
			$_episode['thumbnail'] = (string)$episode['images']['screen'];
			$values_string .= "('".$_episode['parent_tvdb_id']."','".$_episode['tvdb_id']."','".$_episode['title']."','".$_episode['season']."','".$_episode['episode']."','".$_episode['first_aired']."','".$_episode['overview']."','".$_episode['thumbnail']."'),";
		}
		// It was wayyyy too slow inserting one episode at a time, so we had to do this
		Debug::msg("[".$parent_tvdb_id."] Episodes have been updated [no_echo]");
		DB::query("REPLACE INTO data_episodes (parent_tvdb_id, tvdb_id, title, season, episode, first_aired, overview, thumbnail) VALUES ".trim($values_string,","));
	}

// Set
	static function markWatchedUnwatched($data_id=0, $data=array()){
		$value = (int)$data['watched'];
		if (self::isEpisode($data_id)){
			DB::query("UPDATE episodeview SET playCount=:value WHERE c20=:data_id", array('value'=>$value, 'data_id'=>$data_id), true);
		} elseif (self::isMovie($data_id)){
			DB::query("UPDATE movieview SET playCount=:value WHERE c20=:data_id", array('value'=>$value, 'data_id'=>$data_id), true);
		}
		/*
		Debug::msg(\print_r($data, true));
		Debug::msg(\print_r($data_id, true));
		Debug::msg(\print_r($value, true));
		*/
		return true;
	}


}

?>