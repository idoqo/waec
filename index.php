<?php
if (!empty($_POST)) {
	$tada = $_POST['hello'];
	$response = array("status"=>"success", "message"=>$tada);
	header("Content-type: application/json");
	echo json_encode($response);
}