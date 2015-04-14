<?php
include 'config.php';
/**
 * Description of DBBase
 *
 * @author ivanoff
 */
class DBBase {

	public $now = "";
	private $mysqli;
	
	public function __construct() {		
		$this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}		
		$this->mysqli->query("SET CHARACTER SET utf8");
		$this->mysqli->query("SET NAMES utf8");					
		$this->now = date("Y-m-d H:i:s");
	}
	
	public function __destruct() {
		$this->mysqli->close();
	}
	
	public function sanitaze($str) {
		$str = trim($str);
		$str = strip_tags($str);
		if (get_magic_quotes_gpc()) $str = stripslashes($str);
		$str = $this->mysqli->real_escape_string($str);
		return $str;
	}
	
	public function queryTrans($aQueries) {
		$this->mysqli->autocommit(FALSE);
		$error = false;
		foreach ($aQueries as $k=>$v) $this->query($v) ? null : $error = true;		
		$error ? $this->mysqli->rollback() : $this->mysqli->commit();
		$this->mysqli->autocommit(TRUE);
		return true;		
	}
	
	public function query($sQuery) {
		mysqli_report(MYSQLI_REPORT_OFF);		
		$res = $this->mysqli->query($sQuery);		
		if ($this->mysqli->error) {
			try {    
				throw new Exception("MySQL error ". $this->mysqli->error . "<br> Query:<br> $sQuery", $this->msqli->errno);    
			} catch(Exception $e ) {
				echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
				echo nl2br($e->getTraceAsString());				
			}
		}
		return $res;
	}
	
	public function getInsertID() {
		return $this->mysqli->insert_id;
	}


	public function selectAssoc($sQuery,$id) {
		$resp = $this->query($sQuery);
		$aResp = array();				
		while ($row = $resp->fetch_assoc()) {			
			$vID = $row[$id];
			$aResp[$vID] = array();
			unset($row[$id]);
			foreach ($row as $k=>$v) {
				if ($k == $id) continue;
				$aResp[$vID][$k] = $v;
			}			
		}	
		return $aResp;
	}
        
	public function selectAssocNoneUnique($sQuery, $id) {
		$resp = $this->query($sQuery);
		$aResp = array();
		while ($row = $resp->fetch_assoc()) {
			$vID = $row[$id];
			foreach ($row as $k=>$v) {
				if ($k == $id) continue;
				$aResp[$vID][$row["id"]][$k] = $v;
			}	

		}
		return $aResp;
	}

    public function selectArray($sQuery) {
		$resp = $this->query($sQuery);
		$aResp = array();				
		while ($row = $resp->fetch_assoc()) array_push($aResp, $row);
		return $aResp;
	}
	
	public function deleteByID($table,$id) {
		$this->query("UPDATE $table SET to_arc = 1 WHERE id = $id");
	}
	
	public function realDelete() {
		
	}
	
	
}

?>