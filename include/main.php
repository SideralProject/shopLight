<?php
$PRMS = empty($_POST) ? $_GET : $_POST;

if (empty($PRMS)) exit;

function __autoload($class_name) {
    include $class_name . ".class.php";
}
if (get_magic_quotes_gpc()) {
	function stripslashes_gpc(&$value) { $value = stripslashes($value); }
	array_walk_recursive($PRMS, 'stripslashes_gpc');	
}

$cls;
$rsp = array("error"=>false);
if (!empty($PRMS["act"])) {
	$rsp["type"] = $PRMS["act"];
	unset($PRMS["act"]);
	$cls = new Core();		

} else {
	exit();
}

try {
	$rsp["data"] = $cls->call($rsp["type"], $PRMS);
} catch (Exception $e) {
	$rsp["error"]["code"] = $e->getCode();
	$rsp["error"]["msg"] = $e->getMessage();
}

echo json_encode($rsp);
?>