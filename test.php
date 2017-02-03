<?php
require "vendor/autoload.php";

header("Content-type: application/json");
echo file_get_contents("assets/res.json");