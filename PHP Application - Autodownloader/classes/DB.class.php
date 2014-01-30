<?

namespace MediaCenter;
use \PDO; 
use \PDOException;

class DB {
	private $db_connection;

	function __construct(){
	}

	static function connect(){
		try {
			$db_connection = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS);
		} catch(PDOException $e) {
			// We could've used traits here
			Error::fatal("Problem connecting to database: ".$e->getMessage());
		}
		return $db_connection;
	}

	static function xbmc_connect(){
		try {
			$db_connection = new PDO('mysql:host='.DB_XBMC_HOST.';dbname='.DB_XBMC_NAME.';charset=utf8',DB_XMBC_USER,DB_XBMC_PASS);
		} catch(PDOException $e) {
			// We could've used traits here
			Error::fatal("Problem connecting to database: ".$e->getMessage());
		}
		return $db_connection;
	}


	static function select($table, $select=array(), $where=array()){
		$conn = self::connect();
		if (!empty($select) && is_array($select)){
			$select_string = implode(', ', $select);
		} else {
			$select_string = "*";
		}
		if (!empty($where) && is_array($where)){
			$where_string = implode(', ', array_map(function($v, $k){ return $k."='".$v."'"; }, $where, array_keys($where)));
		} else {
			$where_string = "";
		}
		try {
			$q = $conn->prepare("SELECT ".$select_string." FROM `".$table."` ".(!empty($where_string)?"WHERE ".$where_string.";":";"));
			$result = $q->execute();
		} catch(PDOException $e) {
			Error::warning("Problem SELECTing from database: ".$e->getMessage());
			return array();
		}
		if (empty($result)){
			$error_info = $q->errorInfo();
			Error::warning("Problem fetching data from database: ".$error_info[2]);
			return array();
		}
		return $q->fetchAll(PDO::FETCH_ASSOC);
	}

	static function update($table, $set=array(), $where=array()){
		$conn = self::connect();
		if (!empty($set) && is_array($set)){
			$set_string = implode(', ', array_map(function($v, $k){ return $k."=:".$k; }, $set, array_keys($set)));
		} else {
			$set_string = "";
		}
		if (!empty($where) && is_array($where)){
			$where_string = implode(' AND ', array_map(function($v, $k){ return $k."=:".$k.""; }, $where, array_keys($where)));
		} else {
			$where_string = "";
		}
		try {
			// Use: INSERT INTO .... ON DUPLICATE KEY UPDATE
			$q = $conn->prepare("UPDATE `".$table."` SET ".$set_string." WHERE ".$where_string.";");
			$result = $q->execute(array_merge($set,$where));
		} catch(PDOException $e) {
			Error::warning("Problem UPDATEing database: ".$e->getMessage());
			return false;
		}
		if (empty($result)){
			$error_info = $q->errorInfo();
			Error::warning("Problem updating data in database: ".$error_info[2]);
			return false;
		}
		return true;
	}

	static function insert($table, $set=array()){
		$conn = self::connect();
		if (!empty($set) && is_array($set)){
			$set_string = implode(', ', array_map(function($v, $k){ return $k."=:".$k; }, $set, array_keys($set)));
		} else {
			$set_string = "";
		}
		try {
			// Use: INSERT INTO .... ON DUPLICATE KEY UPDATE
			$q = $conn->prepare("INSERT IGNORE INTO `".$table."` SET ".$set_string.";");
			$result = $q->execute($set);
		} catch(PDOException $e) {
			Error::warning("Problem INSERTing database: ".$e->getMessage());
			return false;
		}
		if (empty($result)){
			$error_info = $q->errorInfo();
			Error::warning("Problem inserting data into database: ".$error_info[2]);
			return false;
		}
		$id = $conn->lastInsertId();
		return $id;
	}

	static function remove($table, $where=array()){
		$conn = self::connect();
		if (!empty($where) && is_array($where)){
			$where_string = implode(', ', array_map(function($v, $k){ return $k."=:".$k; }, $where, array_keys($where)));
		} else {
			$where_string = "";
		}
		try {
			// Use: INSERT INTO .... ON DUPLICATE KEY UPDATE
			$q = $conn->prepare("DELETE FROM `".$table."` WHERE ".$where_string.";");
			$result = $q->execute($where);
		} catch(PDOException $e) {
			Error::warning("Problem DELETEing database: ".$e->getMessage());
			return false;
		}
		if (empty($result)){
			$error_info = $q->errorInfo();
			Error::warning("Problem removing data from database: ".$error_info[2]);
			return false;
		}
		return true;
	}

	static function query($query, $vars=array(), $xbmc=false){
		if (!$xbmc){
			$conn = self::connect();
		} else {
			$conn = self::xbmc_connect();
		}
		try {
			// Use: INSERT INTO .... ON DUPLICATE KEY UPDATE
			$q = $conn->prepare($query);
			$result = $q->execute($vars);
		} catch(PDOException $e) {
			Error::warning("Problem QUERYing database: ".$e->getMessage());
			return false;
		}
		if (empty($result)){
			$error_info = $q->errorInfo();
			Error::warning("Problem querying data in database: ".$error_info[2]);
			return false;
		}
		if (stristr($query,"select")){
			return $q->fetchAll(PDO::FETCH_ASSOC);
		}
		return $result;
	}

}

?>