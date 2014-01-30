<?

namespace MediaCenter;

require_once(ROOT_DIR.'/libraries/simple_html_dom.php'); // http://simplehtmldom.sourceforge.net/manual.htm

class ThePirateBayParser {
	
// 	public $url = "http://thepiratebay.org/search/";
//	public $url_mirror = "http://pirateproxy.se/search/";
 	public $domain = "thepiratebay.sx";
	public $domain_mirror = "thepiratebay.org";
	public $query = '';
	public $torrents = array();
	public $error = false;

	function __construct($query){
		$this->query = $query;

		$url = "http://".$this->domain."/search/";
		$url_suffix = "/0/7/200"; // sorts by seeds & shows only videos

		$query_string = urlencode($query);

		$query_url = $url.$query_string.$url_suffix;
		$this->get_results($query_url);
	}

	function get_results($query_url){
		// User Agent seems to be required by The Pirate Bay
		ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
		$html = file_get_html($query_url);
		if (empty($html)){
			Error::warning("Could not reach The Pirate Bay to retrieve search results. ".$query_url);
			if ($this->domain != $this->domain_mirror){
				$this->domain = $this->domain_mirror;
				$this->__construct($this->query);
			} else {
				$this->error = true;
				return;
			}
		}
		$this->parse_results($html);
	}

	function parse_results($html){
		$torrents = array();
		if (!is_object($html)) {
			Error::notice("Problem with the HTML object created from the Torrent File.");
			return;
		}
		$table = $html->find("table[id=searchResult]",0);
		if (empty($table)){
			Error::notice("Empty results page on TPB.");
			return;
		}
		$table_rows = $table->children();
		foreach ($table_rows as $table_row){
			if ($table_row->tag == 'tr'){
				if (!is_object($a_link = $table_row->children(1))) {
					Error::notice("Problem further in the HTML object created from the Torrent File.");
					return;
				}
				$a_link = $table_row->children(1)->find("a",0);
				$a_torrent = $table_row->children(1)->find("a",1);

				// Get Details
				$link = $a_link->getAttribute("href");
				$title = $a_link->innertext;
				$magnet_link = $a_torrent->getAttribute("href");
				$seeds = $table_row->children(2)->innertext;
				$torrent_link = "http://torrents.".$this->domain.str_ireplace("/torrent","",$link).".torrent"; // Or: .TPB.torrent
				/*
				// Get Files
				// Important!! Some file baskets are coming back empty...
				$guid = end(explode("/",dirname($link)));
				$files_html = file_get_html("http://thepiratebay.sx/ajax_details_filelist.php?id=".$guid);
				$files = array();
				foreach ($files_html->find("table",0)->children() as $file){
					$files[] = $file->children(0)->innertext;
				}
				*/
				// $meta_info = new MagnetLinkParser($magnet_link);
				$meta_info = new MagnetLinkParser(false, $torrent_link);
				if (empty($meta_info->files)){
					Error::notice("Magnet Link Parser failed for ".$title." (".$link.")");
					continue;
				}

				// echo $link."<br/>".$title."<br/>".$magnet_link."<br/>".$seeds."<br/>".print_r($files,true);
				$torrents[] = array(
					'link'=>"http://thepiratebay.sx".$link,
					'title'=>$title,
					'magnet_link'=>$magnet_link,
					'seeds'=>$seeds,
					// 'guid'=>$guid,
					'torrent_link'=>$meta_info->torrent_link,
					'files'=>$meta_info->files
				);
			}
		}
		$this->torrents = $torrents;
	}
}
?>