<?

namespace MediaCenter;

class Trakt {

	static $movies_url = "http://api.trakt.tv/movie/summaries.json";
	static $movies_trending_url = "http://api.trakt.tv/movies/trending.json";
	static $movies_search_url = "http://api.trakt.tv/search/movies.json";

	static $shows_url = "http://api.trakt.tv/show/summaries.json";
	static $shows_trending_url = "http://api.trakt.tv/shows/trending.json";
	static $shows_search_url = "http://api.trakt.tv/search/shows.json";

	static $episodes_url = "http://api.trakt.tv/show/summary.json";

	static function get_apikey(){
		$apikey = Settings::get('trakt_api_key');
		if (empty($apikey)){
			Error::fatal('Trakt API Key is not set.');
		}
		return $apikey;
	}

	static function trakt_api_request($url, $params="", $extended=false){
		if ($extended) $params .= "/full/";
	    $api_url = $url."/".self::get_apikey()."/".$params;
	    $json_result = file_get_contents($api_url);
	    if (empty($json_result)){
	    	Error::fatal("Problem connecting to Trakt.tv API.");
	    }
	    $result = json_decode($json_result, true);
	    return $result;
	}

	static function ids_to_csv($items){
		$csv_values = "";  
		foreach($items as $item){ 
			if (empty($item['tvdb_id']) && !empty($item['imdb_id'])){
				$csv_values .= $item['imdb_id'].","; 
			} elseif (!empty($item['tvdb_id'])){
				$csv_values .= $item['tvdb_id'].","; 
			}
		}
		$csv_values = trim($csv_values, ",");
		return $csv_values;
	}

/* =============================================================================================================== */

// Get
	static function get_movies($movies, $extended=false){		
		$movies_array = array_chunk($movies, 50); // There's a limit request it looks like so lowered it from 100 to 50  // We don't want our GET string to be too long (Max 4000 bytes) // The max size of a UTF-8 char is 4 byes, so let's cap this at 1000. There are 9 chars in an imdb_id (let's estimate for 10)			
		$all_movies = array();
		foreach ($movies_array as $_movies){
			$results = self::trakt_api_request(self::$movies_url, self::ids_to_csv($_movies), $extended);
			$all_movies = array_merge($all_movies, $results);
		}
		return $all_movies;
	}

	static function get_movies_trending(){
		$results = self::trakt_api_request(self::$movies_trending_url);
		$movies = array();
		foreach ($results as $movie){
			if (empty($movie['imdb_id']) || empty($movie['title'])) continue; // Some of these don't have an imdb_id, so let's toss 'em
			if ($movie['watchers'] <= 1) continue; // There're a lot of stupid movies that just 1 person is watching
			$movies[] = $movie;
		}
		return $movies;
	}

	static function get_shows($shows, $extended=false){
		$shows_array = array_chunk($shows, 50); // There's a limit request it looks like so lowered it from 100 to 50  // We don't want our GET string to be too long (Max 4000 bytes) // The max size of a UTF-8 char is 4 byes, so let's cap this at 1000. There are 9 chars in an imdb_id (let's estimate for 10)			
		$all_shows = array();
		foreach ($shows_array as $_shows){
			$results = self::trakt_api_request(self::$shows_url, self::ids_to_csv($_shows), $extended);
			$all_shows = array_merge($all_shows, $results);
		}
		return $all_shows;
	}

	static function get_shows_trending(){
		$results = self::trakt_api_request(self::$shows_trending_url);
		$shows = array();
		// Some of these don't have an imdb_id, so let's toss 'em
		foreach ($results as $show){
			if (empty($show['tvdb_id']) || empty($show['title'])) continue;
			if ($show['watchers'] <= 5) continue; // There're a lot of stupid shows that just 1 person is watching
			$shows[] = $show;
		}
		return $shows;
	}

	static function get_episodes($tvdb_id, $extended=true){
		$result = self::trakt_api_request(self::$episodes_url, $tvdb_id, $extended);
		$episodes = array();
		foreach ($result['seasons'] as $season){
			foreach ($season['episodes'] as $episode){
				if ($episode['season'] < 1) continue; // Don't want the Season 0 crap
				if ($episode['first_aired'] < 1) continue; // Don't want anything that doesn't have an air date (undecided future eps)
				$episodes[] = $episode;
			}
		}
		return $episodes;
	}

// Update
	static function update_movie_database($movies=array()){
		$all_or_some = empty($movies) ? "All" : "Some";
		Debug::msg("Updating $all_or_some Movies in Database. [no_echo]");
		if (empty($movies)){ // All
			$movies = Data::getMovies($extended=false);
		}
		$movies__now_with_latest_trakt_data = self::get_movies($movies);
		$movies_that_need_updating = array();
		foreach ($movies__now_with_latest_trakt_data as $movie){
			if (Data::needsUpdate($movie['imdb_id'],$movie['last_updated'])){
				Debug::msg("[".$movie['imdb_id']."] ".$movie['title']." needs updating [no_echo]");
				$movies_that_need_updating[] = $movie;
			}
		}
		if (empty($movies_that_need_updating)){
			Debug::msg("No movies that need updating. [no_echo]");
			return;
		}
		$movies_that_need_updating__now_with_latest_extended_trakt_data = self::get_movies($movies_that_need_updating, $extended=true);
		Data::updateMovies($movies_that_need_updating__now_with_latest_extended_trakt_data);
	}

	static function update_movies_trending(){
		Debug::msg("Updating Trakt Movies Trending");
		$movies = self::get_movies_trending(); // Do this before truncating the table in case the API fails
		DB::query('TRUNCATE TABLE `list_movies_trending`'); 
		foreach ($movies as $movie){
			DB::insert('list_movies_trending', array('imdb_id'=>$movie['imdb_id']));
		}
		Trakt::update_movie_database($movies);
	}

	static function update_search_movies($query){
		Debug::msg("Searching for Movie: ".$query."[no_echo]");
		$search_result_movies = self::trakt_api_request(self::$movies_search_url, urlencode(clean_string($query)));
		// We store the search results in a table for easy/quick access and joins
		Debug::msg("Putting the results in the list_movies_search table [no_echo]");
		DB::query('TRUNCATE TABLE `list_movies_search`'); 
		foreach ($search_result_movies as $movie){
			Debug::msg("Search Result: ".$movie['title']."[no_echo]");
			DB::insert('list_movies_search', array('imdb_id'=>$movie['imdb_id']));
		}
		Debug::msg("Updating the Movies DB with the Search Results from: ".$query."[no_echo]");
		Trakt::update_movie_database($search_result_movies);
	} 

	static function update_show_database($shows=array()){
		$all_or_some = empty($shows) ? "All" : "Some";
		Debug::msg("Updating $all_or_some Shows in Database. [no_echo]");
		if (empty($shows)){ // All
			$shows = Data::getShows($extended=false);
		}
		$shows__now_with_latest_trakt_data = self::get_shows($shows);
		$shows_that_need_updating = array();
		foreach ($shows__now_with_latest_trakt_data as $show){
			if (Data::needsUpdate($show['tvdb_id'],$show['last_updated'])){
				Debug::msg("[".$show['tvdb_id']."] ".$show['title']." needs updating [no_echo]");
				$shows_that_need_updating[] = $show;
			}
		}
		if (empty($shows_that_need_updating)){
			Debug::msg("No shows that need updating. [no_echo]");
			return;
		}
		$shows_that_need_updating__now_with_latest_extended_trakt_data = self::get_shows($shows_that_need_updating, $extended=true);
		Data::updateShows($shows_that_need_updating__now_with_latest_extended_trakt_data);
		self::update_episodes_for_shows_that_needed_updating($shows_that_need_updating__now_with_latest_extended_trakt_data);
	}

	static function update_shows_trending(){
		Debug::msg("Updating Trakt shows Trending");
		$shows = self::get_shows_trending(); // Do this before truncating the table in case the API fails
		DB::query('TRUNCATE TABLE `list_shows_trending`'); 
		foreach ($shows as $show){
			DB::insert('list_shows_trending', array('tvdb_id'=>$show['tvdb_id']));
		}
		Trakt::update_show_database($shows);
	}

	static function update_search_shows($query){
		Debug::msg("Searching for Show: ".$query."[no_echo]");
		$search_result_shows = self::trakt_api_request(self::$shows_search_url, urlencode(clean_string($query)));
		// We store the search results in a table for easy/quick access and joins
		Debug::msg("Putting the results in the list_shows_search table [no_echo]");
		DB::query('TRUNCATE TABLE `list_shows_search`'); 
		foreach ($search_result_shows as $show){
			if (empty($show['tvdb_id'])) continue;
			Debug::msg("Search Result: ".$show['title']."[no_echo]");
			DB::insert('list_shows_search', array('tvdb_id'=>$show['tvdb_id']));
		}
		Debug::msg("Updating the Shows DB with the Search Results from: ".$query."[no_echo]");
		Trakt::update_show_database($search_result_shows);
	} 

	static function update_episodes_for_shows_that_needed_updating($shows){
		foreach ($shows as $show){
			$episodes = self::get_episodes($show['tvdb_id'], $extended=true);
			Data::updateEpisodes($episodes, $show['tvdb_id']);
		}
	}



}

?>