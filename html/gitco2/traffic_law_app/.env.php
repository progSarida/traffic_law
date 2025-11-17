<?php
///////////////////////////////////////////////////////
///
/// APP TL
///
///////////////////////////////////////////////////////



//CORE VARIABLES
define("PRODUCTION",true);
define("DEBUG",true);
define("ROOT", __DIR__);


$MainRoot       = "/var/www/html";
$MainPath       = 'https://'.$_SERVER['HTTP_HOST'].'/gitco2/traffic_law_app';
$MainPathDoc    = 'https://'.$_SERVER['HTTP_HOST'].'/gitco2/traffic_law';
$EmpySession    = 'https://'.$_SERVER['HTTP_HOST'].'/gitco2/';








///////////////////////////////////////////////////////
///
/// MySql parameter
///
///////////////////////////////////////////////////////
define("DB_HOST", 'localhost');
define("DB_NAME", 'traffic_law');
define("MAIN_DB",'sarida');

define("DB_USERNAME", 'root');
define("DB_PASSWORD", 'Sarida!!1');



define("MAP_KEY",'AIzaSyBYvKk2fxjA4mcYrZ8vKomW8zUQEi1k9_s');
