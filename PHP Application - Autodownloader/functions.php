<?

namespace MediaCenter;

// Functions without a home, or functions required for the Class loader

function print_r($array){
	echo "<xmp>";
	\print_r($array);
	echo "</xmp>";
}

function rm_folder_recursively($dir) {
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rm_folder_recursively("$dir/$file");
        else unlink("$dir/$file");
    }
    rmdir($dir);
    return true;
}

function clean_string($string){
    $string = preg_replace('/[^A-Za-z0-9 ]/', '', $string);
    return $string;
}

// This catches fatals... but doesn't ECHO output (just so you know in case you think it's not working)
register_shutdown_function('\MediaCenter\shutDownFunction'); 
function shutDownFunction() { 
    $error = error_get_last();
    if ($error['type']==2048) return;
    if (!empty($error)) { 
        if ($error['type']==1){
            \MediaCenter\Error::fatal("[SYSTEM] ".$error['message']." in ".$error['file']." on line ".$error['line']." (".$error['type'].") [no_echo]");
        } else {
            \MediaCenter\Error::warning("[SYSTEM] ".$error['message']." in ".$error['file']." on line ".$error['line']." (".$error['type'].") [no_echo]");
        }
    }
} 

?>