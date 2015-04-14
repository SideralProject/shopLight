<?php
	session_start();
	echo json_encode(array('msg' => session_destroy()));
?>
