<?

namespace MediaCenter;

class Notifications {
	static function sms($subject, $message){
		$sms = Settings::get("administrator_sms");
		if (empty($sms)){
			return;
		}
		$headers = 'From: Mediacenter';
		mail($sms,$subject,$message,$headers);
	}
}

?>