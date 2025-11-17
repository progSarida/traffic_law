<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$Id= CheckValue('Id','s');
$rs= new CLS_DB();
$customers = $rs->Select('Customer',"CityId='".$Id."'", "CityId");
$customer = mysqli_fetch_array($customers);
$ProcessingDataPaymentNational = $rs->Select('ProcessingDataPaymentNational',"CityId='".$Id."'", "CityId");
$ProcessingDataPaymentNationals = mysqli_fetch_array($ProcessingDataPaymentNational);
$ProcessingDataPaymentForeign = $rs->Select('ProcessingDataPaymentForeign',"CityId='".$Id."'", "CityId");
$ProcessingDataPaymentForeigns = mysqli_fetch_array($ProcessingDataPaymentForeign);
$ProcessingData126BisNational = $rs->Select('ProcessingData126BisNational',"CityId='".$Id."'", "CityId");
$ProcessingData126BisNationals = mysqli_fetch_array($ProcessingData126BisNational);
$ProcessingData126BisForeign = $rs->Select('ProcessingData126BisForeign',"CityId='".$Id."'", "CityId");
$ProcessingData126BisForeigns = mysqli_fetch_array($ProcessingData126BisForeign);
echo $str_out;
$str_out ='

<section style="background-color: #cbe9ea">
  <div class="col-sm-12 BoxRowTitle" style="text-align:center">';
            if (isset($_REQUEST['check']) == 'true') {
                $str_out .='Nuovo Customer';
            } else {
                $str_out .='Modifica Customer';
            }
            $class1 = ''; $inclass1 = '';
            $class2 = ''; $inclass2 = '';
            $class3 = ''; $inclass3 = '';
            $class4 = ''; $inclass4 = '';
            $class5 = ''; $inclass5 = '';
            if ($_REQUEST['tab'] == 1) { $class1 = 'active'; $inclass1 = 'in active'; }
            if ($_REQUEST['tab'] == 2) { $class2 = 'active'; $inclass2 = 'in active'; }
            if ($_REQUEST['tab'] == 3) { $class3 = 'active'; $inclass3 = 'in active'; }
            if ($_REQUEST['tab'] == 4) { $class4 = 'active'; $inclass4 = 'in active'; }
            if ($_REQUEST['tab'] == 5) { $class5 = 'active'; $inclass5 = 'in active'; }

$str_out .='
  </div>
  <ul class="nav nav-tabs" style="background-color: #cbe9ea">
    <li class="'.$class1.'"><a data-toggle="tab" href="#home">Dati ente</a></li>
    <li class="'.$class2.'"><a data-toggle="tab" href="#menu1">Ulteriore dati ente</a></li>
    <li class="'.$class3.'"><a data-toggle="tab" href="#menu2">Dati banca</a></li>
    <li class="'.$class4.'"><a data-toggle="tab" href="#menu3">Parametri solleciti</a></li>
    <li class="'.$class5.'"><a data-toggle="tab" href="#menu4">Parametri 126 bis</a></li>
  </ul>
  <div class="tab-content" style="background-color: #cbe9ea">
    <div id="home" class="tab-pane fade '.$inclass1.'">
        <form name="f_customer1" id="f_customer1" method="post">
            <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="1">
            ';
            if (isset($_REQUEST['check']) == 'true') {
                $str_out .=' <input name="check" type="hidden" value="true">';
            }
            $str_out .='
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                    Nome ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    <input class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto"  name="ManagerName" type="text" value="'.$customer['ManagerName'].'" style="width:28rem" required>   
                
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Settore ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                     <input class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerSector" type="text" value="'.$customer['ManagerSector'].'" style="width:28rem" required> 
                    
                    </div>
                </div> 
            </div> 
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Partita iva ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                     <input class=" frm_field_required frm_field_string" maxlength="16" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerTaxCode" type="text" value="'.$customer['ManagerTaxCode'].'" style="width:28rem" required>         
                    </div> 
                    <div class="col-sm-3 BoxRowLabel">
                        Codice fiscale ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption" required>
                     <input class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerVAT" type="text" value="'.$customer['ManagerVAT'].'" style="width:28rem" required> 
                        
                    </div> 
                </div>              
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Città ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerCity" type="text" value="'.$customer['ManagerCity'].'" style="width:28rem" required>
                        
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Provincia ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                     <input class=" frm_field_required frm_field_string" maxlength="2" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerProvince" type="text" value="'.$customer['ManagerProvince'].'" style="width:28rem" required>
                    
                    </div>
                </div> 
            </div>  
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                     <div class="col-sm-3 BoxRowLabel">
                        Indirizzo ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    <input class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerAddress" type="text" value="'.$customer['ManagerAddress'].'" style="width:28rem" required>                    
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        CAP
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    <input class=" frm_field_required frm_field_string" maxlength="5" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerZIP" type="text" value="'.$customer['ManagerZIP'].'" style="width:28rem" required>         
                    </div>                   
                </div>
            </div>

              <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel" required>
                        Stato ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    <input class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerCountry" type="text" value="'.$customer['ManagerCountry'].'" style="width:28rem" required>  
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Telefono ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                     <input name="ManagerPhone" type="text" value="'.$customer['ManagerPhone'].'" style="width:28rem">
                    </div>
                </div> 
            </div> 
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Fax ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                     <input name="ManagerFax" type="text" value="'.$customer['ManagerFax'].'" style="width:28rem">                   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Mail ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    <input name="ManagerMail" type="text" value="'.$customer['ManagerMail'].'" style="width:28rem">  
                    </div>
                </div> 
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        PEC Ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ManagerPEC" type="text" value="'.$customer['ManagerPEC'].'" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Sito internet Ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">  
                        <input name="ManagerWeb" type="text" value="'.$customer['ManagerWeb'].'" style="width:28rem">
                    </div>
                </div> 
            </div> 
            
              <div class="col-sm-12">
                 <div class="col-sm-12 BoxRow">
                     <div class="col-sm-3 BoxRowLabel">
                         Responsabile Certificazione atto conforme ad originale
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                           <input name="ManagerProcessName" type="text" value="'.$customer['ManagerProcessName'].'" style="width:28rem">
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         Responsabile del servizio Polizia Locale
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                          <input name="ManagerDataEntryName" type="text" value="'.$customer['ManagerDataEntryName'].'" style="width:28rem">
                     </div>
                 </div> 
             </div>  

            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Info addizionali ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    <input name="ManagerInfo" type="text" value="'.$customer['ManagerInfo'].'" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                         Luogo dell\'ente dove si crea il verbale 
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                         <input name="ManagerSignName" type="text" value="'.$customer['ManagerSignName'].'" style="width:28rem">
                     </div>                   
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button type="submit" class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div id="menu1" class="tab-pane fade '.$inclass2.'">
        <form name="f_customer" id="f_customer2" action="tbl_customer_upd_exe.php" method="post">
            <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="2">';
            if (isset($_REQUEST['check']) == 'true') {
                $str_out .=' <input name="check" type="hidden" value="true">';
            }
            $str_out .='
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Codice assegnato per richiesta dati enti esteri
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Reference" maxlength="10" type="text" value="'.$customer['Reference'].'" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Importo verbale forfettario
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                          <input name="LumpSum" type="checkbox" '.ChkCheckButton($customer['LumpSum']).' />
                    </div>
                </div> 
            </div>    
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Nella stampa del verbale deve figurare riferimento comune
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                      <input name="PDFRefPrint" type="checkbox" '.ChkCheckButton($customer['PDFRefPrint']).' />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Codice per generazione quinto campo ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="FifthField" class="frm_field_numeric" maxlength="3" type="number" value="'.$customer['FifthField'].'"  style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;">
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Gestione firma elettronica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="DigitalSignature" type="checkbox" '.ChkCheckButton($customer['DigitalSignature']).' />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Protocollazione ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input name="ExternalRegistration" type="checkbox" '.ChkCheckButton($customer['ExternalRegistration']).' />
                    </div>
                </div> 
            </div>   
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Costi anticipati da sarida per sped nazionali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input name="NationalAnticipateCost" type="checkbox" '.ChkCheckButton($customer['NationalAnticipateCost']).' />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Responsabile immissione dati e procedimento
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ChiefControllerList" type="checkbox" '.ChkCheckButton($customer['ChiefControllerList']).' />
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Costi anticipati da sarida per sped estere
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignAnticipateCost" type="checkbox" '.ChkCheckButton($customer['ForeignAnticipateCost']).' />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Ritorno notifiche
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    <input name="ReturnPlace" type="text" value="'.$customer['ReturnPlace'].'" style="width:28rem">   
                    
                    </div>
                </div> 
            </div>   
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Unione di enti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input name="CityUnion" type="checkbox" '.ChkCheckButton($customer['CityUnion']).' />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Tipo assegnazione importi come Da capitolato
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="FinePaymentSpecificationType" type="checkbox" '.ChkCheckButton($customer['FinePaymentSpecificationType']).' />
                    </div>
                </div> 
            </div> 
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Creazione file pdf con creazione verbali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input name="FinePDFList" type="checkbox" '.ChkCheckButton($customer['FinePDFList']).' />
                    </div>  
                    <div class="col-sm-3 BoxRowLabel">
                        Password MCTC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    <input name="MCTCPassword" type="text" value="'.$customer['MCTCPassword'].'" style="width:28rem">                      
                   </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        User MCTC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                     <input name="MCTCUserName" type="text" value="'.$customer['MCTCUserName'].'" style="width:28rem">   
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
               
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button type="submit" class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="menu2" class="tab-pane fade '.$inclass3.'">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <form name="f_customer" id="f_customer3" action="tbl_customer_upd_exe.php" method="post">
            <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="3">';
            if (isset($_REQUEST['check']) == 'true') {
                $str_out .=' <input name="check" type="hidden" value="true">';
            }
            $str_out .='
             <div class="col-sm-12">
                 <div class="col-sm-12 BoxRow">
                     <div class="col-sm-3 BoxRowLabel">
                         IBAN
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                         <input name="NationalBankIban" type="text" value="'.$customer['NationalBankIban'].'" style="width:28rem">
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         IBAN
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                         <input name="ForeignBankIban" type="text" value="'.$customer['ForeignBankIban'].'" style="width:28rem">    
                     </div>
                 </div>
             </div>
             <div class="col-sm-12">
                 <div class="col-sm-12 BoxRow">
                     <div class="col-sm-3 BoxRowLabel">
                         Nome Banca
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                          <input name="NationalBankName" type="text" value="'.$customer['NationalBankName'].'" style="width:28rem">
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         Nome Banca
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                          <input name="ForeignBankName" type="text" value="'.$customer['ForeignBankName'].'" style="width:28rem">
                     </div>
                 </div> 
             </div>  
             <div class="col-sm-12">
                 <div class="col-sm-12 BoxRow">
                     <div class="col-sm-3 BoxRowLabel">
                         Gestione pagamenti sarida
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                        <input name="NationalBankMgmt" type="checkbox" '.ChkCheckButton($customer['NationalBankMgmt']).' />
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         Gestione pagamenti sarida
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                         <input name="ForeignBankMgmt" type="checkbox" '.ChkCheckButton($customer['ForeignBankMgmt']).' />
                     </div>                     
                 </div> 
             </div>
             <div class="col-sm-12">
                 <div class="col-sm-12 BoxRow">
                     <div class="col-sm-3 BoxRowLabel">
                         Intestatario CC  ITA
                     </div>
                     <div class="col-sm-3 BoxRowCaption">

                      <input name="NationalBankOwner" type="text" value="'.$customer['NationalBankOwner'].'" style="width:28rem">
                       
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         CC ESTERO
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                       
                         <input name="ForeignBankOwner" type="text" value="'.$customer['ForeignBankOwner'].'" style="width:28rem">
                     </div>
                 </div>
             </div>
             <div class="col-sm-12">
                 <div class="col-sm-12 BoxRow">
                     <div class="col-sm-3 BoxRowLabel">
                         CC
                     </div>
                     <div class="col-sm-3 BoxRowCaption">               
                          <input name="NationalBankAccount" type="text" value="'.$customer['NationalBankAccount'].'" style="width:28rem">
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         CC
                     </div>
                     <div class="col-sm-3 BoxRowCaption">
                     <input name="ForeignBankAccount" type="text" value="'.$customer['ForeignBankAccount'].'" style="width:28rem">    
                     </div>
                 </div> 
             </div>  
             <div class="col-sm-12">
                 <div class="col-sm-12 BoxRow">
                     <div class="col-sm-3 BoxRowLabel">
                         SWIFT
                     </div>
                     <div class="col-sm-3 BoxRowCaption">      
                         <input name="NationalBankSwift" type="text" value="'.$customer['NationalBankSwift'].'" style="width:28rem">
                     </div>
                     <div class="col-sm-3 BoxRowLabel">
                         SWIFT
                     </div>
                     <div class="col-sm-3 BoxRowCaption"> 
                           <input name="ForeignBankSwift" type="text" value="'.$customer['ForeignBankSwift'].'" style="width:28rem">            
                     </div>
                 </div> 
             </div>                 
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button type="submit" class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>   
        </form>
    </div>
    <div id="menu3" class="tab-pane fade '.$inclass4.'">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <form name="f_customer4" id="f_customer4" method="post">
          <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="4">';
            if (isset($_REQUEST['check']) == 'true') {
                $str_out .=' <input name="check" type="hidden" value="true">';
            }
            $str_out .='
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input name="Automatic" type="checkbox" style="width:28rem" '.ChkCheckButton($ProcessingDataPaymentNationals['Automatic']).'>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input name="AutomaticForeign" type="checkbox" style="width:28rem" '.ChkCheckButton($ProcessingDataPaymentForeigns['Automatic']).'> 
                     </div>
                </div>
            </div>
             <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="WaitDay" id="WaitDay" data-bv-notempty="true" data-bv-notempty-message="Richiesto" class="frm_field_required frm_field_numeric" type="text" value="'.$ProcessingDataPaymentNationals['WaitDay'].'" style="width:28rem">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="WaitDayForeign" id="WaitDayForeign" data-bv-notempty="true" data-bv-notempty-message="Richiesto" class="frm_field_required frm_field_numeric" type="text" value="'.$ProcessingDataPaymentForeigns['WaitDay'].'" style="width:28rem">   
                    </div>                              
                </div>
            </div>           
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RangeDayMin" type="number" data-bv-notempty="true" data-bv-notempty-message="Richiesto" value="'.$ProcessingDataPaymentNationals['RangeDayMin'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_required frm_field_numeric">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RangeDayMinForeign" type="number" data-bv-notempty="true" data-bv-notempty-message="Richiesto" value="'.$ProcessingDataPaymentForeigns['RangeDayMin'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_required frm_field_numeric">   
                    </div>                              
                </div>
            </div>        
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RangeDayMax" type="number" data-bv-notempty="true" data-bv-notempty-message="Richiesto" value="'.$ProcessingDataPaymentNationals['RangeDayMax'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_required frm_field_numeric">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RangeDayMaxForeign" type="number" data-bv-notempty="true" data-bv-notempty-message="Richiesto" value="'.$ProcessingDataPaymentForeigns['RangeDayMax'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_required frm_field_numeric">   
                    </div>
                </div>
            </div>
             <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento normale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PaymentDayAccepted" id="PaymentDayAccepted" type="text" value="'.$ProcessingDataPaymentNationals['PaymentDayAccepted'].'" style="width:28rem">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento normale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PaymentDayAcceptedForeign" id="PaymentDayAcceptedForeign" type="text" value="'.$ProcessingDataPaymentForeigns['PaymentDayAccepted'].'" style="width:28rem">   
                    </div>
                </div>
            </div>           
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento ridotti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ReducedPaymentDayAccepted" id="ReducedPaymentDayAccepted" type="text" value="'.$ProcessingDataPaymentNationals['ReducedPaymentDayAccepted'].'" style="width:28rem">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento ridotti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ReducedPaymentDayAcceptedForeign" id="ReducedPaymentDayAcceptedForeign" type="text" value="'.$ProcessingDataPaymentForeigns['ReducedPaymentDayAccepted'].'" style="width:28rem">   
                    </div>
                </div>
            </div>  
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>     
         </form>  
    </div>    
    <div id="menu4" class="tab-pane fade '.$inclass5.'">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <form name="f_customer5" id="f_customer5" method="post">
            <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="5">';
            if (isset($_REQUEST['check']) == 'true') {
                $str_out .=' <input name="check" type="hidden" value="true">';
            }
            $str_out .='
             <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Automatic" id="Automatic" type="checkbox" value="on" style="width:28rem" '.ChkCheckButton($ProcessingData126BisNationals['Automatic']).'>
                     </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="AutomaticForeign" value="on" type="checkbox" style="width:28rem" '.ChkCheckButton($ProcessingData126BisForeigns['Automatic']).'>
                    </div>                              
                </div>
            </div>       
             <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RangeDayMin" type="number" data-bv-notempty="true" data-bv-notempty-message="Richiesto" class="frm_field_required frm_field_numeric" value="'.$ProcessingData126BisNationals['RangeDayMin'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RangeDayMinForeign" type="number" data-bv-notempty="true" data-bv-notempty-message="Richiesto" class="frm_field_required frm_field_numeric" value="'.$ProcessingData126BisForeigns['RangeDayMin'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;">   
                    </div>                              
                </div>
            </div> 
             <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RangeDayMax" type="number" data-bv-notempty="true" data-bv-notempty-message="Richiesto" class="frm_field_required frm_field_numeric" value="'.$ProcessingData126BisNationals['RangeDayMax'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RangeDayMaxForeign" type="number" data-bv-notempty="true" data-bv-notempty-message="Richiesto" class="frm_field_required frm_field_numeric" value="'.$ProcessingData126BisForeigns['RangeDayMax'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;">   
                    </div>                              
                </div>
            </div>
              <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="WaitDay" type="text" data-bv-notempty="true" data-bv-notempty-message="Richiesto" id="WaitDay5" class="frm_field_required frm_field_numeric" value="'.$ProcessingData126BisNationals['WaitDay'].'" style="width:28rem">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="WaitDayForeign" id="WaitDayForeign5" data-bv-notempty="true" data-bv-notempty-message="Richiesto" class="frm_field_required frm_field_numeric" type="text" value="'.$ProcessingData126BisForeigns['WaitDay'].'" style="width:28rem">   
                    </div>                              
                </div>
            </div>
              <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Accertarore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ControllerId" type="text" value="'.$ProcessingData126BisNationals['ControllerId'].'" style="width:28rem">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Accertarore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ControllerIdForeign" type="text" value="'.$ProcessingData126BisForeigns['ControllerId'].'" style="width:28rem">   
                    </div>                              
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button class="btn btn-default" style="margin-top:1rem;">Modifica</button>
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
?>

<script type="text/javascript">
$(document).ready(function () {

    $('#f_customer5').bootstrapValidator({}).on('success.form.bv', function (e) {
            e.preventDefault();
            var form = $("#f_customer5").serialize();
            console.log(form);
            $.ajax({
                url: 'tbl_customer_upd_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: form,
                success: function (data) {
                    console.log(data.check);
                    if (data.check == "true") {
                        window.location.href = "tbl_customer_upd.php?Id="+data.Id+"&check=true&tab="+data.tab+"";
                    } else {
                        window.location.href = "tbl_customer_upd.php?Id="+data.Id+"&tab="+data.tab+"";
                    }
                },
            });
        });

        $('#f_customer4').bootstrapValidator({}).on('success.form.bv', function (e) {
            e.preventDefault();
            var form = $("#f_customer4").serialize();
            $.ajax({
                url: 'tbl_customer_upd_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: form,
                success: function (data) {
                    if (data.check == 'true') {
                        window.location.href = "tbl_customer_upd.php?Id="+data.Id+"&check=true&tab="+data.tab+"";
                    } else {
                        window.location.href = "tbl_customer_upd.php?Id="+data.Id+"&tab="+data.tab+"";
                    }
                },
            });
        });

        $('#f_customer1').bootstrapValidator({}).on('success.form.bv', function (e) {
            e.preventDefault();
            var form = $("#f_customer1").serialize();
            $.ajax({
                url: 'tbl_customer_upd_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: form,
                success: function (data) {
                    if (data.check == 'true') {
                        window.location.href = "tbl_customer_upd.php?Id="+data.Id+"&check=true&tab="+data.tab+"";
                    } else {
                        window.location.href = "tbl_customer_upd.php?Id="+data.Id+"&tab="+data.tab+"";
                    }
                },
            });
        });
});
</script>

<?php

include(INC."/footer.php");

?>

<script>
    $('document').ready(function () {



        $("input[type=text].frm_field_date").keyup(function (e) {

            this.value = this.value.replace(/[^0-9\/]/g,'');

            var textSoFar = $(this).val();

            if (textSoFar.length == 2 || textSoFar.length == 5) {
                $(this).val(textSoFar + "/");
            }
            else if (textSoFar.length > 10) {
                $(this).val(textSoFar.substr(0,10));
            }
        });

        $("input[type=text].frm_field_time").keyup(function (e) {

            var textSoFar = $(this).val();

            if (textSoFar.length == 2) {
                $(this).val(textSoFar + ":");
            }
            else if (textSoFar.length > 5) {
                $(this).val(textSoFar.substr(0,5));
            }
        });


        $("input[type=text].frm_field_numeric").keyup(function (e) {
            this.value = this.value.replace(/[^0-9\.\,]/g,'').replace(',','.');
        });

        $("input[type=text].frm_field_currency").keyup(function (e) {
            this.value = this.value.replace(/[^0-9\.\,]/g,'').replace(',','.');
        });


    });

</script>

<script type="text/javascript">
function setInputFilter(textbox, inputFilter) {
  ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
    textbox.addEventListener(event, function() {
      if (inputFilter(this.value)) {
        this.oldValue = this.value;
        this.oldSelectionStart = this.selectionStart;
        this.oldSelectionEnd = this.selectionEnd;
      } else if (this.hasOwnProperty("oldValue")) {
        this.value = this.oldValue;
        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
      }
    });
  });
}

setInputFilter(document.getElementById("WaitDay"), function(value) {
  return /^\d*\.?\d*$/.test(value);
});
setInputFilter(document.getElementById("WaitDayForeign"), function(value) {
  return /^\d*\.?\d*$/.test(value);
});
setInputFilter(document.getElementById("PaymentDayAccepted"), function(value) {
  return /^\d*\.?\d*$/.test(value);
});
setInputFilter(document.getElementById("PaymentDayAcceptedForeign"), function(value) {
  return /^\d*\.?\d*$/.test(value);
});
setInputFilter(document.getElementById("ReducedPaymentDayAccepted"), function(value) {
  return /^\d*\.?\d*$/.test(value);
});
setInputFilter(document.getElementById("ReducedPaymentDayAcceptedForeign"), function(value) {
  return /^\d*\.?\d*$/.test(value);
});
setInputFilter(document.getElementById("WaitDay5"), function(value) {
  return /^\d*\.?\d*$/.test(value);
});
setInputFilter(document.getElementById("WaitDayForeign5"), function(value) {
  return /^\d*\.?\d*$/.test(value);
});

</script>
