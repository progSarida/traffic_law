<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");



if($_POST) {


	$keyword = '%' . addslashes($_POST['keyword']) . '%';
	$field = $_POST['field'];

	$finds = $rs->SelectQuery("SELECT DISTINCT $field FROM Fine WHERE CityId='" . $_SESSION['cityid'] . "' AND $field LIKE '$keyword' ORDER BY $field");


	while ($find = mysqli_fetch_array($finds)) {
		echo "<li onclick=\"set_item('" . str_replace("'", "\'", $find[$field])  . "','$field')\">".$find[$field]."</li>";
	}

}
