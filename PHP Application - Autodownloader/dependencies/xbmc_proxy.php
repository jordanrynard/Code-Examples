<?
// Same origin policy issue with Remote (taken from Play To XBMC chrome extension), so we use a makeshift proxy here

$data = file_get_contents("php://input");

// $content = file_get_contents('a.txt');
// file_put_contents('a.txt', $content."\n".$data);
echo file_get_contents('http://triangle-pi.local:8080/jsonrpc?request='.urlencode($data));
?>