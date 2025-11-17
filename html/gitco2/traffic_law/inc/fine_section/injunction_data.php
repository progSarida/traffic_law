<?php
global $Id;

$rs_FineInjunction = $rs->Select("FineInjunction","FineId = $Id");
$r_FineInjunction = $rs->getArrayLine($rs_FineInjunction);

$str_CSSInjunction = 'data-toggle="tab"';
$str_Injunction = '
        <div class="col-sm-12 BoxRowTitle" >
            <div class="col-sm-12 BoxRowTitle" style="text-align:center">
                Coattiva
            </div>
        </div>
        <div class="clean_row HSpace16"></div>';

$str_Injunction .= '
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">Data e ora registrazione</div>
            <div class="col col-sm-3 BoxRowLabel">'.DateOutDB($r_FineInjunction['RegDate'])." ".$r_FineInjunction['RegTime'].'</div>
            <div class="col-sm-3 BoxRowLabel">Data inoltro al concessionario</div>
            <div class="col col-sm-3 BoxRowLabel">'.DateOutDB($r_FineInjunction['ConcessionaireSendDate']).'</div>
            <div class="clean_row HSpace4"></div>';
$str_Injunction_data = '';
if(mysqli_num_rows($rs_FineInjunction)>0){
    //Imposta la visualizzazione per il dettaglio verbale
    $str_Injunction_data = '
        <div class="tab-pane" id="Injunction">
            <div id="InjunctionWindow" class="col-sm-12">
                '.$str_Injunction.'
            </div>
        </div>
                    
    ';
    }
else{
    $str_CSSInjunction = ' style="color:#C43A3A; cursor:not-allowed;" ';
}