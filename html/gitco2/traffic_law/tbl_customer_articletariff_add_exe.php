<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Selected = isset($_POST['check']) ? $_POST['check'] : null;
$Year = CheckValue('Year', 'n');
$NewFee = CheckValue('NewFee', 's');
$NewMaxFee = CheckValue('NewMaxFee', 's');
$Filters = CheckValue('Filters', 's');

$UpdatedRows = 0;

if ($Selected){
    $rs->Start_Transaction();

    foreach ($Selected as $ArticleId){
            
        $a_UpdateMin= array(
            array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$NewFee,'settype'=>'flt'),
        );
        
        $a_UpdateMax = array(
            array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$NewMaxFee,'settype'=>'flt'),
        );
        
        if ($NewFee != ''){
            $rs->Update('ArticleTariff', $a_UpdateMin, "ArticleId=$ArticleId AND Year=$Year");
        }
        if ($NewMaxFee != '') {
            $rs->Update('ArticleTariff', $a_UpdateMax, "ArticleId=$ArticleId AND Year=$Year");
        }
        
        if(mysqli_affected_rows($rs->conn) > 0) $UpdatedRows++;
    }
    
    $rs->End_Transaction();
    
    $_SESSION['Message'] = "Azione eseguita con successo, $UpdatedRows ".($UpdatedRows == 1 ? 'tariffa è stata aggiornata' : 'tariffe sono state aggiornate.');
} else $_SESSION['Message'] = "Non sono stati selezionati record.";

header("location:tbl_customer_articletariff_add.php".$Filters."&Filter=1");
