<?php
$Documentation_Out = "";
if($_SESSION['Documentation']!=""){
    $Documentation_Out ='
		window.open(\''.$_SESSION['Documentation'].'\');
	';
    $_SESSION['Documentation']="";
}

?>


<script>

  $('document').ready(function () {

      <?= $Documentation_Out ?>

  });


</script>

</body>
</html>