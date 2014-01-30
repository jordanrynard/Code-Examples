<?
/* 
Plugin name: Simple Twitter Feed
Description: Uses the Twitter v1.1 API to retrieve Tweets from a specific user and caches them in the WP DB. 
Version: 2.0.0
Author:Jordan Rynard
*/

/*
USE:

$twitterFeed = new jrTwitterFeed(array(
	'num'=>3, 
	'user'=>'Prof_S_Hawking',
	'date'=>"M n @ ga",
	'interval'=>1.5, // Number of hours to wait before updating local cache
	'tag'=>'li', // Default Tag, be sure to wrap with UL manually if using li
	'file_storage'=>false // Store in file instead of DB
));
$tweets = $twitterFeed->get_tweets();

*/

class jrTwitterFeed {

	// In the future have the settings setable in the dashboard.
	public $tweet_defaults = array(
		'num'=>3, 
		'user'=>'twitter',
		'date'=>"M n @ ga",
		'interval'=>1.5, // Number of hours to wait before updating cache
		'tag'=>'li',
		'file_storage'=>false
	);

	private $oauth_access_token = "2318795935-D7KRYNHCLxA1M3hWWH8vSX6ymrdlEElvELDR6oX";
	private $oauth_access_token_secret = "bxNDvdOSUKyhD9FF0RPZDEMwrIc59Oc56h7kwA5Zc2q53";
	private $consumer_key = "dOmnKHCeSArxZyuKI95xmw";
	private $consumer_secret = "KlFW2eJX6ni9GlYqSGNCUSbsKzTokGMEsYMUs48n0";

	public $args = array();

	function __construct($args=array()){
		$this->args=$args;	
	}

	function buildBaseString($baseURI, $method, $params) {
	    $r = array();
	    ksort($params);
	    foreach($params as $key=>$value){
	        $r[] = "$key=" . rawurlencode($value);
	    }
	    return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
	}

	function buildAuthorizationHeader($oauth) {
	    $r = 'Authorization: OAuth ';
	    $values = array();
	    foreach($oauth as $key=>$value)
	        $values[] = "$key=\"" . rawurlencode($value) . "\"";
	    $r .= implode(', ', $values);
	    return $r;
	}

	function retrieve_fresh_tweets($username, $count){
		$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";

		$oauth = array(
	        'screen_name' => $username,
	        'count' => $count,
	        'oauth_consumer_key' => $this->consumer_key,
	        'oauth_nonce' => time(),
	        'oauth_signature_method' => 'HMAC-SHA1',
	        'oauth_token' => $this->oauth_access_token,
	        'oauth_timestamp' => time(),
	        'oauth_version' => '1.0'
	    );

		$base_info = buildBaseString($url, 'GET', $oauth);
		$composite_key = rawurlencode($this->consumer_secret) . '&' . rawurlencode($this->oauth_access_token_secret);
		$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
		$oauth['oauth_signature'] = $oauth_signature;

		// Make Requests
		$header = array($this->buildAuthorizationHeader($oauth), 'Expect:');
		$options = array(
	            CURLOPT_HTTPHEADER => $header,
	            //CURLOPT_POSTFIELDS => $postfields,
	            CURLOPT_HEADER => false,
	            CURLOPT_URL => $url . '?screen_name='.$username.'&count='.$count, 
	            CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false
	            );

		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$json = curl_exec($feed);
		curl_close($feed);

		// $twitter_data = json_decode($json);
		return $json;
	}

	function get_tweets(){
		$args = $this->args + $this->tweet_defaults;
		if (empty($args['user'])){
			return "Please set a Twitter 'user'";
		}

		if ($args['file_storage']){
			$time = (int)file_get_contents('jr-twitter-feed_time.txt'); // get last update
		} else {
			$time = (int)get_option('jr-twitter-feed_time'); // get last update
		}
		if (time() - $time > ($args['interval']*3600)){
			$tweets_json = retrieve_fresh_tweets($args['user'], $args['num']); // get tweets and decode them into a variable
			$tweets = json_decode($tweets_json);	
			if ($args['file_storage']){
				file_put_contents('jr-twitter-feed_time.txt',time());
			} else {
				update_option('jr-twitter-feed_time',time());
			}
			if (!empty($tweets[0]->text) && empty($tweets->error)){
				if ($args['file_storage']){
					file_put_contents('jr-twitter-feed_json.txt',$tweets_json);
				} else {
					update_option('jr-twitter-feed_json',$tweets_json);
				}
			}
		}
		if ($args['file_storage']){
			$tweets = json_decode(file_get_contents('jr-twitter-feed_json.txt'));	
		} else {
			$tweets = json_decode(get_option('jr-twitter-feed_json'));	
		}

		$html='';
		if (is_array($tweets)):
			foreach ($tweets as $tweet):
				if (!empty($tweet->text)){
					$string = $tweet->text;
					// Parse links
					// http://saturnboy.com/2010/02/parsing-twitter-with-regexp/
					$string = preg_replace('@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@', '<a target="_blank" href="$1">$1</a>', $string);							// Parse usernames
					$string = preg_replace('/@(\w+)/', '<a target="_blank" href="http://twitter.com/$1">@$1</a>', $string);
					// Parse hashtags
					$string = preg_replace('/\s+#(\w+)/', ' <a target="_blank" href="http://search.twitter.com/search?q=%23$1">#$1</a>', $string);							
					// Date
					if ($args['date']){
						$string .= ' <span class="date">'.date($args['date'],strtotime($tweet->created_at))."</span>";
					}
					// Add the Tweet
					$html .= '<'.$args['tag'].'>';
					$html .= $string;
					$html .= '</'.$args['tag'].'>';
				}
			endforeach;
		endif;

		return $html;
	}

}