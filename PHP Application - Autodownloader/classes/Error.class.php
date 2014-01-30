<?

namespace MediaCenter;

class Error {

    static function fatal($string){
        Debug::msg($string, "FATAL");
        if (!empty($GLOBALS['cron'])){
            Crons::finishCron();
        }
        if (!empty($GLOBALS['ajax'])){ // Output error if we're going to die
            echo json_encode(array('error'=>1,'msg'=>$string));
        }
       die();
    }

    static function warning($string){
        Debug::msg($string, "WARNING");
    }

    static function notice($string){
        Debug::msg($string, "NOTICE");
    }

    static function quit($string){
        Debug::msg($string, "QUIT");
        if (!empty($GLOBALS['cron'])){
            Crons::finishCron();
        }
        if (!empty($GLOBALS['ajax'])){ // Output error if we're going to die
            echo json_encode(array('error'=>1,'msg'=>$string));
        }
        die();
    }
   
}

?>