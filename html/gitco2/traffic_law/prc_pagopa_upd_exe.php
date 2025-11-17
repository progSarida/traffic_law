<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
/*
$url_Request = "https://nodopagamenti-test.regione.liguria.it/portale/nodopagamenti/rest/pagamentodiretto/modificaimporto";
$str_Param = "iuv=002600000261603&importo=199.99&causaleAnnullo=";
https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=002000000120072
*/

$url_Request = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/rest/pagamentodiretto/modificaimporto";

$rs_ProcessingPagoPA = $rs->SelectQuery("SELECT * FROM `FineArticle` WHERE CityId='U480' AND Fee=230.67 AND FineId IN (SELECT Id From Fine WHERE PagoPA1 IS NOT NULL AND PagoPA2 IS NOT NULL)");

$Cont = 0;
while ($r_ProcessingPagoPA = mysqli_fetch_array($rs_ProcessingPagoPA)) {
    $Cont++;
    $rs_Fine = $rs->Select('Fine', "ProtocolYear=2019 AND PagoPA1 IS NOT NULL AND PagoPA2 IS NOT NULL AND Id=".$r_ProcessingPagoPA['FineId']);
    $r_Fine = mysqli_fetch_array($rs_Fine);


    $str_PagoPa1 = "iuv=".$r_Fine['PagoPA1']."&importo=176.70&causaleAnnullo=";

    $ch = curl_init($url_Request);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $str_PagoPa1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);



    if(curl_exec($ch) === false)
    {
        echo "Errore: ". curl_error($ch)." - Codice errore: ".curl_errno($ch);
        DIE;
    }
    else
    {
        $result1=curl_exec($ch);
    }

    curl_close($ch);




    $str_PagoPa2 = "iuv=".$r_Fine['PagoPA2']."&importo=245.90&causaleAnnullo=";
    $ch = curl_init($url_Request);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $str_PagoPa2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);



    if(curl_exec($ch) === false)
    {
        echo "Errore: ". curl_error($ch)." - Codice errore: ".curl_errno($ch);
        DIE;
    }
    else
    {
        $result2=curl_exec($ch);
    }

    curl_close($ch);



echo $Cont.") ".$r_ProcessingPagoPA['FineId']." --- ". $str_PagoPa1 . " --- " .$str_PagoPa2. " " ."<br>";

}




DIE;
