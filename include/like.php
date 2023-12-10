<?php
session_name('bam_logon');
session_start();
if (isset($_POST['id'],$_POST['activo'])) {
	if (isset($_SESSION['likes']))
		$key = array_search($_POST['id'], $_SESSION['likes']);
	else $key = false;
	if ( $_POST['activo']==0 && $key!==false) { //desactivar corazon
		unset($_SESSION['likes'][$key]);
	}
	elseif ($_POST['activo']==1 && $key===false) {
		$_SESSION['likes'][]=$_POST['id'];
	}
	if (isset($_SESSION['likes'])) {
		//print_r($_SESSION['likes']);
		
	};
}

?>