<?php

class fileAlterationMonitor {

    // This plugin is for development use only, so don't worry about having to manually enter the theme folder currently, worry about that in v2
    protected $stylesheet =  './../../themes/twentyfourteen/style.css';
    protected $scss_stylesheet = './../../themes/twentyfourteen/style.scss';

    // Folders to monitor
    protected $dirs = array(
        './../../themes',    // wp-content/themes
    //    './../../uploads'    // wp-content/uploads (for content checking)
    //    './../../plugins'     // wp-content/plugins
    );
    protected $script_excecution_time_limit = 125;
    protected $deadLine;
    // Pusher (our method for quick and easy websocket communication via any server)
    private $pusher;
    protected $pusher_file = 'libraries/pusher/pusher.php';
    protected $pusher_key = 'bfef05c109ea89dd1789';
    protected $pusher_secret = 'af812af721e74f45b7cc';
    protected $pusher_appID = '46845';
    protected $pusher_channel = 'browser-update';
    // PHP Sass
    protected $php_sass_parser_file = '/libraries/phpsass/SassParser.php';


    public function __construct() {
        $script_start_time = time();
        // Instantiate Pusher, cause we're using it either way
        require_once($this->pusher_file);
        $this->pusher = new Pusher($this->pusher_key, $this->pusher_secret, $this->pusher_appID);
        $this->pusher->trigger(
            $this->pusher_channel, 
            'initiatingScan-'.$_GET['socketId'], 
            array(
                'script_start_time' => date("g:i:sa", $script_start_time), 
                'server_time' => date("g:i:sa",time()), 
                'scriptExecutionTimeLimit' => $this->script_excecution_time_limit
            )
        );
        // Instantiate a process
        $pid = new pid('./process_id');
        if($pid->already_running) {
            $this->pusher->trigger(
                $this->pusher_channel, 
                'alreadyScanning-'.$_GET['socketId'], 
                array(
                    'script_start_time'=>date("g:i:sa", $script_start_time), 
                    'server_time' => date("g:i:sa",time()), 
                    'scriptExecutionTimeLimit' => $this->script_excecution_time_limit
                )
            );
            $this->pusher->trigger(
                $this->pusher_channel, 
                'alreadyScanning', 
                array(
                    'script_start_time'=>date("g:i:sa", $script_start_time), 
                    'server_time' => date("g:i:sa",time()), 
                    'scriptExecutionTimeLimit' => $this->script_excecution_time_limit
                ), 
                $_GET['socketId'] // Socket ID here excludes the recipient
            );
            die();
        }
        // Let's Start Scanning
        $this->pusher->trigger(
            $this->pusher_channel, 
            'startingScan', 
            array(
                'script_start_time'=>date("g:i:sa", $script_start_time), 
                'server_time' => date("g:i:sa",time()), 
                'scriptExecutionTimeLimit' => $this->script_excecution_time_limit
            )
        );
        // No caching please
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: -1');
        // Set Time Limit
        if (!ini_get('safe_mode')) { 
            set_time_limit($this->script_excecution_time_limit); 
        }
        $current_limit = ini_get('max_execution_time');
        // In case of unsuccesful ini_get, (or unlimited execution), we fall back to the default 30 sec
        if (empty($current_limit) || $current_limit < 1) {
            $this->script_excecution_time_limit = 30;
        }
        // We stop the loop 5 sec before the time limit, just to be sure
        $this->deadLine = time() + $this->script_excecution_time_limit - 5;
        $this->main($script_start_time);
    }

    protected function main($script_start_time) {
        // Clear file state cache
        ignore_user_abort(true); //the user can stop the page from loading, but not the script.
        clearstatcache();
        // long polling loop
        // We use do-while as the truth expression is checked at the end, so the loop gets run no matter what at least once
        do {
            echo "\n"; // Required to send output to client for connection_aborted() to work
            foreach ($this->dirs as $root) {
                $file_info = $this->checkDir(realpath($root), $script_start_time);
                if ($file_info){
                    if ($file_info['extension'] == 'scss'){
                        $this->pusher->trigger($this->pusher_channel, 'processSCSS', array('file_info' => $file_info));
                        // We need to do this in here, so that the updateCSS isn't sent until AFTER the processSCSS() has finished
                        $css_file_info = $this->processSCSS();
                        $this->pusher->trigger($this->pusher_channel, 'updateCSS', array('file_info' => $css_file_info));
                        $script_start_time = time(); // We're gonna keep going, so reset time (we need this after so CSS doesn't get updated twice)
                    } elseif ($file_info['extension'] == 'css'){
                        $script_start_time = time();
                        $this->pusher->trigger($this->pusher_channel, 'updateCSS', array('file_info' => $file_info));
                    } else {
                        $this->pusher->trigger($this->pusher_channel, 'refreshBrowser', array('file_info' => $file_info));
                        die();
                    }
                }
            }
            // look for the changes every second until the execution time allows it.
            usleep(50000); // Half second (500000)... changed to 1/2 of 1/10th of a second (50000) - consumes about 10% of CPU... 1/10th was too slow (100000)
        } while (time() < $this->deadLine && !connection_aborted());
        $this->pusher->trigger(
            $this->pusher_channel, 
            'scriptTimedOutWithoutChanges', 
            array(
                'script_start_time'=>date("g:i:sa", $script_start_time), 
                'server_time' => date("g:i:sa",time()), 
                'scriptExecutionTimeLimit' => $this->script_excecution_time_limit
            )
        );
        die();
    }

    protected function checkDir($root, $script_start_time) {
        $stack[] = $root;
        // walk through the stack
        while (!empty($stack)) {
            $dir = array_shift($stack);
            if (is_dir($dir)){
                $files = glob($dir.'/*');
                // make sure that we have an array (glob can return false in some cases)
                if (!empty($files) && is_array($files)) {
                    foreach ($files as $file) {
                        if (is_dir($file)) {
                            // we add the directories to the stack to check them later
                            $stack[] = $file;
                        } elseif (is_file($file)) {
                           // check the modification times of the files
                           $file_last_modified_time = filemtime($file);
                           if ($script_start_time < $file_last_modified_time) {
                                $file_info = pathinfo($file);
                                return $file_info;
                            }
                        }
                    } // end foreach
                }
            } // end if is_dir
        } // end while
        return false;
    }

    protected function processSCSS(){
        require_once($this->php_sass_parser_file);
        $sass_parser = new SassParser(array('cache'=>false,'line_numbers'=>true));
        $css = $sass_parser->toCss($this->scss_stylesheet);
        $fh = fopen($this->stylesheet, 'w');
        if($fh) {
            fwrite($fh, $css);
            fclose($fh);
        }
        return pathinfo(realpath($this->stylesheet));
    }


} // end fileAlterationMonitor



class pid {

    protected $filename;
    public $already_running = false;
   
    function __construct($directory) {
        $this->filename = $directory . '/' . basename($_SERVER['PHP_SELF']) . '.pid';
        if(is_writable($this->filename) || is_writable($directory)) {
            if(file_exists($this->filename)) {
                $pid = (int)trim(file_get_contents($this->filename));
                if(posix_kill($pid, 0)) {
                    $this->already_running = true;
                }
            }
        } else {
            die("Cannot write to pid file '$this->filename'. Program execution halted.\n");
        }
        if(!$this->already_running) {
            $pid = getmypid();
            file_put_contents($this->filename, $pid);
        }       
    }

    public function __destruct() {
        if(!$this->already_running && file_exists($this->filename) && is_writeable($this->filename)) {
            unlink($this->filename);
        }
    }
   
} // end pid



new fileAlterationMonitor();

?>