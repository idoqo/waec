<?php
require "vendor/autoload.php";

//set default controller and action
header("Content-type: application/json");
$controller = "Request";
$action = "index";

include_once "router.php";