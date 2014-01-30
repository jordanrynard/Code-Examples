<?

class FrontEnd {
	
	public $view="tv";
	public $list="new";
	public $query="";
	public $listings=array(); // They all get stored in the same Var

	function __construct(){
		// URL Rewriting
		$params = explode("/",$_SERVER['REQUEST_URI']);
		$this->view = empty($params[1]) ? $this->view : $params[1];
		$this->list = empty($params[2]) ? $this->list :$params[2];
		$this->query = empty($_POST['query']) ? $this->query : $_POST['query'];
		// Live Debugging
		if (stristr($this->view,"debug")){
			include(DEBUG_DIR."/live_debug.php");
			return;
		}
		// Front End
		$this->setListings();
		if (is_file(ROOT_DIR."/view/".$this->view.".php")){
			include(ROOT_DIR."/view/header.php");
			if ($this->list == 'search'){
				include (ROOT_DIR.'/view/search.php');
			}
			include(ROOT_DIR."/view/".$this->view.".php");
			include(ROOT_DIR."/view/footer.php");
		} else {
			include(ROOT_DIR."/view/404.php");
		}
	}


/* ============================================================================================================================ */
	function setListings(){
		$listings = array();
		switch ($this->view){
			case 'tv':
				switch ($this->list){
					case 'mylist':
						$listings = \MediaCenter\Data::getShowsMylist();
						break;
					case 'popular':
						$listings = \MediaCenter\Data::getShowsTrending();
						break;
					case 'new':
						$listings = \MediaCenter\Data::getShowsNew();
						break;
					case 'search':
						$listings = \MediaCenter\Data::getShowsSearch($this->query);
						break;
				}
				break;
			case 'movies':
				switch ($this->list){
					case 'mylist':
						$listings = \MediaCenter\Data::getMoviesMylist();
						break;
					case 'popular':
						$listings = \MediaCenter\Data::getMoviesTrending();
						break;
					case 'new':
						$listings = \MediaCenter\Data::getMoviesNew();
						break;
					case 'search':
						$listings = \MediaCenter\Data::getMoviesSearch($this->query);
						break;
				}
				break;
			case 'episodes':
				break;
		}
		$this->listings = $listings;
	}

	function getShowMovieEpisode(){
		$show_movie_episode = array_shift($this->listings);
		if (empty($show_movie_episode)){
			return false;
		}
		$ShowMovieEpisode = new ShowMovieEpisode($show_movie_episode);
		return $ShowMovieEpisode;
	}

/* ============================================================================================================================ */


/* ============================================================================================================================ */

// Bools

	function isTV(){
		if ($this->view == "tv"){
			return true;
		}
		return false;
	}

	function isMovies(){
		if ($this->view == "movies"){
			return true;
		}
		return false;
	}

	function isEpisodes(){
		if ($this->view == "episodes"){
			return true;
		}
		return false;
	}

	function isMyList(){
		if ($this->list == "mylist"){
			return true;
		}
		return false;
	}

	function isPopular(){
		if ($this->list == "popular"){
			return true;
		}
		return false;
	}

	function isSearch(){
		if ($this->list == "search"){
			return true;
		}
		return false;
	}

	function isNew(){
		if ($this->list == "new"){
			return true;
		}
		return false;
	}

}

?>