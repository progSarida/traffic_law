<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');


ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER . "/" . $_SESSION['cityid'] . "/";
$ImportFile = CheckValue('ImportFile', 's');
$chkTolerance = 0;
$error = false;
$str_MsgProblem = "";


if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".", "$file");
        if (strtolower($aFile[count($aFile) - 1]) == "csv") {
            $Cont++;
            $FileList .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">' . $Cont . '</div>
            <div class="table_caption_H col-sm-10">' . $file . '</div>
            <div class="table_caption_button col-sm-1">
                <a href="imp_pec.php?ImportFile=' . $file . '"><span class="fa fa-upload"></span></a>
                &nbsp;
            </div>
            <div class="clean_row HSpace4"></div>
			</div>    
			';
        }
    }

    closedir($directory_handle);
}
if ($Cont == 0) {
    $FileList = '
            <div class="col-sm-12">
                <div class="table_caption_H col-sm-11">Nessun file presente</div>
                <div class="table_caption_button col-sm-1"></div>
			    <div class="clean_row HSpace4"></div>
			</div>    
			';
}

$str_out = '
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="' . $_SESSION['blazon'] . '" style="width:50px;">
					<span class="title_city">' . $_SESSION['citytitle'] . ' ' . $_SESSION['year'] . '</span>
				</div>
			</div>
		</div>		
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">ELENCO FILE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
            	
            ' . $FileList;

echo $str_out;


if ($ImportFile == "") {
    $str_out =
        '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
		</div>';
} else {


    $file = fopen($path . $ImportFile, "r");
    $delimiter = ",";
    $cont = 0;


    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Riga</div>
            <div class="table_label_H col-sm-1">Id trasgressore</div>
            <div class="table_label_H col-sm-2">Nominativo</div>
            <div class="table_label_H col-sm-1">P.IVA/CF presente</div>            
            <div class="table_label_H col-sm-1">Nuova P.IVA/CF</div>
            
            <div class="table_label_H col-sm-2">PEC presente</div>            
            <div class="table_label_H col-sm-2">Nuova PEC</div>

            <div class="table_label_H col-sm-1">IrideCode presente</div>            
            <div class="table_label_H col-sm-1">Nuovo IrideCode</div>


            <div class="clean_row HSpace4"></div>	
        </div>
        ';
    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0]) && trim($row[0]) != "") {
                $str_TaxCode = trim($row[2]);
                $str_PEC = trim($row[3]);

                $n_IrideCode = (isset($row[4]) && strlen(trim($row[4])) > 0) ? trim($row[4]) : 0;


                if ($str_PEC != "") {
                    $cont++;


                    if (strlen($str_TaxCode) != 16 && strlen($str_TaxCode) != 11 && strlen($str_TaxCode) != 0) {
                        for ($i = strlen($str_TaxCode); $i < 11; $i++) {
                            $str_TaxCode = "0" . $str_TaxCode;
                        }

                    }


                    $TrespasserId = $row[1];


                    $rs_Trespasser = $rs->Select('Trespasser', "Id=" . $TrespasserId);

                    if (mysqli_num_rows($rs_Trespasser) == 0) {
                        $error = true;
                        $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $str_MsgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Trasgressore con id ' . $TrespasserId . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

                    } else {

                        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);

                        $str_Name = trim($r_Trespasser['CompanyName'] . " " . $r_Trespasser['Surname'] . " " . $r_Trespasser['Name']);
                        $str_PreviousPEC = $r_Trespasser['PEC'];
                        $str_PreviousTaxCode = $r_Trespasser['TaxCode'];
                        $n_PreviousIrideCode = $r_Trespasser['IrideCode'];


                        $str_out .= '
            <div class="col-sm-12"> 
                <div class="table_caption_H col-sm-1">' . $cont . '</div>
                <div class="table_caption_H col-sm-1">' . $TrespasserId . '</div>
                <div class="table_caption_H col-sm-2">' . $str_Name . '</div>
                <div class="table_caption_H col-sm-1">' . $str_PreviousTaxCode . '</div>
                <div class="table_caption_H col-sm-1">' . $str_TaxCode . '</div>
                
                <div class="table_caption_H col-sm-2">' . $str_PreviousPEC . '</div>
                <div class="table_caption_H col-sm-2">' . $str_PEC . '</div>

                <div class="table_caption_H col-sm-1">' . $n_PreviousIrideCode . '</div>
                <div class="table_caption_H col-sm-1">' . $n_IrideCode . '</div>


                <div class="clean_row HSpace4"></div>
			</div>    
            ';


                    }

                }


            }
        }
        fclose($file);
    }
    if (!$error) {
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_pec_exe.php">
            <input type="hidden" name="P" value="imp_pec.php">
            <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
            <div class="table_label_H col-sm-12">
                <input type="submit" value="Importa" >                           
            </div >
		</div >';
    }
}

echo $str_out;


if (strlen($str_MsgProblem) > 0) {
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $str_MsgProblem;

}
