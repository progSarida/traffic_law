<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER."/U563/";
$ImportFile = CheckValue('ImportFile','s');
$chk_Tolerance = 0;
$error = false;

$str_Problem    = '';
$str_Detector   = '';
$str_Article    = '';

if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".","$file");
        if (strtolower($aFile[count($aFile)-1])=="xml"){
            $Cont++;
            $FileList .=  '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">'.$Cont . '</div>
            <div class="table_caption_H col-sm-10">'.$file . '</div>
            <div class="table_caption_button col-sm-1">
                '. ChkButton($aUserButton, 'imp','<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>') .'
                <a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>
            </div>
            <div class="clean_row HSpace4"></div>
			</div>    
			';
        }
    }

    closedir($directory_handle);
}
if($Cont==0){
    $FileList =  '
            <div class="col-sm-12">
                <div class="table_caption_H col-sm-11">Nessun file presente</div>
                <div class="table_caption_button col-sm-1"></div>
			    <div class="clean_row HSpace4"></div>
			</div>    
			';
}

$str_out ='
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="'.$_SESSION['blazon'].'" style="width:50px;">
					<span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>
				</div>
			</div>
		</div>		
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-11">ELENCO FILE</div>
				<div class="table_add_button col-sm-1 right">
        			'.ChkButton($aUserButton, 'add','<a href="mgmt_violation_add.php"><span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span></a>').'      				
				</div>
				<div class="clean_row HSpace4"></div>	
			</div>
            	
            ' . $FileList;

echo $str_out;


if($ImportFile==""){
    $str_out =
        '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
		</div>';
}else{






    $xml=simplexml_load_file($path.$ImportFile) or die("Error: Cannot create object");

    $cont = 0;

    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE '.$ImportFile.'</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-2">Numero</div>
            <div class="table_label_H col-sm-3">IDUnivoco\</div>
            <div class="table_label_H col-sm-3">Proto</div>
            <div class="table_label_H col-sm-2">Protoa</div>
            <div class="table_label_H col-sm-1">Record</div>
            <div class="clean_row HSpace4"></div>	
        </div>
        ';

    foreach($xml->children() as $xml_Import) {
        $chkFine = "";
        $cont++;




        $Numero = $xml_Import->Numero->Numero;

        $IDUnivoco = $xml_Import->Numero->IDUnivoco;

        $Proto = $xml_Import->ProtoCDS->Proto;

        $Protoa = $xml_Import->ProtoCDS->Protoa;




        $finds = $rs->Select('Fine', "CityId='U563' AND Code='".$Numero."/".$Protoa."' AND IuvCode=''");
        $FindNumber = mysqli_num_rows($finds);

        $chk_Fine =  ($FindNumber == 1) ? "OK" : "noooo";

        $str_out .='
        <div class="col-sm-12"> 
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$cont.'</div>
            <div class="table_caption_H col-sm-2'.$chkFine.'">'.$Numero . '</div>
            <div class="table_caption_H col-sm-3'.$chkFine.'">'.$IDUnivoco.'</div>
            <div class="table_caption_H col-sm-3'.$chkFine.'">'.$Proto . '</div>
            <div class="table_caption_H col-sm-2'.$chkFine.'">'.$Protoa . '</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$chk_Fine . '</div>



            <div class="clean_row HSpace4"></div>
        </div>    
        ';



        echo "UPDATE Fine SET IuvCode='".$IDUnivoco."' Code='".$Proto."/".$Protoa."' WHERE CityId='U563' AND Code='".$Numero."/".$Protoa."'<br />";



        $a_Fine = array(
            array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Proto."/".$Protoa, 'settype'=>'str'),
            array('field'=>'IuvCode','selector'=>'value','type'=>'str','value'=>$IDUnivoco, 'settype'=>'str'),
        );


        $rs->Update('Fine',$a_Fine, "CityId='U563' AND Code='".$Numero."/".$Protoa."' AND IuvCode=''");






    }

}

echo $str_out;




