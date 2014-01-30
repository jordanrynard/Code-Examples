<?

namespace MediaCenter;

class RottenTomatoes {
	static $dvd_new_url = "http://api.rottentomatoes.com/api/public/v1.0/lists/dvds/new_releases.json";
	static $movie_url = "http://api.rottentomatoes.com/api/public/v1.0/movies.json";
	static $movie_lookup_by_imdb_url = "http://api.rottentomatoes.com/api/public/v1.0/movie_alias.json";

	function __construct(){
	}

	static function get_apikey(){
		$apikey = Settings::get('rotten_tomatoes_api_key');
		if (empty($apikey)){
			Error::fatal('Rotten Tomatoes API Key is not set.');
		}
		return $apikey;
	}

	static function getDVDNewReleases(){
		$url = self::$dvd_new_url."?apikey=".self::get_apikey()."&page_limit=50"; // 50 is the max page limit
		$json_results = @file_get_contents($url);
		if (empty($json_results)){
			Error::fatal("Problem contacting the Rotten Tomatoes API");
		}
		$result = json_decode($json_results, true);
		$movies = array();
		foreach ($result['movies'] as $movie){
			if (empty($movie['alternate_ids']['imdb'])) continue;
			$movies[] = array(
				'imdb_id' => "tt".$movie['alternate_ids']['imdb'],
				'release_date' => strtotime($movie['release_dates']['dvd'])
			);
		}
		return $movies;
	}

	static function UpdateDVDNewReleases(){
		Debug::msg("Updating New DVD Releases from Rotten Tomatoes");
		$movies = self::getDVDNewReleases(); // Do this before truncating the table in case the API fails
		DB::query('TRUNCATE TABLE `list_movies_new`');
		foreach ($movies as $movie){
			DB::insert('list_movies_new', array('imdb_id'=>$movie['imdb_id'],'release_date'=>$movie['release_date']));
		}
		Trakt::update_movie_database($movies);
	}

	static function getCriticsScoreURL($imdb_id){
		$imdb_id = trim($imdb_id,"tt"); // Rotten tomatoes stores the ID as an integer...
		// $url = self::$movie_url."?apikey=".self::get_apikey()."&q=".urlencode($title)."&page_limit=1"; // This was providing inaccurate results - ie. The World's End
		$url = self::$movie_lookup_by_imdb_url."?apikey=".self::get_apikey()."&type=imdb&id=".$imdb_id; 
		return $url;
	}

}

?>