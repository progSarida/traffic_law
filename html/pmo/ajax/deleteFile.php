<?php
$path = $_POST['path'];

if(unlink($path)) echo "OK";
else echo "Eliminazione fallita";
