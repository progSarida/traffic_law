<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

ini_set('max_execution_time', 3000);

const PAYMENT_TYPES=array("1"=>"BONIFICO BANCARIO","18"=>"BOLLETTINO TELEMATICO","2"=>"BONIFICO POSTALE");

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER."/_PAYMENT_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;
$error = false;
$msgProblem = "";

$PaymentTypeId = CheckValue('PaymentTypeId', 'n');
$str_out .= '
    	                <form name="f_import" action="imp_bank_exe.php">

    	<div class="row-fluid">

                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                        Tipo Pagamento
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                    '.CreateArraySelect(PAYMENT_TYPES,true,"PaymentTypeId","PaymentTypeId",$PaymentTypeId,true).'
                    </div>

                </div>			
        </div>
        <div class="clean_row HSpace4"></div>';


if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".","$file");
        if (strtolower($aFile[count($aFile)-1])=="csv"){
            $Cont++;
            $FileList .=  '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">'.$Cont . '</div>
            <div class="table_caption_H col-sm-10">'.$file . '</div>
            
            <div class="table_caption_button col-sm-1">
                '. ChkButton($aUserButton, 'imp','<a onClick="importFile('."'".$str_CurrentPage.'&ImportFile='.$file."'".')"><span class="fa fa-upload"></span></a>') .'
                &nbsp;
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


$str_out .='

        <div class="row-fluid">
        	<div class="col-sm-12">
        	    <div class="table_label_H col-sm-12">IMPORTAZIONE BONIFICI</div>
        	    <div class="clean_row HSpace4"></div>	
				<div class="table_label_H col-sm-11">ELENCO FILE</div>
				<div class="table_add_button col-sm-1 right">
        			'.ChkButton($aUserButton, 'add','<a href="mgmt_violation_add.php"><span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span></a>').'      				
				</div>
				<div class="clean_row HSpace4"></div>	
			</div>
            	
            ' . $FileList;


if($ImportFile==""){
    $str_out .=
        '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
		</div>';
}else{

    if($file = fopen($path . $ImportFile, "r")){
        if (strtolower(substr($ImportFile,-3))=="csv") {

            $delimiter = detectDelimiter($path . $ImportFile);
            $cont = 0;


            $customers = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
            $customer = mysqli_fetch_array($customers);


            $str_out .= '
            <div class="col-sm-12">
                <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
                    <div class="clean_row HSpace4" ></div >	
                </div >
            </div >
            <div class="col-sm-12">
                <div class="table_label_H col-sm-1">Riga</div>
                <div class="table_label_H col-sm-1">Data pag.</div>
                <div class="table_label_H col-sm-1">Data accr.</div>
                <div class="table_label_H col-sm-1">Cron</div>
                <div class="table_label_H col-sm-4">Nome</div>
                <div class="table_label_H col-sm-1">Importo</div>
                <div class="table_label_H col-sm-3"></div>
    
                <div class="clean_row HSpace4"></div>

                <div class="table_label_H col-sm-12">Note</div>

                <div class="clean_row HSpace4"></div>
            </div>
            ';



            $List = 0;
            $TableId=1;

            $a_FindStr = array("142/7",
                "142/8",
                "142/9",
                "142-7",
                "142-8",
                "142-9",
                "142.7",
                "142.8",
                "142.9",
                "ART 142",
                "ART142",
                "ART. 142",
                "ART.142",
                "DEL 30",
                " 5 G",
                "RIDOTTA 30",
                " 5GG",
                " 5 GG",
                " 5 GIORNI",
                " 5GIO"
            );

            $a_ReplaceStr = array("","","","","","","","","","","","","","","","","","","","");


            if(is_resource($file)) {

                while (!feof($file)) {
                    $row = fgetcsv($file, 0, $delimiter);

                    $Id = "";
                    if (isset($row[0])) {

                        if ($List == 0) {
                            if (trim($row[0] == "Data Contabile")) {
                                $List = 1;
                            }
                        } else {
                            $cont++;
                            
                            $PaymentDate = $row[0];
                            if (strlen($PaymentDate) == 8) {
                                $PaymentDate = substr($PaymentDate, 0, 6) . "20" . substr($PaymentDate, 6, 2);
                            }
                            $CreditDate = $row[1];
                            if (strlen($CreditDate) == 8) {
                                $CreditDate = substr($CreditDate, 0, 6) . "20" . substr($CreditDate, 6, 2);
                            }
                            $Amount = $row[6];
                            $Causal = $row[8];

                            $Descr = trim($row[3]);

                            $Name = "";
                            if ($Descr == "Bonifico dall'estero") {
                                //////////////////////////////////////////////////////////////////////////////
                                //////////////////////////////////////////////////////////////////////////////
                                ////
                                ////
                                ////    FOREIGN
                                ////
                                ////
                                //////////////////////////////////////////////////////////////////////////////
                                //////////////////////////////////////////////////////////////////////////////

                                if (strpos($Causal, 'ZZ21/') !== false) {
                                    $a_NameCausal = explode("ZZ21/", trim($Causal));
                                    $aName = explode("ZZ3", trim($a_NameCausal[1]));
                                    $Name = trim($aName[0]);
                                } else if (strpos($Causal, 'ZZ2') !== false) {
                                    $a_NameCausal = explode("ZZ2", trim($Causal));
                                    $aName = explode("ZZ3", trim($a_NameCausal[1]));
                                    $Name = trim($aName[0]);
                                }

                                if ($Name != "") {
                                    if (strpos($Causal, 'CRON') !== false) {
                                        $aId = explode("CRON", trim($Causal));

                                    } else {
                                        $aId = explode("ZZ3", trim($Causal));
                                    }

                                    $pos_StartNumber = -1;
                                    $pos_EndNumber = 0;

                                    if (isset($aId[1])) {
                                        $str_Clean = str_replace($a_FindStr, $a_ReplaceStr, $aId[1]);


                                        for ($i = 0; $i < strlen($str_Clean); $i++) {

                                            if (is_numeric(substr($str_Clean, $i, 1))) {
                                                if ($pos_StartNumber < 0) {
                                                    $pos_StartNumber = $i;
                                                }
                                            } else {
                                                if ($pos_StartNumber >= 0) {
                                                    $pos_EndNumber = $i;
                                                    break;
                                                }
                                            }

                                        }
                                        $n_Start = $pos_StartNumber;
                                        $n_Lenght = $pos_EndNumber - $pos_StartNumber;

                                        if ($n_Lenght == 0) $n_Lenght = 1;

                                        $Id = substr($str_Clean, $n_Start, $n_Lenght);
                                    } else $str_Clean = "";

                                }


                                //$Amount = intval(substr($Amount, 0, (strlen($Amount) - 2))) . "." . substr($Amount, -2);


                                $FindNumber = 0;
                                if ($Id == "" || $Id == 0 || $Id == "/") {
                                    $Id = 0;
                                } else {
                                    $fines = $rs->Select('V_FineTrespasser', "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolId=" . $Id, "ProtocolYear DESC");
                                    $FindNumber = mysqli_num_rows($fines);
                                }


                                $chkFine = '';
                                $chkPayment = '';
                                $chkTrespasser = '';
                                $chkAmount = '';

                                if ($FindNumber == 0) {
                                    $chkFine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                    $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Verbale non trovato</div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
                                } else {
                                    $trespasser = mysqli_fetch_array($fines);
                                    $Id = $trespasser['FineId'];
                                    $payments = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $Id . " AND TableId=" . $TableId);
                                    $FindNumber = mysqli_num_rows($payments);

                                    if ($FindNumber > 0) {

                                        $chkPayment = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                        $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Pagamento già presente</div>
                            <div class="clean_row HSpace4"></div>
                        </div>    
                        ';
                                    } else {

                                        if ($trespasser['Genre'] == 'D') {
                                            $a_TrespasserCompany = explode(" ", trim($trespasser['CompanyName']));
                                            if (isset($a_TrespasserCompany[0])) {
                                                $str_TrespasserFind = $a_TrespasserCompany[0];
                                            } else {
                                                $str_TrespasserFind = $trespasser['CompanyName'];
                                            }
                                        } else $str_TrespasserFind = $trespasser['Surname'];

                                        if (strpos($Causal, $str_TrespasserFind) === false) {
                                            if (strpos($Causal, $trespasser['VehiclePlate']) === false) {
                                                $chkTrespasser = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                                $msgProblem .= '
                                    <div class="col-sm-12">
                                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                        <div class="table_caption_H col-sm-11 alert-danger">Tragressore non trovato</div>
                                        <div class="clean_row HSpace4"></div>
                                    </div>    
                                    ';

                                            }
                                        }
                                    }
                                }


                            } else {

                                //////////////////////////////////////////////////////////////////////////////
                                //////////////////////////////////////////////////////////////////////////////
                                ////
                                ////
                                ////    NATIONAL
                                ////
                                ////
                                //////////////////////////////////////////////////////////////////////////////
                                //////////////////////////////////////////////////////////////////////////////


                                $a_Causal = explode(" ID", trim($Causal));

                                $a_Name = explode(" ", trim($a_Causal[0]));

                                $Name = str_replace($a_Name[0], "", trim($a_Causal[0]));

                                if (strpos($Amount, ".") === false && strpos($Amount, ",") === false) {
                                    $Amount = $Amount . ".00";
                                } else {

                                    $Amount = str_replace(",", ".", $Amount);

                                }


                                if (strpos($Causal, 'CRON') !== false) {
                                    $aId = explode("CRON", trim($Causal));

                                } else {
                                    $aId = explode("RI1", trim($Causal));
                                }


                                $pos_StartNumber = -1;
                                $pos_EndNumber = 0;

                                if (isset($aId[1])) {
                                    $str_Clean = str_replace($a_FindStr, $a_ReplaceStr, $aId[1]);


                                    for ($i = 0; $i < strlen($str_Clean); $i++) {

                                        if (is_numeric(substr($str_Clean, $i, 1))) {
                                            if ($pos_StartNumber < 0) {
                                                $pos_StartNumber = $i;
                                            }
                                        } else {
                                            if ($pos_StartNumber >= 0) {
                                                $pos_EndNumber = $i;
                                                break;
                                            }
                                        }

                                    }
                                    $n_Start = $pos_StartNumber;
                                    $n_Lenght = $pos_EndNumber - $pos_StartNumber;

                                    if ($n_Lenght == 0) $n_Lenght = 1;

                                    $Id = substr($str_Clean, $n_Start, $n_Lenght);
                                } else $str_Clean = "";


                                if ($Id == "" || $Id == 0 || $Id == "/") $Id = 0;

                                $fines = $rs->Select('V_FineTrespasser', "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolId=" . $Id, "ProtocolYear DESC");
                                $FindNumber = mysqli_num_rows($fines);

                                $chkFine = '';
                                $chkPayment = '';
                                $chkTrespasser = '';
                                $chkAmount = '';

                                if ($FindNumber == 0) {
                                    $chkFine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                    $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Verbale non trovato</div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
                                } else {
                                    $trespasser = mysqli_fetch_array($fines);
                                    $Id = $trespasser['FineId'];
                                    $payments = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $Id . " AND TableId=" . $TableId);
                                    $FindNumber = mysqli_num_rows($payments);

                                    if ($FindNumber > 0) {

                                        $chkPayment = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                        $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Pagamento già presente</div>
                            <div class="clean_row HSpace4"></div>
                        </div>    
                        ';
                                    } else {

                                        if ($trespasser['Genre'] == 'D') {
                                            $a_TrespasserCompany = explode(" ", $trespasser['CompanyName']);
                                            $str_TrespasserFind = $a_TrespasserCompany[0];
                                        } else $str_TrespasserFind = $trespasser['Surname'];

                                        if (strpos($Causal, $str_TrespasserFind) === false) {
                                            if (strpos($Causal, $trespasser['VehiclePlate']) === false) {
                                                $chkTrespasser = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                                $msgProblem .= '
                                    <div class="col-sm-12">
                                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                        <div class="table_caption_H col-sm-11 alert-danger">Tragressore non trovato</div>
                                        <div class="clean_row HSpace4"></div>
                                    </div>    
                                    ';

                                            }
                                        }
                                    }
                                }

                            }


                            $str_out .= '
                <div class="col-sm-12"> 
                    <div class="table_caption_H col-sm-1">' . $cont . '</div>
                    <div class="table_caption_H col-sm-1">' . $chkPayment . $PaymentDate . '</div>
                    <div class="table_caption_H col-sm-1">' . $CreditDate . '</div>
                    <div class="table_caption_H col-sm-1">' . $chkFine . $Id . '</div>
                    <div class="table_caption_H col-sm-4">' . $chkTrespasser . $Name . '</div>
                    <div class="table_caption_H col-sm-1">' . $Amount . '</div>
                    <div class="table_caption_button col-sm-3"></div>

                    <div class="clean_row HSpace4"></div>
                    
                    <div class="table_caption_H col-sm-12" style="height:8rem;">' . $Causal . '</div>
    
                    <div class="clean_row HSpace16"></div>
                </div>    
                ';
                        }
                    }

                }
                fclose($file);
            } else {
                $_SESSION['Message']['Error'] = 'Errore nell\'apertura del file: '.$ImportFile;
            }
            if (!$error) {
                $str_out .= '
            <div class="col-sm-12">
                <input type="hidden" name="P" value="imp_bank.php">
                <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
                <div class="table_label_H col-sm-12">
                    <input type="submit" value="Importa" >                           
                </div >
            </div >';
            }

        }
    } else {
        $_SESSION['Message']['Error'] = 'Errore nell\'apertura del file: '.$ImportFile;
    }

}
$str_out.="</form>";

if(strlen($msgProblem)>0){
    $str_out.= '
		<div class="clean_row HSpace48"></div>
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>
		</div>
		' . $msgProblem;
    
}

echo $str_out;
?>

<script>
    function importFile(url){
        window.location=url+"&PaymentTypeId="+document.getElementById("PaymentTypeId").value;
    }  
</script>

<?php
include(INC."/footer.php");


