<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['_token']) || ($_POST['_token'] !== $_SESSION['_token'])) {
		die('Invalid token.');
	}
}	
$_SESSION['_token'] = bin2hex(openssl_random_pseudo_bytes(16));

if (!$_SESSION['authenticated']) {
	Header ("Location: /login.php");
}


?>
