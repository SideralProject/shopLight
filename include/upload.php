<?php 
require_once('SimpleImage.php');
$target = basename($_FILES['fileup']['name']);
$ok = 0; 
//This is our size condition 
//if ($uploaded_size > 350000) { 
//	echo "Your file is too large.<br>"; 
//	$ok=0; 
//} 
//This is our limit file type conditio
switch ($_FILES['fileup']['type']) {
	case "image/jpeg":
		$ok = 1;
	break;
}
if ($ok==0) { 
	echo "0"; 
} else { 
	$name = $_POST['cat'] . "_" . $_POST['art'] . "_" . $_POST['num'] . ".jpg";		
	if(move_uploaded_file($_FILES['fileup']['tmp_name'], "../art/".$name)) { 
		$image = new SimpleImage();		
   		$image->load("../art/".$name);
		$image->resizeToWidth(375);
		$image->save("../art/".$name);		
		$image->resizeToWidth(150);				
        $image->save("../art/thumb/".$name);		
		echo "10";
	} else { 
		echo "2"; 
	} 
} 
?> 