<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

$rs= new CLS_DB();


$CurrentDay = date("Y-m-d");
$str_MgmtFine = '';
$rs_Fine = $rs->Select('V_Fine', "StatusTypeId=13 AND ProtocolYear=2022 AND ProtocolId=0 AND ControllerId=".$_SESSION['controllerid']);
if(mysqli_num_rows($rs_Fine)>0){
    $str_MgmtFine = '
        <div class="col-xs-11 BoxRowLabel" style="font-size: 2rem; padding:1rem; border:2px solid #7e6b6b; text-align:center; margin-bottom:1rem; height:4.6rem; ">
            <a href="mgmt_warning_upd.php" style="color:#fff">GESTISCI PREAVVISO</a>
        </div>
    
        <div class="clean_row HSpace4"></div>
    ';

} else {
    $str_MgmtFine = '
        <div class="col-xs-12 BoxRowLabel" style="font-size: 2rem; padding:1rem; border:2px solid #7e6b6b; text-align:center; margin-bottom:1rem; height:4.6rem; color:#fff">
            <a href="mgmt_warning_add.php" style="color:#fff">INSERISCI PREAVVISO</a>
        </div>
        <div class="clean_row HSpace4"></div>
    ';
}




$str_out = '
    <div class="row-fluid">
        <div class="col-xs-12">

            '. $str_MgmtFine .'

        </div>
    </div>            
    ';








$rs_Fine = $rs->Select('V_Fine', "StatusTypeId=13 AND ProtocolYear=2022 AND ProtocolId>0 AND ControllerId=".$_SESSION['controllerid']. " AND Finedate='". $CurrentDay ."'", "FineTime DESC");


$str_out .= '
    <div class="row-fluid" style="position:absolute; top:30rem; width:100%;">

        <div class="table_label_H col-xs-11">Preavvisi creati oggi </div>
    
        <div class="clean_row HSpace4"></div>
        
        <div class="table_label col-xs-1" style="background-color:#fff; height:3.2rem"></div>
        
        <div class="table_label col-xs-8" style="height:3.2rem;">
            <div class="table_label_H col-xs-4">Cron</div>
            <div class="table_label_H col-xs-4">Ora</div>				
            <div class="table_label_H col-xs-4">Targa</div>				
        </div>
        
        <div class="table_label_H col-xs-2" style="background-color:#fff"></div>	
                
        <div class="clean_row HSpace4"></div>
        
        
    ';
if(mysqli_num_rows($rs_Fine)==0){

    $str_out .= '

        <div class="table_label col-xs-12">
            Nessun preavvio presente
        </divtable_label>
    ';
} else {
    while ($r_Fine = mysqli_fetch_array($rs_Fine)) {

        $rs_FineDocumentation = $rs->Select('FineDocumentation', "Documentation LIKE '%.pdf' AND DocumentationTypeId=1 AND FineId=".$r_Fine['Id'], " Documentation");
        
        $str_Pdf_Link = '';
        if(mysqli_num_rows($rs_FineDocumentation)>0){
            $cont = 0;
            while ($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)) {
                $cont++;
                $str_Icon = ($cont==1) ? '<span class="	glyphicon glyphicon-open-file" style="font-size:2.5rem;line-height:3rem;"></span>' : '<span class="glyphicon glyphicon-save-file" style="font-size:2.5rem;line-height:3rem;"></span>';
                $str_Pdf_Link .= '<a href="../traffic_law/doc/national/violation/'. $r_Fine['CityId'] .'/'. $r_Fine['Id'] .'/'. $r_FineDocumentation['Documentation'] .'" target="_BLANK">'. $str_Icon .'</a> &nbsp; &nbsp;';
            }
            
        }
        $str_out .= '
            <div class="table_label col-xs-1" style="background-color:#fff; height:3.2rem">
                <a href="mgmt_warning_viw.php?FineId='. $r_Fine['Id'] .'" style="color:#000"><span class="glyphicon glyphicon-eye-open" style="font-size:1.5rem; line-height:3rem;"></span></a>
            </div>
            <div class="table_label col-xs-8" style="height:3.2rem;">
                <div class="table_label col-xs-4" style="height:3.2rem; line-height:3rem;">'. $r_Fine['ProtocolId'] .'</div>
                <div class="table_label col-xs-4" style="height:3.2rem; line-height:3rem;">'. TimeOutDB($r_Fine['FineTime']) .'</div>				
                <div class="table_label col-xs-4" style="height:3.2rem; line-height:3rem;">'. $r_Fine['VehiclePlate'] .'</div>
            </div>			
            <div class="table_label col-xs-2" style="background-color:#fff; height:3.2rem;">'. $str_Pdf_Link .'</div>		  
       
            <div class="clean_row HSpace4"></div>
        ';
    }
}




$str_out .= '</div>';


echo $str_out;


include(INC."/footer.php");