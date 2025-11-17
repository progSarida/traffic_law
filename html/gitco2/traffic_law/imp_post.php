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
$path = PUBLIC_FOLDER."/_PAYMENT_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;
$error = false;
$msgProblem = "";
/*

1- abilitazione all'importazione a cura dell'utente dei file vari;
2- che sia "a prova di stupido" e che quindi sia annotato che debbano essere importte prima il csv con le immagini e poi il txt;
3- che i bollettini dematerializzati abbiano come nome l'obbligato in solido;
4- che nella bonifica dei versamenti sia data la possibilità all'utente di scegliere il pagante tra l'obbligato in solido e trasgressore (tramite radio botton) o di digitarne il nome se altro (vedi se è possibile suggerirlo);
5- che nella bonifica dei versamenti sia possibile effettuare la ricerca non solo per cron. ma anche per n° di accertamento, obbligato in solido, targa (al momento la procedura ancorchè prevista non funziona);
6- controllare per bene lo scorporo dei versamenti nell'elenco degli stessi;
7- altro che ti ha detto che ho difficoltà ad interpretare..

*/


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
                '. ChkButton($aUserButton, 'imp','<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>') .'
                &nbsp;
            </div>
            <div class="clean_row HSpace4"></div>
			</div>    
			';
        }
        if (strtolower($aFile[count($aFile)-1])=="txt"){
            $Cont++;
            $FileList .=  '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">'.$Cont . '</div>
            <div class="table_caption_H col-sm-10">'.$file . '</div>
            <div class="table_caption_button col-sm-1">
                '. ChkButton($aUserButton, 'imp','<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>') .'
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
        	    <div class="table_label_H col-sm-12">IMPORTAZIONE BOLLETTINI POSTALI</div>
        	    <div class="clean_row HSpace4"></div>	
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

    $file = fopen($path . $ImportFile, "r");
    $aFile = explode(".",$ImportFile);



    if (strtolower($aFile[count($aFile)-1])=="csv") {


        $delimiter = detectDelimiter($path . $ImportFile);
        $cont = 0;


        $customers = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
        $customer = mysqli_fetch_array($customers);


        $str_out = '
            <div class="col-sm-12">
                <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
                    <div class="clean_row HSpace4" ></div >	
                </div >
            </div >
            <div class="col-sm-12">
                <div class="table_label_H col-sm-1">Img Riga</div>
                <div class="table_label_H col-sm-1">Data pagamento</div>
                <div class="table_label_H col-sm-1">Data accredito</div>
                <div class="table_label_H col-sm-2">Quinto campo</div>
                <div class="table_label_H col-sm-2">Tipo doc</div>
                <div class="table_label_H col-sm-2">Importo</div>
                <div class="table_label_H col-sm-3">Immagine</div>
    
                <div class="clean_row HSpace4"></div>	
            </div>
            ';
        if(is_resource($file)) {
            while (!feof($file)) {
                $row = fgetcsv($file, 1000, $delimiter);
                if (isset($row[0])) {

                    if ($row[0] == "ACCOUNTNUMBER" || trim($row[0]) == "") continue;

                    $cont++;

                    $BankAccount = trim($row[0]);
                    $PaymentDate = $row[3];
                    $CreditDate = $row[9];


                    $FifthField = str_replace("*", "", trim($row[10]));
                    $DocumentType = $row[6];
                    $Amount = trim($row[7]);
                    $Documentation = $row[18];

                    if (strlen($FifthField) < 16) {
                        $n_NumDiff = 16 - strlen($FifthField);

                        for ($i = 1; $i <= $n_NumDiff; $i++) {
                            $FifthField = "0" . $FifthField;
                        }
                    }


                    $FifthFieldTable = intval(substr($FifthField, 0, 2));
                    $FifthFieldPaymentNumber = intval(substr($FifthField, 2, 2));
                    $PaymentTypeId = substr($FifthField, 4, 1);
                    $FifthFieldAdditionalField = substr($FifthField, 5, 1);
                    $Id = intval(substr($FifthField, 6));


                    $PaymentDate = substr($PaymentDate, 6, 2)."/".substr($PaymentDate, 4, 2)."/".substr($PaymentDate, 0, 4);
                    $CreditDate = substr($CreditDate, 6, 2)."/".substr($CreditDate, 4, 2)."/".substr($CreditDate, 0, 4);

                    if (file_exists($path . $Documentation)) {
                        $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                    } else {
                        $error = true;
                        $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Immagine non presente</div>
                            <div class="clean_row HSpace4"></div>
                        </div>    
                        ';
                    }


                    $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND Id=" . $Id);
                    $FindNumber = mysqli_num_rows($fines);

                    $chkFine = '';
                    $chkPayment = '';
                    if ($FindNumber == 0) {
                        $chkFine = ' table_caption_error';
                    } else {

                        $payments = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $Id . " AND TableId=" . $FifthFieldTable . " AND PaymentFee=" . $FifthFieldPaymentNumber);
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

                        }


                    }

                    $Amount = substr($Amount, 0, (strlen($Amount) - 2)) . "." . substr($Amount, -2);


                    $str_out .= '
                <div class="col-sm-12"> 
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkFile . ' ' . $cont . ' ' . $chkPayment . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $PaymentDate . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $CreditDate . '</div>
                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $FifthField . '</div>
                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $DocumentType . '</div>
                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $Amount . '</div>
                    <div class="table_caption_H col-sm-3' . $chkFine . '">' . $Documentation . '</div>
    
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                }

            }
            fclose($file);
        }
        if (!$error) {
            $str_out .= '
            <div class="col-sm-12">
                <form name="f_import" action="imp_post_exe.php">
                <input type="hidden" name="P" value="imp_post.php">
                <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
                <div class="table_label_H col-sm-12">
                    Comprimi immagini
                    <select name="compress">
                        <option value="0">NO</option>
                        <option value="1">SI</option>
                    </select>
                    <input type="submit" value="Importa" >                           
                </div >
            </div >';
        }

    }else {
        $delimiter = detectDelimiter($path . $ImportFile);
        $cont = 0;

        $chk_Bank = 0;
        $customers = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
        $customer = mysqli_fetch_array($customers);
        $chk_Total = 0;

        $str_out = '
            <div class="col-sm-12">
                <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
                    <div class="clean_row HSpace4" ></div >	
                </div >
            </div >
            <div class="col-sm-12">
                <div class="table_label_H col-sm-1">Riga</div>
                <div class="table_label_H col-sm-1">Data pagamento</div>
                <div class="table_label_H col-sm-1">Data accredito</div>
                <div class="table_label_H col-sm-4">Quinto campo</div>
                <div class="table_label_H col-sm-2">Tipo doc</div>
                <div class="table_label_H col-sm-3">Importo</div>
 
    
                <div class="clean_row HSpace4"></div>	
            </div>
            ';
        if(is_resource($file)) {
            while (!feof($file)) {
                $row = fgets($file);
                if (strlen(trim($row)) != "") {
                    if ($chk_Bank == 0) {
                        $chk_Bank++;
                        $a_row = explode($delimiter, $row);

                    } else {

                        $cont++;
                        $a_row = explode($delimiter, $row);

                        if (trim($a_row[5]) != "") {
                            $FifthField = trim($a_row[5]);

                            if (strlen($FifthField) < 16) {
                                $n_NumDiff = 16 - strlen($FifthField);

                                for ($i = 1; $i <= $n_NumDiff; $i++) {
                                    $FifthField = "0" . $FifthField;
                                }
                            }

                            $DocumentType = $a_row[3];

                            $Amount = str_replace(",", ".", $a_row[7]);
                            $chk_Total += $Amount;
                            $CreditDate = $a_row[1];
                            $PaymentDate = $a_row[2];

                            $chkFine = '';
                            $chkPayment = '';

                            if (($DocumentType == 674 || $DocumentType == 896) && $FifthField != "0000000000000000") {

                                $FifthFieldTable = intval(substr($FifthField, 0, 2));
                                $FifthFieldPaymentNumber = intval(substr($FifthField, 2, 2));
                                $PaymentTypeId = substr($FifthField, 4, 1);
                                $FifthFieldAdditionalField = substr($FifthField, 5, 1);
                                $Id = intval(substr($FifthField, 6));
                                $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND Id=" . $Id);
                                $FindNumber = mysqli_num_rows($fines);


                                if ($FindNumber == 0) {
                                    //$error = true;
                                    $chkFine = ' table_caption_error';
                                    $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Violazione non trovata</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>    
                                ';
                                } else {
                                    $fine = mysqli_fetch_array($fines);
                                    
                                    $payments = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $Id . " AND TableId=" . $FifthFieldTable . " AND PaymentFee=" . $FifthFieldPaymentNumber);
                                    $FindNumber = mysqli_num_rows($payments);

                                    if ($FindNumber > 0) {

                                        //Bug 1695
                                        //inserisco sempre il pagamento ma prima confronto il $FifthField
                                        // se è uguale mettere il pagamento non associato
                                        // se è diverso inserire il pagamento come associato
                                        // non va gestita parte documentale
                                        $r_FinePayment = mysqli_fetch_array($payments);
                                        if (isset($FifthField) && isset($r_FinePayment['FifthField']) && $FifthField == $r_FinePayment['FifthField']) {
                                            
                                            //salvo il pagamento senza associazione con il verbale (FineId = 0)
                                            $chkPayment = '<i class="glyphicon glyphicon-exclamation-sign" style="color:yallow"></i>';
                                            $msgProblem .= '
                                                <div class="col-sm-12">
                                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                                    <div class="table_caption_H col-sm-11 alert-danger">Pagamento già presente con stesso quinto campo ' . $FifthField . " CRON " . $fine['ProtocolId'] . "/" . $fine['ProtocolYear'] . '. Sarà inserito tra i pagamenti da bonificare.</div>
                                                    <div class="clean_row HSpace4"></div>
                                                </div>
                                                ';
                                        } else {
                                            $chkPayment = '<i class="glyphicon glyphicon-exclamation-sign" style="color:green"></i>';
                                            $msgProblem .= '
                                                <div class="col-sm-12">
                                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                                    <div class="table_caption_H col-sm-11 alert-danger">Pagamento già presente con quinto campo diverso ' . $FifthField . " CRON " . $fine['ProtocolId'] . "/" . $fine['ProtocolYear'] . '. Sarà inserito tra i pagamenti associati alla posizione individuata.</div>
                                                    <div class="clean_row HSpace4"></div>
                                                </div>
                                                ';
                                        }
                                    }
                                }
                            }

                            $str_out .= '
                        <div class="col-sm-12"> 
                            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $cont . ' ' . $chkPayment . '</div>
                            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $PaymentDate . '</div>
                            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $CreditDate . '</div>
                            <div class="table_caption_H col-sm-4' . $chkFine . '">' . $FifthField . '</div>
                            <div class="table_caption_H col-sm-2' . $chkFine . '">' . $DocumentType . '</div>
                            <div class="table_caption_H col-sm-3' . $chkFine . '">' . $Amount . '</div>
            
                            <div class="clean_row HSpace4"></div>
                        </div>    
                        ';

                        } else {
                            $str_out .= '
                        <div class="col-sm-12"> 
                            <div class="table_caption_H col-sm-4">Totale righe: ' . $a_row[7] . '</div>
                            <div class="table_caption_H col-sm-4">Importo: ' . $a_row[10] . '</div>
                            <div class="table_caption_H col-sm-4">Controllo Importo: ' . $chk_Total . '</div>
            
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
                <form name="f_import" action="imp_post_exe.php">
                <input type="hidden" name="P" value="imp_post.php">
                <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
                <div class="table_label_H col-sm-12">
                    <input type="submit" value="Importa" >                           
                </div >
            </div >';
        }
    }
}

echo $str_out;


if(strlen($msgProblem)>0){
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $msgProblem;

}

include(INC."/footer.php");