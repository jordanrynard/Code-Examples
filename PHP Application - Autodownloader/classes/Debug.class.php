<?

namespace MediaCenter;

class Debug {

    static function msg($string='', $level='MSG'){
        if (DEBUG_ON){ 
            $string = str_pad("[".$level."]",9," ",STR_PAD_RIGHT)." [".self::backtraceClass()."] ".$string; 
            $log_entry = "[".date("m d Y h:i:s A")."] ".$string."\n";
            if (!stristr($string,'no_log')){ // SocketIO
                self::log($log_entry);
            }
            if (!stristr($string,'no_echo') && empty($GLOBALS['ajax'])){ // System Errors, SocketIO (also don't output if it's AJAX)
                echo "<pre>".$string."\n</pre>";
            }
            if (LIVE_DEBUG_ON){
                if (!stristr($string,'no_live_debug')){ // Loader, SocketIO
                    self::liveDebug($log_entry);
                }
            }
        }
    }

    static function spacer($append=''){
        $string = "=================================================================================================================================\n";
        self::msg("\n\n".$string.$string.$string.$string.$string."\n".$append, "SPACER");
    }

    static function log($log_entry){
        $i=0; log: $i++;
        $log_locked = intval(file_get_contents(LOG_LOCK_FILE));
        if ($log_locked){
            if ($i > 10) {
                Error::warning("Haven't been able to access the Debug Log for 10 seconds. Methinks the log lock is stuck. [no_log]"); 
                return;
            }
            sleep(1);
            goto log;
        }
        file_put_contents(LOG_LOCK_FILE, 1);
        $log_contents = file_get_contents(LOG_FILE);
        $appended_log_contents = $log_entry.$log_contents;
        file_put_contents(LOG_FILE, $appended_log_contents);
        file_put_contents(LOG_LOCK_FILE, 0);
    }

    static function liveDebug($log_entry){
        SocketIO::emit("update_live_debug", array('msg'=>$log_entry, 'keywords'=>array('no_log','no_echo','no_live_debug') ));
    }

    static function backtraceClass(){
        $trace = debug_backtrace();      
        trace:
        $last_trace = array_shift($trace);
        $where = !empty($last_trace['class'])?$last_trace['class']:$last_trace['function'];
        $where = end(explode("\\",$where)); // Have to remove Namespaces
        if (($where=='include' || $where=='Debug' || $where=='Error') && count($trace)>0){ // 0 because we could have popped the last one off the array so it's empty
            goto trace; 
        }
        return $where;
        // $trace[0] is ourself 
        // $trace[1] is our caller
        // and so on...
        // echo "called by {$trace[1]['class']} :: {$trace[1]['function']}";
    }

}

?>