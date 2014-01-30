<?

namespace MediaCenter;

class Status {

	static $status_list = array(
		// The order here matters becasue we check starting with the first one to see if it is set.
		130 => array(
				'column'=>'not_found_gave_up',
				'text'=>'Not Found'
			),
		120 => array(
				'column'=>'not_found',
				'text'=>'Timeout'
			),
		110 => array(
				'column'=>'mobile_conversion_complete',
				'text'=>'Play'
			),
		100 => array(
				'column'=>'downloaded',
				'text'=>'Play on TV' // In Library
			),
		40 => array(
				'column'=>'downloading',
				'text'=>'%s%% Done'
			),
		30 => array(
				'column'=>'queued',
				'text'=>'Queued'
			),
		20 => array(
				'column'=>'finding',
				'text'=>'Finding'
			),
		10 => array(
				'column'=>'selected',
				'text'=>'Download'
			),
		0 => array(
				'column'=>'download',
				'text'=>'Download'
			)
	);

	static function statusCodeToStatus($status_code, $string){
		$status = @self::$status_list[$status_code];
		if (empty($status)){
			Error::fatal("Could not find a Status with Status Code: ".$status_code);
		}
		if (!empty($string)){
			$status['text'] = sprintf($status['text'], $string);
		}
		return $status;
	}

	static function updateStatus($data_id, $status_code, $string=''){
		$status = self::statusCodeToStatus($status_code, $string);
		SocketIO::emit("update_download_status", array('data_id'=>$data_id,'column'=>$status['column'],'text'=>$status['text']));
	}

	static function getStatus($item){
		$default_status = end(self::$status_list);		
		foreach (self::$status_list as $status){
			$column = $status['column'];
			$text = $status['text'];			
			if (!empty($item[$column])){ // If no matches, we have need to set the last one, which is download
				if ($column=='downloading') {
					$status['text'] = sprintf($status['text'], $item['percent_done']);
				}
				return $status;
			}
		}
		return $default_status;
	}

}

?>