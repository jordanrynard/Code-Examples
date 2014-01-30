<?
	// Called by Crontab belonging to www-data

	// To list all Cron Tabs, and their jobs, Run this as root only in Command Line, not here:
	// for user in $(cut -f1 -d: /etc/passwd); do echo $user; crontab -u $user -l; echo; done

// There are our Auto-Download operations:
//	\MediaCenter\Torrents::findTorrents();
//	\MediaCenter\Torrents::downloadTorrents();
//	\MediaCenter\Torrents::updateQueuedTorrents();
//	\MediaCenter\Torrents::updateDownloadingTorrents();
//	\MediaCenter\Torrents::processDownloadedTorrents();
//	\MediaCenter\ConvertVideo::updateConvertingMobileFiles();
//	\MediaCenter\ConvertVideo::processConvertedMobileFiles();



	include ('loader.php');

	if (\MediaCenter\Settings::get('crons_disabled')!=0 && (!empty($argv[1]) && $argv[1] == 'run_by_crontab')){ // If Crons are disabled we can still run them manually via the web
		\MediaCenter\Error::quit("Crons are disabled. There is nothing to see here.");
	}

	if (!empty($_GET['create'])){
		\MediaCenter\Crons::createSystemCronFile();
		\MediaCenter\Error::quit("Crontab file has been updated");
	}

	// If this file if being called by the Crontab (or manually via the web)
	if (!empty($_SERVER['REMOTE_ADDR']) || $argv[1] == 'run_by_crontab'){ 
		\MediaCenter\Crons::runCrons();
	
	} else {
	// If this file is being executed as a specific Job from the Crons class
		set_time_limit(0);
		$class = $argv[1];
		$method = $argv[2];
		if (empty($class) || empty($method)){
			\MediaCenter\Error::fatal("There was a problem with this Cron task"); 
		}
		$GLOBALS['cron'] = array('class'=>$class,'method'=>$method);
		\MediaCenter\Crons::beginCron($class,$method);
		\MediaCenter\Crons::finishCron($class,$method);	
	}

?>
