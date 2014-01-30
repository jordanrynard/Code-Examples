<?

namespace MediaCenter;

class Files {

// Get
	static function getFile($data_id){
		$results = DB::select('files',array(),array('data_id'=>$data_id));
		if (empty($results)){
			Error::fatal("Could not find the File with the data_id: ".$data_id);
		}
		$file = $results[0];

		// Need to append the URLs to the files
		$mobile_location_uri = str_ireplace(ROOT_DIR,"",$file['mobile_location']);
		$mobile_location_url = URL.$mobile_location_uri;		
		$location_uri = str_ireplace(ROOT_DIR,"",$file['location']);
		$location_url = URL.$location_uri;
		$additional_details = array(
			'mobile_location_url'=>$mobile_location_url,
			'location_url'=>$location_url
		);

/*		// Deal with play_count, and resume_time (this is just temporary)
		if ($file['playCount'] > $file['play_count']){
			$file['play_count'] = $file['playCount'];
		}
		if ($file['resumeTimeInSeconds'] > $file['resume_time']){
			$file['resume_count'] = $file['resumeTimeInSeconds'];
		}
*/
		$file = array_merge($file, $additional_details);

		return $file;
	}

	static function getUnconvertedMobileFiles(){
		$results = DB::query("SELECT * FROM `files` WHERE mobile_conversion_complete=0;");
		if (empty($results)){
			Error::quit("No Unconverted Mobile Files Found");
		}
		return $results;
	}

// Do
	static function processDownloadedTorrentFiles($torrent){
		$torrent_dir = Transmission::getTorrentDownloadDirectory($torrent['title']);
		$torrent_details = Transmission::getTorrentDetails($torrent['torrent_hash']);
		if (empty($torrent_details)){
			Error::warning("The Torrent details were empty. Something has gone horribly wrong. [".$torrent['title']."]");
			return false;
		}
		$filename = self::makeFileName($torrent['data_id']);
		$foldername = self::makeFolderName($torrent['data_id']);
		$filepath = self::getFilePath($torrent['data_id'], $foldername);
		mkdir($filepath, 0777, true);
		$file_indexes = unserialize($torrent['file_indexes']);
		$i=2;
		foreach ($file_indexes as $index){ // Currently the system only supports One file per DATA_ID
			$torrent_filename = $torrent_details['files'][$index]['name'];
			$torrent_file_location = $torrent_dir."/".$torrent_filename;
			$new_filename = $filename.".".pathinfo($torrent_filename,PATHINFO_EXTENSION);
			$new_file_location = $filepath."/".$new_filename;
			// File Checks
			if (!is_file($torrent_file_location) && is_file($new_file_location)){ // Are we trying to execute this script again cause it failed the first time somewhere?
				Debug::msg("The Torrent File doesn't exist, but the New File does -- it has been moved already. Skipping the move process.");
				goto file_has_been_moved;
			} elseif (!is_file($torrent_file_location)){
				Debug::msg("The Torrent File doesn't exist: ".$torrent_file_location);
				return false;
			}
			// For multiple files (not used at present)
			if (is_file($new_file_location)){
				$new_file_location = pathinfo($new_file_location,PATHINFO_DIRNAME)."/".pathinfo($new_file_location,PATHINFO_FILENAME)."_".$i.".".pathinfo($new_file_location,PATHINFO_EXTENSION);
				$i++;
			}
			// Move the file to the Media folder
			Debug::msg("Moving File: '".$torrent_file_location."' to '".$new_file_location."'");
			$result = rename($torrent_file_location, $new_file_location);
			if (!$result){
				Error::warning("There was a problem moving that file.");
				return false;
			}
			file_has_been_moved:
			DB::insert("files",array("data_id"=>$torrent['data_id'], 'location'=>$new_file_location));
			ConvertVideo::mobile($torrent['data_id']); // Convert the file (for mobile playback)
		}	
		Files::generateNfoFile($torrent['data_id'], $filepath."/".$filename.".nfo"); // Create .nfo file (so we scrape the right movie)	
		$result = rm_folder_recursively($torrent_dir); // Remove all the download files
		// Update XBMC library
		if (!$result){
			Error::warning("There was a problem removing the directory: ".$torrent_dir);
			return false;
		}
		return true;
	}

	static function makeFileName($data_id){
		$details = Data::getDetails($data_id);
		if (Data::isMovie($data_id)){
			return clean_string($details['title'])." (".$details['year'].") [".$details['imdb_id']."]";
		} elseif (Data::isEpisode($data_id)){
			return clean_string($details['parent_title'])." S".str_pad($details['season'],2,"0",STR_PAD_LEFT)."E".str_pad($details['episode'],2,"0",STR_PAD_LEFT)." [".$details['tvdb_id']."]";
		}
		Error::fatal("A Filename is being made for something that we don't know what it is");
	}

	static function makeFolderName($data_id){
		$details = Data::getDetails($data_id);
		if (Data::isMovie($data_id)){
			return clean_string($details['title'])." (".$details['year'].")";
		} elseif (Data::isEpisode($data_id)){
			return clean_string($details['parent_title'])."/Season ".str_pad($details['season'],2,"0",STR_PAD_LEFT);
		}
		Error::fatal("A Foldername is being made for something that we don't know what it is");
	}

	static function getFilePath($data_id, $foldername){
		if (Data::isMovie($data_id)){
			return MOVIES_DIR."/".$foldername;
		} elseif (Data::isEpisode($data_id)){
			return TV_DIR."/".$foldername;
		}
		Error::fatal("A Filepath is being requested for something that we don't know what it is");
	}

	static function generateNfoFile($data_id, $filename){
		if (Data::isMovie($data_id)){
			file_put_contents($filename, "http://imdb.com/title/".$data_id);
		} elseif (Data::isEpisode($data_id)){
			file_put_contents($filename, "http://thetvdb.com/?tab=episode&id=".$data_id);
		}
	}

// Set
	static function setMobileConversionComplete($data_id){
		DB::update("files", array("mobile_conversion_complete"=>1), array("data_id"=>$data_id));
	}

	static function setMobileLocation($data_id, $mobile_location){
		DB::update('files', array("mobile_location"=>$mobile_location, "mobile_current_filesize"=>0),array("data_id"=>$data_id));
	}

	static function setResumeTime($data_id, $data=array()){ // In Seconds
		$resume_time = $data['resume_time'];
		// DB::query("UPDATE movieview, episodeview SET resumeTimeInSeconds=:resumeTimeInSeconds WHERE movieview.c09=:data_id OR episodeview.c09=:data_id;", array(':resumeTimeInSeconds'=>$resume_time,':data_id'=>$data_id), "XBMC");
		DB::query("UPDATE files SET resume_time=:resume_time WHERE data_id=:data_id;", array(':resume_time'=>$resume_time,':data_id'=>$data_id));		
		Debug::msg("[".$data_id."] Updated the Resume Time for ".Data::getTitle($data_id)." to ".$resume_time." seconds.");
		return $data;
	}


// Update
	static function updateMobileCurrentFilesize($data_id, $filesize){
		DB::update("files", array("mobile_current_filesize"=>$filesize), array("data_id"=>$data_id));
	}


}


/*

These are the Triggers to SYNC the XBMC DB with our DB (Run in XBMC DB):





DELIMITER &&
	CREATE TRIGGER movie_resumetime 
	AFTER UPDATE ON MyVideos75.bookmark FOR EACH ROW 
		BEGIN
			UPDATE media_center_2.files SET resume_time=NEW.timeInSeconds WHERE data_id=(SELECT c09 FROM MyVideos75.movieview WHERE idFile=NEW.idFile);
    	END&&
DELIMITER ;

DELIMITER &&
	CREATE TRIGGER episode_resumetime 
	AFTER UPDATE ON MyVideos75.bookmark FOR EACH ROW 
		BEGIN
			UPDATE media_center_2.files SET resume_time=NEW.timeInSeconds WHERE data_id=(SELECT c09 FROM MyVideos75.episodeview WHERE idFile=NEW.idFile);
    	END&&
DELIMITER ;

DELIMITER &&
	CREATE TRIGGER movie_creation_resumetime 
	AFTER INSERT ON MyVideos75.files FOR EACH ROW 
		BEGIN
			IF (SELECT resume_time FROM media_center_2.files WHERE data_id=NEW.c09) THEN
			ELSE
			END IF;
			UPDATE media_center_2.files SET resume_time=NEW.timeInSeconds WHERE data_id=(SELECT c09 FROM MyVideos75.movieview WHERE idFile=NEW.idFile);
    	END&&
DELIMITER ;
on insert into MyVideos75.files, if media_center_2.files.resume_time > 0 OR media_center_2.files.play_count > 0, then insert into MyVideos75.bookmark idFile, timeInSeconds, totalTimeInSeconds, player="DVDPlayer", type=1


DELIMITER &&
	CREATE TRIGGER episode_resumetime 
	AFTER UPDATE ON MyVideos75.bookmark FOR EACH ROW 
		BEGIN
			UPDATE media_center_2.files SET resume_time=NEW.timeInSeconds WHERE data_id=(SELECT c09 FROM MyVideos75.episodeview WHERE idFile=NEW.idFile);
    	END&&
DELIMITER ;



These are the Triggers to SYNC our DB with the XBMC DB (Run in our DB):
DELIMITER &&
	CREATE TRIGGER movie_resumetime 
	AFTER UPDATE ON media_center_2.files FOR EACH ROW 
		BEGIN
			UPDATE MyVideos75.bookmark SET timeInSeconds=NEW.resume_time WHERE idFile=(SELECT idFile FROM MyVideos75.movieview WHERE c09=NEW.data_id);
    	END&&
DELIMITER ;

DELIMITER &&
	CREATE TRIGGER episode_resumetime 
	AFTER UPDATE ON MyVideos75.bookmark FOR EACH ROW 
		BEGIN
			UPDATE MyVideos75.bookmark SET timeInSeconds=NEW.resume_time WHERE idFile=(SELECT idFile FROM MyVideos75.episodeview WHERE c09=NEW.data_id);
    	END&&
DELIMITER ;

*/

?>