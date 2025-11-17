<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Users = isset($_POST['check']) ? $_POST['check'] : null;
$CityId = CheckValue('CityId', 's');
$CityYear = CheckValue('CityYear', 'n');
$Filters = CheckValue('Filters', 's');

if ($Users){
    if ($CityYear > 0){
        $rs->Start_Transaction();
        foreach ($Users as $UserId){
            $rs_UserCity = $rs->Select('sarida.UserCity', "CityId='$CityId' AND CityYear=$CityYear AND UserId=$UserId");
            if (mysqli_num_rows($rs_UserCity) <= 0){
                $a_Insert = array(
                    array('field'=>'MainMenuId','selector'=>'value','type'=>'int','value'=>3,'settype'=>'int'),
                    array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$UserId,'settype'=>'int'),
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                    array('field'=>'CityYear','selector'=>'value','type'=>'int','value'=>$CityYear,'settype'=>'int'),
                );
                
                $rs->Insert('sarida.UserCity', $a_Insert);
            }
        }
        $rs->End_Transaction();
        
        $_SESSION['Message'] = 'Azione eseguita con successo.';
    } else $_SESSION['Message'] = 'Errore: anno non valido.';
}else $_SESSION['Message'] = 'Errore: selezionare almeno un utente.';

header("location:tbl_customer_year_add.php".$Filters."&Filter=1");
