<?php

include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


    $query = "SELECT * FROM Flow ORDER BY Id ASC";
    $a_flows = $rs->getResults($rs->ExecuteQuery($query));

    for($i=0;$i<count($a_flows);$i++){

        print_r($a_flows[$i]);
        echo "<br>*".substr($a_flows[$i]['InvoiceZoneDate'],0,4)."*<br>";

        $a_zoneCost = array(0,0,0,0);
        if($a_flows[$i]['Zone0Number']>0 && $a_flows[$i]['Zone0Amount']>0)
            $a_zoneCost[0] = $a_flows[$i]['Zone0Amount']/$a_flows[$i]['Zone0Number'];
        if($a_flows[$i]['Zone1Number']>0 && $a_flows[$i]['Zone1Amount']>0)
            $a_zoneCost[1] = $a_flows[$i]['Zone1Amount']/$a_flows[$i]['Zone1Number'];
        if($a_flows[$i]['Zone2Number']>0 && $a_flows[$i]['Zone2Amount']>0)
            $a_zoneCost[2] = $a_flows[$i]['Zone2Amount']/$a_flows[$i]['Zone2Number'];
        if($a_flows[$i]['Zone3Number']>0 && $a_flows[$i]['Zone3Amount']>0)
            $a_zoneCost[3] = $a_flows[$i]['Zone3Amount']/$a_flows[$i]['Zone3Number'];

        $printCost = 0;
        if($a_flows[$i]['RecordsNumber']>0 && $a_flows[$i]['OtherAmount']>0)
            $printCost = $a_flows[$i]['OtherAmount']/$a_flows[$i]['RecordsNumber'];

        $postageInvoiceId = null;
        $printInvoiceId = null;
        if($a_flows[$i]['InvoiceZoneNumber']>0 && $a_flows[$i]['InvoiceZoneDate']!=null){
            $query = "SELECT Id FROM Flow_Invoices ";
            $query.= "WHERE Number='".$a_flows[$i]['InvoiceZoneNumber']."' AND Year=".substr($a_flows[$i]['InvoiceZoneDate'],0,4)." AND Date='".$a_flows[$i]['InvoiceZoneDate']."'";
            $a_postageInvoice = $rs->getArrayLine($rs->ExecuteQuery($query));
            print_r($a_postageInvoice);
            if(!isset($a_postageInvoice['Id'])){
                $query = "INSERT INTO Flow_Invoices (Number, Year, Date) VALUES ";
                $query.= "('".$a_flows[$i]['InvoiceZoneNumber']."',".substr($a_flows[$i]['InvoiceZoneDate'],0,4).",'".$a_flows[$i]['InvoiceZoneDate']."')";
                $rs->ExecuteQuery($query);
                $postageInvoiceId = $rs->LastId();
            }
            else
                $postageInvoiceId = $a_postageInvoice['Id'];
        }

        if($a_flows[$i]['InvoiceOtherNumber']>0 && $a_flows[$i]['InvoiceOtherDate']!=null){
            $query = "SELECT Id FROM Flow_Invoices ";
            $query.= "WHERE Number='".$a_flows[$i]['InvoiceOtherNumber']."' AND Year=".substr($a_flows[$i]['InvoiceOtherDate'],0,4)." AND Date='".$a_flows[$i]['InvoiceOtherDate']."'";
            $a_printInvoice = $rs->getArrayLine($rs->ExecuteQuery($query));
            print_r($a_printInvoice);
            if(!isset($a_printInvoice['Id'])){
                $query = "INSERT INTO Flow_Invoices (Number, Year, Date) VALUES ";
                $query.= "('".$a_flows[$i]['InvoiceOtherNumber']."',".substr($a_flows[$i]['InvoiceOtherDate'],0,4).",'".$a_flows[$i]['InvoiceOtherDate']."')";
                $rs->ExecuteQuery($query);
                $printInvoiceId = $rs->LastId();
            }
            else
                $printInvoiceId = $a_printInvoice['Id'];
        }

        $query = "UPDATE Flow SET Zone0Postage = ".$a_zoneCost[0].", Zone1Postage = ".$a_zoneCost[1].", Zone2Postage = ".$a_zoneCost[2].", Zone3Postage = ".$a_zoneCost[3];
        $query.= ", PrintCost =".$printCost;
        if($postageInvoiceId!=null)
            $query.= ", PostageInvoiceId=".$postageInvoiceId;
        if($printInvoiceId!=null)
            $query.= ", PrintInvoiceId=".$printInvoiceId;
        $query.= " WHERE Id=".$a_flows[$i]['Id'];

        $rs->ExecuteQuery($query);

    }






include(INC."/footer.php");
?>
