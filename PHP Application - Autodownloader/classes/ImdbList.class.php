<?

namespace MediaCenter; 

require_once('./libraries/simple_html_dom.php'); // http://simplehtmldom.sourceforge.net/manual.htm

class ImdbList {

	function __construct(){
	}

	static function get_results($url){
		// User Agent seems to be required by The Pirate Bay
		ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
		$html = file_get_html($url);
		if (empty($html)){
			Error::warning("Could not reach IMDB to retrieve this list ".$url);
			return;
		}
		return self::parse_results($html);
	}


	static function parse_results($html){
		$shows = array();
		$list = $html->find("div.list",0);
		if (empty($list)){
			Error::notice("Empty list page on IMDB.");
			return;
		}
		$list_items = $list->children();
		foreach ($list_items as $list_item){
			$a_link = $list_item->find("a",0);
			$link = $a_link->getAttribute("href");
			$_link = explode("/",$link);
			$shows[]['imdb_id'] = (string)$_link[2]; 
			$shows[]['tvdb_id'] = (string)$_link[2]; // It's actually IMDB_ID but we're passing it to a Trakt function that reads tvdb_id
		}
		return $shows;
	}


	static function get_ids($url){
		// http://www.imdb.com/list/y6ZxAmYH5VY/ (2013-2014 new shows)
		$shows = self::get_results($url);
		return $shows;
	}

	// Does this belong here? This is all kinda wonky
	static function update_shows_new(){
		$shows = self::get_ids('http://www.imdb.com/list/y6ZxAmYH5VY/');
		
		Debug::msg("Updating IMDB List shows New");
		DB::query('TRUNCATE TABLE `list_shows_new`'); 
		$_shows = Trakt::get_shows($shows);
		foreach ($_shows as $_show){
			DB::insert('list_shows_new', array(
				'tvdb_id'=>$_show['tvdb_id']
			));
		}
		Trakt::update_show_database($shows);
	}

}

?>