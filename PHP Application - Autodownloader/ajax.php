<?
	// AJAX Calls

	include ('loader.php');

	$GLOBALS['ajax'] = true;

	$class = @$_POST['class'];
	$method = @$_POST['method'];
	$data_id = @$_POST['data_id'];
	$data = empty($_POST['data']) ? array() : $_POST['data'];


	$result = call_user_func('\MediaCenter\\'.$class.'::'.$method,$data_id,$data);
	if (!empty($result)){
		die(json_encode(array('success'=>1,'result'=>$result)));
	}
	

	die(json_encode(array('error'=>1)));

?>