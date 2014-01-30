<?

namespace MediaCenter;

class ConvertVideo {
	// 320 x 240
	// 480 x 320
	// 720 x 480
	// http://rodrigopolo.com/ffmpeg/cheats.php (Contains iPhone and other device type examples!)
	// https://trac.ffmpeg.org/wiki/x264EncodingGuide (contains the order of -preset speeds)
	// -preset ultrafast (after libx264)
	// static $ffmpeg_mobile = "ffmpeg -y -i '%s' -s 480x320 -r 29.97 -b:v 400k -bt 500k -vcodec libx264 -preset medium -movflags faststart -acodec libfdk_aac -ac 2 -ar 44100 -b:a 128k -threads 0 '%s'";
	static $ffmpeg_mobile = "ffmpeg -y -i '%s' -s 320x240 -r 30000/1001 -b:v 200k -bt 240k -vcodec libx264 -vpre ipod320 -movflags faststart -acodec libfdk_aac -ac 2 -ar 44100 -b:a 128k -threads 0 '%s'";
	static $mobile_file_ext = '.mp4';

	static function mobile($data_id){
		$file = Files::getFile($data_id);
		if (!is_file($file['location'])){
			Error::fatal("Could not find the file for conversion: ".$file['location']);
		}
		$mobile_location = pathinfo($file['location'], PATHINFO_DIRNAME)."/".pathinfo($file['location'], PATHINFO_FILENAME)." mobile".self::$mobile_file_ext;
		$ffmpeg_mobile = sprintf(self::$ffmpeg_mobile, $file['location'], $mobile_location);
		Files::setMobileLocation($data_id, $mobile_location);
		/*
		// For Debugging: 
		$result = shell_exec($ffmpeg_mobile." 2>&1");
		var_dump($result);
		*/
		exec($ffmpeg_mobile." >> /dev/null &"); // This allows for asynchronous
		Debug::msg("The Mobile Conversion is underway for file: ".$mobile_location);
	}


	static function updateConvertingMobileFiles(){
		$files = Files::getUnconvertedMobileFiles();
		if (empty($files)){
			Error::quit("No currently converting Mobile Files Found");
		}
		foreach ($files as $file){
			$filesize = filesize($file['mobile_location']);
			if ($filesize != $file['mobile_current_filesize']){
				Files::updateMobileCurrentFilesize($file['data_id'], $filesize);
				Debug::msg("Mobile File Still Converting: ".basename($file['mobile_location'])." (".number_format($filesize)." bytes)  ".$file['mobile_location']);
			}
		}
	}


	static function processConvertedMobileFiles(){
		$files = Files::getUnconvertedMobileFiles();
		sleep(1); // Give it some time so we don't prematurely detect it
		if (empty($files)){
			Error::quit("No files with Unprocessed Mobile Conversions Found");
		}
		foreach ($files as $file){
			$filesize = filesize($file['mobile_location']);
			if ($filesize == $file['mobile_current_filesize']){
				$details = Data::getDetails($file['data_id']);
				Files::setMobileConversionComplete($file['data_id']);
				Debug::msg("Finished Converting Mobile File: ".$file['mobile_location']." (".number_format($filesize)." bytes)");
				Status::updateStatus($file['data_id'], 110);
				if (Data::isMovie($file['data_id'])){
					Notifications::sms($details['title'],"Mobile Ready.");
				} elseif (Data::isEpisode($file['data_id'])){
					Notifications::sms($details['title']." S".str_pad($details['season'],2,'0',STR_PAD_LEFT)." E".str_pad($details['episode'],2,'0',STR_PAD_LEFT),"Mobile Ready.");
				}
			}
		}
	}

}

?>