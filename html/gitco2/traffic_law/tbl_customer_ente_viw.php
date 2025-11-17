<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_table.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');





$id= $_GET['CityId'];

$rs= new CLS_DB();

$customers = $rs->Select('Customer',"CityId='$id'", "CityId");
$customer = mysqli_fetch_array($customers);
$ProcessingDataPaymentNational = $rs->Select('ProcessingDataPaymentNational',"CityId='".$id."'", "CityId");
$ProcessingDataPaymentNationals = mysqli_fetch_array($ProcessingDataPaymentNational);
$ProcessingDataPaymentForeign = $rs->Select('ProcessingDataPaymentForeign',"CityId='".$id."'", "CityId");
$ProcessingDataPaymentForeigns = mysqli_fetch_array($ProcessingDataPaymentForeign);
$ProcessingData126BisNational = $rs->Select('ProcessingData126BisNational',"CityId='".$id."'", "CityId");
$ProcessingData126BisNationals = mysqli_fetch_array($ProcessingData126BisNational);
$ProcessingData126BisForeign = $rs->Select('ProcessingData126BisForeign',"CityId='".$id."'", "CityId");
$ProcessingData126BisForeigns = mysqli_fetch_array($ProcessingData126BisForeign);
echo $str_out;

$str_out ='

<section style="background-color: #cbe9ea">
  <div class="col-sm-12 BoxRowTitle" style="text-align:center">';

$str_out .='
  </div>
  <ul class="nav nav-tabs" style="background-color: #cbe9ea">
    <li class="active"><a data-toggle="tab" href="#home">Dati ufficio</a></li>
    <li><a data-toggle="tab" href="#menu1">Ulteriori dati</a></li>
    <li><a data-toggle="tab" href="#menu2">Dati banca</a></li>
  </ul>
  <div class="tab-content" style="background-color: #cbe9ea">
    <div id="home" class="tab-pane fade in active">
        <form name="f_customer1" id="f_customer1" method="post">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                    Ubicazione ufficio (Comune)
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerName'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Settore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerSector'].'
                    </div>
                </div> 
            </div> 
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                         Indirizzo ufficio
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$customer['ManagerAddress'].'   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Provincia
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerProvince'].'
                    </div>
                </div> 
            </div>  
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                     <div class="col-sm-3 BoxRowLabel">
                          Fax ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerFax'].'                 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        CAP
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerZIP'].'        
                    </div>                   
                </div>
            </div>
                <div class="clean_row HSpace4"></div>
              <div class="col-sm-12">
                <div class="col-sm-12 ">
                    <div class="col-sm-3 BoxRowLabel">
                        PEC  ufficio
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerPEC'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Telefono ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerPhone'].'
                    </div>
                </div> 
            </div> 
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    
                    <div class="col-sm-3 BoxRowLabel">
                        Mail ente
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        '.$customer['ManagerMail'].'
                    </div>
                </div> 
            </div>
   
            <div class="clean_row HSpace4"></div>
              <div class="col-sm-12">
                 <div class="col-sm-12 ">
                     <div class="col-sm-3 BoxRowLabel">
                         Responsabile Certificazione atto conforme ad originale
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerProcessName'].'
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         Responsabile del servizio Polizia Locale
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerDataEntryName'].'
                     </div>
                 </div> 
             </div>  
             <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Info addizionali ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerInfo'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                         Luogo dell\'ente dove si crea il verbale 
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ManagerSignName'].'
                     </div>                   
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <input type="button" onclick="window.location=\'tbl_customer_ente.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div id="menu1" class="tab-pane fade">
        <form name="f_customer" id="f_customer2" action="tbl_customer_upd_exe.php" method="post">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Codice assegnato per richiesta dati enti esteri
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['Reference'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Importo verbale forfettario
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['LumpSum']) == 'checked') {
                            $LumpSum = 'Si';
                        } else {
                            $LumpSum = 'No';
                        }
                        $str_out .=' 
                        '.$LumpSum.'
                    </div>
                </div> 
            </div>    
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Nella stampa del verbale deve figurare riferimento comune
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['PDFRefPrint']) == 'checked') {
                            $PDFRefPrint = 'Si';
                        } else {
                            $PDFRefPrint = 'No';
                        }
                        $str_out .=' 
                      '.$PDFRefPrint.'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Codice per generazione quinto campo ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['FifthField'].'
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Gestione firma elettronica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['DigitalSignature']) == 'checked') {
                            $DigitalSignature = 'Si';
                        } else {
                            $DigitalSignature = 'No';
                        }
                        $str_out .=' 
                    '.$DigitalSignature.'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Protocollazione ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['ExternalRegistration']) == 'checked') {
                            $ExternalRegistration = 'Si';
                        } else {
                            $ExternalRegistration = 'No';
                        }
                        $str_out .=' 
                       '.$ExternalRegistration.'
                    </div>
                </div> 
            </div>   
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Costi anticipati da sarida per sped nazionali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['NationalAnticipateCost']) == 'checked') {
                            $NationalAnticipateCost = 'Si';
                        } else {
                            $NationalAnticipateCost = 'No';
                        }
                        $str_out .=' 
                       '.$NationalAnticipateCost.'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Responsabile immissione dati e procedimento
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['ChiefControllerList']) == 'checked') {
                            $ChiefControllerList = 'Si';
                        } else {
                            $ChiefControllerList = 'No';
                        }
                        $str_out .=' 
                        '.$ChiefControllerList.'
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Costi anticipati da sarida per sped estere
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['ForeignAnticipateCost']) == 'checked') {
                            $ForeignAnticipateCost = 'Si';
                        } else {
                            $ForeignAnticipateCost = 'No';
                        }
                        $str_out .=' 
                        '.$ForeignAnticipateCost.'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Ritorno notifiche
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ReturnPlace'].'   
                    </div>
                </div> 
            </div>   
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Unione di enti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['CityUnion']) == 'checked') {
                            $CityUnion = 'Si';
                        } else {
                            $CityUnion = 'No';
                        }
                        $str_out .='
                       '.$CityUnion.'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Tipo assegnazione importi come Da capitolato
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['FinePaymentSpecificationType']) == 'checked') {
                            $FinePaymentSpecificationType = 'Si';
                        } else {
                            $FinePaymentSpecificationType = 'No';
                        }
                        $str_out .='
                        '.$FinePaymentSpecificationType.'
                    </div>
                </div> 
            </div> 
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Creazione file pdf con creazione verbali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['FinePDFList']) == 'checked') {
                            $FinePDFList = 'Si';
                        } else {
                            $FinePDFList = 'No';
                        }
                        $str_out .='
                       '.$FinePDFList.' 
                    </div>  
                    <div class="col-sm-3 BoxRowLabel">
                        Password MCTC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['MCTCPassword'].'                     
                   </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        User MCTC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$customer['MCTCUserName'].'  
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Validazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                         if (ChkCheckButton($customer['Validation']) == 'checked') {
                            $validation = 'Si';
                        } else {
                             $validation = 'No';
                        }
                        $str_out .='
                       '.$validation.' 
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
               
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <input type="button" onclick="window.location=\'tbl_customer.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="menu2" class="tab-pane fade">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <div class="clean_row HSpace16"></div>
        <form name="f_customer" id="f_customer3" action="tbl_customer_upd_exe.php" method="post">
             <div class="col-sm-12">
                 <div class="col-sm-12">
                     <div class="col-sm-3 BoxRowLabel">
                         IBAN
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        '.$customer['NationalBankIban'].'
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         IBAN
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ForeignBankIban'].'   
                     </div>
                 </div>
             </div>
             <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                 <div class="col-sm-12">
                     <div class="col-sm-3 BoxRowLabel">
                         Nome Banca
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        '.$customer['NationalBankName'].'
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         Nome Banca
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ForeignBankName'].'
                     </div>
                 </div> 
             </div>  
             <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                 <div class="col-sm-12">
                     <div class="col-sm-3 BoxRowLabel">
                         Gestione pagamenti sarida
                     </div>
                     <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($customer['NationalBankMgmt']) == 'checked') {
                            $NationalBankMgmt = 'Si';
                        } else {
                            $NationalBankMgmt = 'No';
                        }
                        $str_out .='
                        '.$NationalBankMgmt.'
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         Gestione pagamenti sarida
                     </div>
                     <div class="col-sm-3 BoxRowCaption">';
                            if (ChkCheckButton($customer['ForeignBankMgmt']) == 'checked') {
                                $ForeignBankMgmt = 'Si';
                            } else {
                                $ForeignBankMgmt = 'No';
                            }
                            $str_out .='
                        '.$ForeignBankMgmt.'
                     </div>                     
                 </div> 
             </div>
             <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                 <div class="col-sm-12">
                     <div class="col-sm-3 BoxRowLabel">
                         Intestatario CC  ITA
                     </div>
                     <div class="col-sm-3 BoxRowCaption">

                      '.$customer['NationalBankOwner'].'
                       
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         CC ESTERO
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                       
                        '.$customer['ForeignBankOwner'].'
                     </div>
                 </div>
             </div>
             <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                 <div class="col-sm-12">
                     <div class="col-sm-3 BoxRowLabel">
                         CC
                     </div>
                     <div class="col-sm-3 BoxRowCaption">               
                        '.$customer['NationalBankAccount'].'
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         CC
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        '.$customer['ForeignBankAccount'].'    
                     </div>
                 </div> 
             </div>  
             <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                 <div class="col-sm-12">
                     <div class="col-sm-3 BoxRowLabel">
                         SWIFT
                     </div>
                     <div class="col-sm-3 BoxRowCaption">      
                        '.$customer['NationalBankSwift'].'
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         SWIFT
                     </div>
                     <div class="col-sm-3 BoxRowCaption"> 
                        '.$customer['ForeignBankSwift'].'          
                     </div>
                 </div> 
             </div>                 
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <input type="button" onclick="window.location=\'tbl_customer.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>   
        </form>
    </div>   
  </div>
</div>
</section>
';


echo $str_out;

include(INC."/footer.php");