<?

namespace MediaCenter;

class Crons {
	
	static function runCrons(){
		$crons = self::getOnlyReadyCrons();
		foreach ($crons as $cron){
			self::runCron($cron);
		}
	}

	static function runCron($cron=array()){
		Debug::msg(str_pad("[Executing]",15," ",STR_PAD_RIGHT).$cron['class']."::".$cron['method']);		
	    exec("php -f ".ROOT_DIR."/cron.php '".$cron['class']."' '".$cron['method']."' >> /dev/null &"); // This allows me to run multiple crons at the same time
	}

	static function beginCron($class, $method){
		Debug::msg(str_pad("[Started]",15," ",STR_PAD_RIGHT).$class."::".$method);		
		DB::query("UPDATE _crons SET is_active=1 WHERE class=:class AND method=:method;", array(':class'=>$class,':method'=>$method));
		if (!method_exists("\\MediaCenter\\".$class, $method)){
			Error::fatal("The following Class/Method does not Exist: ".$class."::".$method);
		}
		$result = call_user_func("\\MediaCenter\\".$class."::".$method);
	}

	static function finishCron($class='', $method=''){
		if (empty($class) && empty($method)){ // Used for When a fatal Error is triggered
			$class = $GLOBALS['cron']['class'];
			$method = $GLOBALS['cron']['method'];
		}
		Debug::msg(str_pad("[Finished]",15," ",STR_PAD_RIGHT).$class."::".$method);
		DB::query("UPDATE _crons SET last_run=now(), is_active=0 WHERE class=:class AND method=:method;", array(':class'=>$class,':method'=>$method));
	}

	static function getOnlyReadyCrons(){
		// !! Need to compensate for fact that the cron doesn't run every 1 minute because of the delay after execution to insertion into DB, so lets subtract 5 seconds
		// We use the select in a select so we can work with the calculated variable in the where statement -- and the derived select statement requires an alias, so we just use 'table_alias'
		$crons = DB::query('SELECT * FROM _crons WHERE TIME_TO_SEC(TIMEDIFF(now(),last_run)) - (interval_mins*60-5) > 0 AND is_active=0 AND interval_mins!=0');
		return $crons;
	}
	
	static function createSystemCronFile(){
		if(!function_exists('shell_exec')) {
			Error::fatal("shell_exec is not enabled.");
		}
		$user = shell_exec('echo "$(whoami)"');
		Debug::msg("Cron has been inserted to Crontab for user: ".$user);
		// Remove any Cron Entries we've entered previously
		$result = shell_exec("crontab -l | grep -v '#dynamic_cron' | crontab -"); 
		Debug::msg("Removed any existing #dynamic_cron. ".$result);
		// Add new Cron entry
		$result = shell_exec('(crontab -l ; echo "* * * * * php '.ROOT_DIR.'/cron.php run_by_crontab #dynamic_cron") |uniq - | crontab -');
		Debug::msg("Added new #dynamic_cron to CronTab. ".$result);
		// List all Cron Jobs... Run this as root only in Command Line, not here:
		// for user in $(cut -f1 -d: /etc/passwd); do echo $user; crontab -u $user -l; echo; done
	}

}

?>