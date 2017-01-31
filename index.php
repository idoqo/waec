<?php
require "vendor/autoload.php";


/*if (!empty($_POST)) {
	$tada = $_POST['hello'];
	$response = array("status"=>"success", "message"=>$tada);
	header("Content-type: application/json");
	echo json_encode($response);
}*/
$req = new \model\Request();
$req->setExamYear(2016);
$req->setExamType($req::$EXAM_TYPE_RAIN);
$req->setCardSerial("4564567657");
$req->setCardPin("564557657787");
$req->setExamNumber("08965464644");
echo $req->execute();