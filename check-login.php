<?php
session_start();
if(isset($_SESSION['login']) & ($_SESSION['login'] == true)){

}else{
	header('location:login.php');
	exit;
}
if(isset($_SESSION['id']) & !empty($_SESSION['id'])){

}else{
	header('location:login.php');
	exit;
}
if(isset($_SESSION['last_login']) & !empty($_SESSION['last_login'])){

}else{
	header('location:login.php');
	exit;
}

// Verify the user still exists in the database.
// Handles the case where a user is deleted while their session is still active.
require_once('includes/connect.php');
$chksql = "SELECT id FROM users WHERE id=? AND activate=1";
$chkresult = $db->prepare($chksql);
$chkresult->execute(array($_SESSION['id']));
if($chkresult->rowCount() != 1){
	session_destroy();
	header('location:login.php');
	exit;
}
?>