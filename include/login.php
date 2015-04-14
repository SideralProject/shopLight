<?php
	session_start();
	require_once("mainClass.class.php");
	$mc = new mainClass();
	if (!empty($_POST['uname']) && !empty($_POST['upass'])) {
		$uname = mysql_real_escape_string($_POST['uname']);
		$upass = mysql_real_escape_string($_POST['upass']);
		$res = $mc->login($uname, $upass);
		if ($res) {			
			session_regenerate_id();
			$_SESSION['SESS_MEMBER_ID'] = $res['id'];
			$_SESSION['SESS_MEMBER'] = $res['names'];
			session_write_close();			
			echo json_encode(array(msg => true));
		} else {
			echo json_encode(array(msg => "Грешен потребител и/или парола"));
		}
	}	
?>
