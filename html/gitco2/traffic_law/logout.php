<?php
require_once ('cost-sarida-gitco.php');
session_start();
session_destroy();
header('Location: '.(CONTESTO_URL ?: '/'));