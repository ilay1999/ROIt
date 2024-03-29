<?php
    include 'ConParams.php';
	
	$connect = mysqli_connect($ConDBparams[0]['dbip'], $ConDBparams[0]['dblogin'], $ConDBparams[0]['dbpass'], $ConDBparams[0]['dbname'], $ConDBparams[0]['port']);

	if (!$connect){
		die('Error no connect');
	}
?>