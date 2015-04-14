<?php
class Core extends DBBase {

	private static $aMethods = array(
		'migrateWight'			=> 'migrateWight',
		
		
		'getMetalPrices'		=> 'getMetalPrices',
		'setMetalPrices'		=> 'setMetalPrices',
		'getCategories'			=> 'getCategories',
		'editCategory'			=> 'editCategory',
		'deleteCategory'		=> 'deleteCategory',
		'addCategory'			=> 'addCategory',
		'getArticleInCategory'	=> 'getArticleInCategory',
		'deleteArticle'			=> 'deleteArticle',
		'getAdverts'			=> 'getAdverts',
		'setAdverts'			=> 'setAdverts',
		
		'moveArts'			=> 'moveArts',
		'delArts'			=> 'delArts',
		'delArtImg'			=> 'delArtImg',
		'getOrders'			=> 'getOrders',
		'confirmOrder'		=> 'confirmOrder',
		'rejectOrder'		=> 'rejectOrder',
		'sendOrder'			=> 'sendOrder',
	);
	
	
	public function call($method, $params) {
		if(empty(self::$aMethods[$method])) throw new Exception("Prohibited!");
		if(!is_array($params)) throw new Exception("Invalid parameters!");	
		return call_user_func_array(array($this,self::$aMethods[$method]),array($params));
	}
		
	/*
public function init() {
		$sQuery = "
			SELECT 
				c.*,
				i.id AS iid,
				i.name AS iname, 
				i.desc AS idesc,
				i.price AS iprice,
				i.price_free AS iprice_free
			FROM categories AS c 
			LEFT JOIN instock AS i ON i.id_category = c.id AND i.to_arc = 0
			WHERE c.to_arc = 0
			ORDER BY c.id
		";
		$result = $this->query($sQuery);
		$aCategories = array();
		while($cat = $result->fetch_assoc()) {
			$aCategories[$cat['id']]['name'] = $cat['name'];
			$aCategories[$cat['id']]['desc'] = $cat['desc'];
			if (!empty($cat['iid'])) {
				$aCategories[$cat['id']]['art'][$cat['iid']]['name'] = $cat['iname'];
				$aCategories[$cat['id']]['art'][$cat['iid']]['desc'] = $cat['idesc'];
				$aCategories[$cat['id']]['art'][$cat['iid']]['price'] = $cat['iprice'];
				$aCategories[$cat['id']]['art'][$cat['iid']]['price_free'] = $cat['iprice_free'];
			} else {
				$aCategories[$cat['id']]['art'] = null;
			}
		}				
		return $aCategories;
	}
	
	public function getInstock() {
		$aCategories;		
		$sQuery = "
			SELECT 
				c.id AS cid,
				c.name AS cname,
				i.id AS iid,
				i.name AS iname, 
				i.description AS idescription
			FROM categories AS c 
			LEFT JOIN instock AS i ON i.id_category = c.id
		";
		if (!empty($search)) {
			$search = trim($search);
			$search = strip_tags($search);
			if (get_magic_quotes_gpc()) $search = stripslashes($search);
			$search = mysql_real_escape_string($search);
			$sQuery." WHERE description like '%".$search."%'";
		}
		$result = mysql_query($sQuery);
		$aCategories = array();
		while($cat = mysql_fetch_assoc($result)) {
			if (!empty($cat['iid'])) {
				$aCategories[$cat['cid']]['cname'] = $cat['cname'];
				$aCategories[$cat['cid']]['child'][$cat['iid']]['iname'] = $cat['iname'];
				$aCategories[$cat['cid']]['child'][$cat['iid']]['idescription'] = $cat['idescription'];
			} else {
				$aCategories[$cat['cid']] = null;
				$aCategories[$cat['cid']]['cname'] = $cat['cname'];
			}
		}		
		return $aCategories;
	}
	
	public function addCategory($name, $user) {
		$sQuery = "
			INSERT INTO categories 
				(name,created_user,created_time) 
			VALUES('$name','$user','$this->now')";
		$this->query($sQuery);
		return $this->init();
	}
	
	public function delArtImg($cat, $art, $num) {				
		return	unlink("../art/".$cat."_".$art."_".$num.".jpg") && 
				unlink("../art/thumb/".$cat."_".$art."_".$num.".jpg");
	}

	public function editArtDesc($id, $name, $price, $price_free, $desc, $user) {		
		$sQuery = "
			UPDATE 
				instock 
			SET 
				instock.name = '$name', 
				instock.price = '$price',
				instock.price_free = '$price_free',
				instock.desc = '$desc', 
				updated_user = '$user', 
				updated_time = '$this->now' 
			WHERE id = $id";
		$result = $this->query($sQuery);
		if ($result) {
			$result = $this->query("SELECT * FROM instock WHERE id=$id");			
			$result = $result->fetch_assoc();
		}
		return $result;	
	}
	
	public function delArts($cat, $aIDs, $user) {
		$ins = implode(",", $aIDs);		
		$sQuery = "
			UPDATE 
				instock 
			SET 
				to_arc = 1, 
				updated_user = '$user', 
				updated_time = '$this->now' 
			WHERE id IN($ins)";
		$this->query($sQuery);
		try {
			foreach($aIDs as $id) {			
				array_map('unlink', glob("../art/".$cat."_".$id."*"));
				array_map('unlink', glob("../art/thumb/".$cat."_".$id."*"));
			}
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
		return true;
	}
	
	public function moveArts($cat, $aIDs, $user) {
		$ins = implode(",", $aIDs);	
		$sQuery = "
			UPDATE 
				instock 
			SET 
				id_category = $cat, 
				updated_user = '$user', 
				updated_time = '$this->now' 
			WHERE id IN($ins)";
		$this->query($sQuery);		
		foreach ($aIDs as $id) {			
			$aFiles = array_merge(glob("../art/*_".$id."_*.jpg"), glob("../art/thumb/*_".$id."_*.jpg"));
			foreach($aFiles as $file) {				
				rename($file, preg_replace("/\d/", $cat, $file, 1));				
			}
		}				
		return $this->init();
	}
	
	public function addArt($cat, $user) {
		$aReturn;
		$sQuery = "
			INSERT INTO instock 
				(id_category,created_user,created_time) 
			VALUES('$cat','$user','$this->now')";
		$this->query($sQuery);
		$aReturn['idArt'] = mysql_insert_id();
		$aReturn['categories'] = $this->init();
		return $aReturn;
	}
	
	public function getOrders($dateFrom, $dateTo) {
		$sDateField = "date_recv";		
		$sQuery = "
			SELECT 
				o.*, 
				i.price, 
				i.id_category 
			FROM orders AS o 
			LEFT JOIN instock AS i ON i.id = o.id_instock 
			WHERE";
		$sQueryNew = $sQuery . " 
				o.date_recv BETWEEN '$dateFrom' AND '$dateTo' 
				AND o.confirmed = 0 
				AND o.rejected = 0 
				AND o.sended = 0 
			ORDER BY o.date_recv DESC";		
		$sQueryConfirmed = $sQuery . " 
				o.updated_time BETWEEN '$dateFrom' AND '$dateTo' 
				AND o.confirmed = 1 
				AND o.rejected = 0 
				AND o.sended = 0 
			ORDER BY o.updated_time DESC";
		$sQueryRejected = $sQuery . " 
				o.updated_time BETWEEN '$dateFrom' AND '$dateTo' 
				AND o.rejected = 1 
				AND o.sended = 0 
			ORDER BY o.updated_time DESC";
		$sQuerySended = $sQuery . " 
				o.updated_time BETWEEN '$dateFrom' AND '$dateTo' 
				AND o.sended = 1 
			ORDER BY o.updated_time DESC";		
		
		$aResult = array();		
	
		$result = $this->query($sQueryNew);
		if ($result) {
			$aResult["new"] = array();
			while ($row = $result->fetch_assoc()) {
				array_push($aResult["new"], $row);	
			}		
		}
		$result = $this->query($sQueryConfirmed);		
		if ($result) {
			$aResult["confirmed"] = array();
			while ($row = $result->fetch_assoc()) {
				array_push($aResult["confirmed"], $row);	
			}		
		}
		$result = $this->query($sQueryRejected);
		if ($result) {
			$aResult["rejected"] = array();
			while ($row = mysql_fetch_assoc($result)) {
				array_push($aResult["rejected"], $row);	
			}		
		}
		$result = $this->query($sQuerySended);
		if ($result) {
			$aResult["sended"] = array();
			while ($row = $result->fetch_assoc()) {
				array_push($aResult["sended"], $row);	
			}		
		}		
		return $aResult;
	}
	
	public function login($uname, $upass) {		
		$upass = md5($upass);
		$res = $this->query("SELECT * FROM users WHERE user='$uname' AND pass='$upass'");		
		return mysql_fetch_assoc($res);
	}
	
	public function confirmOrder($id, $user) {
		$sQuery = "
			UPDATE 
				orders 
			SET 
				confirmed = 1, 
				updated_user = $user,
				updated_time = '$this->now' 
			WHERE id = $id";
		return $this->query($sQuery);
	}
	
	public function rejectOrder($id, $user) {
		$sQuery = "
			UPDATE 
				orders 
			SET 
				rejected = 1,
				updated_user = $user, 
				updated_time = '$this->now' 
			WHERE id = $id";
		return $this->query($sQuery);		
	}
	
	public function sendOrder($idOrder, $idArt, $user) {
		$sQuery = "
			UPDATE 
				instock 
			SET 
				to_arc = 1, 
				updated_user = $user, 
				updated_time = '$this->now' 
			WHERE id = $idArt";
		$this->query($sQuery);
		$sQuery = "
			UPDATE 
				orders 
			SET 
				sended = 1, 
				updated_user = $user,
				updated_time = '$this->now'
			WHERE id = $idOrder";
		$this->query($sQuery);		
		return $this->init();
	}
	
	public function saveReklama($txtr_1, $txtr_2, $txtr_3, $acti_1, $acti_2, $acti_3, $user) {
		$sQuery = "UPDATE reklama SET txt = '$txtr_1', active = '$acti_1' WHERE id = 1";
		$this->query($sQuery);
		$sQuery = "UPDATE reklama SET txt = '$txtr_2', active = '$acti_2' WHERE id = 2";
		$this->query($sQuery);
		$sQuery = "UPDATE reklama SET txt = '$txtr_3', active = '$acti_3' WHERE id = 3";
		$this->query($sQuery);
		return true;
	}
	
*/
	public function migrateWight() {
		try {
		$sQuery = "SELECT id,description FROM instock_demo WHERE to_arc = 0 AND id_category = 9";
		$res = $this->selectArray($sQuery);
		$len = sizeof($res);
		foreach ($res as $k=>$v) {
			$aF = explode(" ", $v["description"]);
			foreach ($aF as $kk=>$gr) {
				if (strpos($gr, "гр")) {
					$aS = explode("гр", $gr);
					$sQuery = "UPDATE instock_demo SET weight='".$aS[0]."' WHERE id = ".$v["id"];
					$this->query($sQuery);
				}	
			}
		}
		return "good";
		} catch(Exception $e) {
			return $e;
		}
	}
	
	public function getAdverts() {
		$sQuery = "SELECT * FROM reklama_demo";
		return $this->selectArray($sQuery);
	}
	
	public function setAdverts($params) {
		foreach($params as $k=>&$v) $v = $this->sanitaze($v);
		$sQuery = "UPDATE reklama_demo SET txt = '".$params["txt1"]."', active = '".$params["active1"]."' WHERE id = 1";
		$this->query($sQuery);
		$sQuery = "UPDATE reklama_demo SET txt = '".$params["txt2"]."', active = '".$params["active2"]."' WHERE id = 2";
		$this->query($sQuery);
		$sQuery = "UPDATE reklama_demo SET txt = '".$params["txt3"]."', active = '".$params["active3"]."' WHERE id = 3";
		$this->query($sQuery);
		return true;
	}

	public function getMetalPrices() {
		$sQuery = "SELECT gold,silver FROM metal_prices_demo";
		$resp = $this->selectArray($sQuery);
		return $resp[0];	
	}
	
	public function setMetalPrices($params) {
		foreach($params as $k=>&$v) $v = $this->sanitaze($v);
		print_r($params);
		$sQuery = "UPDATE metal_prices_demo SET ";
		if (!empty($params["gold"])) {
			$sQuery .= "gold = '".$params["gold"]."'";
			return $this->query($sQuery);
		}
		if (!empty($params["silver"])) {
			$sQuery .= "silver = '".$params["silver"]."'";
			return $this->query($sQuery);
		}
	}
	
	public function addCategory($params) {
		foreach($params as $k=>&$v) $v = $this->sanitaze($v);
		$sQuery = "INSERT INTO categories_demo (name,description,created_time,created_user) VALUES('".$params["name"]."','".$params["description"]."','".$this->now."','".$params["user"]."')";
		return $this->query($sQuery);
	}
	public function getCategories() {
		$sQuery = "SELECT id,name,description FROM categories_demo WHERE to_arc=0 ORDER BY id";
		return $this->selectArray($sQuery);
	}
	
	public function editCategory($params) {
		foreach($params as $k=>&$v) $v = $this->sanitaze($v);	
		$sQuery = "
			UPDATE 
				categories_demo 
			SET 
				name = '".$params["name"]."',
				updated_user = '".$params["user"]."', 
				updated_time = '$this->now'
			WHERE id = ".$params["id"];
		$result = $this->query($sQuery);		
		
		return $result;				
	}
	
	public function deleteCategory($params) {	
		foreach($params as $k=>&$v) $v = $this->sanitaze($v);	
		$aQueries = array(
			"UPDATE 
				categories_demo
			SET 
				to_arc = 1, 
				updated_user = '".$params["user"]."', 
				updated_time = '$this->now' 
			WHERE id = ".$params["id"],
			
			"UPDATE 
				instock_demo 
			SET 
				id_category = 1, 
				updated_user = '".$params["user"]."', 
				updated_time = '$this->now' 
			WHERE id_category = ".$params["id"]
		);

		return $this->queryTrans($aQueries);
	}
	
	public function getArticleInCategory($params) {
		foreach($params as $k=>&$v) $v = $this->sanitaze($v);
		$sQuery = "SELECT id,id_category,name,description,price,price_free,weight FROM instock_demo WHERE to_arc = 0 AND id_category = '".$params["id"]."'";
		return $this->selectArray($sQuery);
	}
	
	public function deleteArticle($params) {
		foreach($params as $k=>&$v) $v = $this->sanitaze($v);
		$sQuery = "UPDATE instock_demo SET to_arc = 1,updated_user='".$params["user"]."',updated_time='".$this->now."' WHERE id = ".$params["id"];
		return $this->query($sQuery);
	}
	
	public function getReklama() {
		$result = $this->query("SELECT * FROM reklama_demo");	
		$aResult = array(0=>0);
		while ($row = $result->fetch_assoc()) array_push($aResult, $row);
		return $aResult;
	}

}
?>
