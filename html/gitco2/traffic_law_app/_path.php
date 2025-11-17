<?php
session_start();


require(".env.php");

if(!isset($_SESSION['controllerid'])) header("location: ".$EmpySession);


ini_set('display_errors',"1");
date_default_timezone_set("Europe/Rome");

define("CLS",   ROOT."/cls");
define("INC",   ROOT."/inc");
define("TCPDF", ROOT. "/tcpdf");

define("IMG",   $MainPath."/img");
define("JS",    $MainPath."/js");
define("LIB",   $MainPath."/lib");
define("CSS",   $MainPath."/css");



//define("NATIONAL_VIOLATION", "/var/www/html/TL/traffic_law/doc/national/violation");
//define("NATIONAL_VIOLATION", "/var/www/gdeluca/traffic_law/doc/national/violation");
//define("FOREIGN_VIOLATION", "/var/www/gdeluca/traffic_law/doc/foreign/violation");


//define("NATIONAL_VIOLATION_HTML", "/traffic_law/doc/national/violation");
//define("FOREIGN_VIOLATION_HTML", "/traffic_law/doc/foreign/violation");

//define("NATIONAL_VIOLATION_HTML", "/traffic_law/doc/national/violation");
//define("FOREIGN_VIOLATION_HTML", "/traffic_law/doc/foreign/violation");












define("NATIONAL_VIOLATION", "/var/www/html/gitco2/traffic_law/doc/national/violation");
define("FOREIGN_VIOLATION", "/var/www/html/gitco2/traffic_law/doc/foreign/violation");


define("NATIONAL_VIOLATION_HTML", "/gitco2/traffic_law/doc/national/violation");
define("FOREIGN_VIOLATION_HTML", "/gitco2/traffic_law/doc/foreign/violation");
