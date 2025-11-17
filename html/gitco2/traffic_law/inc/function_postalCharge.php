<?php 
function getPostalCharge($cityId, $fineDate, $cls_db = null){
    //11/02/2022 dato che questa funzione è usata da cls/PagoPAER/PagoPAERService.php in cui viene istanziata la classe CLS_DB in versione xml,
    //viene passata attraverso il parametro $cls_db, altrimenti per retrocompatibilità si usa la golbale $rs che è l'istanza usata solitamente in
    //tutta l'applicazione
    
    if(isset($cls_db)) $rs = $cls_db;
    else global $rs;
    
    $postalcharges = $rs->Select('PostalCharge', "FromDate<='$fineDate' AND ToDate IS NULL and CityId='$cityId'");
    
    $RowNumber = mysqli_num_rows($postalcharges);
    if($RowNumber==0){
        $postalcharges = $rs->Select('PostalCharge', "'$fineDate' BETWEEN FromDate AND ToDate and CityId='$cityId'");
    }
    return mysqli_fetch_array($postalcharges);
}

?>