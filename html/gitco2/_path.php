<?php
require_once('cost-sarida-gitco.php');
ini_set('display_errors', "1");
date_default_timezone_set("Europe/Rome");

session_start();

define("ROOT", __DIR__);

$MainPath =  CONTESTO_URL;

define("IMG", $MainPath . "/loghi");
define("FONT", $MainPath . "/fonts");
define("INC", ROOT . "/inc");
define("JS", $MainPath . "/traffic_law/js");
define("LIB", $MainPath . "/lib");
define("CSS", $MainPath . "/traffic_law/css");
define("CLS", ROOT . "/cls");
