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
        	  	<div class="col-sm-12 BoxRow" >
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				Modifica articolo
					</div>
  				</div>
            </div> 
        </div> 
    	<div class="row-fluid" style="margin-top:2rem;">
    	     
 
    	    <div class="col-sm-12">
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-2 BoxRowLabel">
        				Blasone
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				'.$customer['Blazon'].'
					</div>
					<div class="col-sm-2 BoxRowLabel">
        	            Nome aggiuntivo del responsabile
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				'.$customer['ManagerAdditionalName'].'
					</div>
					<div class="col-sm-2 BoxRowLabel">
        				Nome responsabile
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				'.$customer['ManagerName'].'
					</div>
  				</div> 
            </div>    
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                       Settore Manager
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerSector'].'
                    </div>

                    <div class="col-sm-2 BoxRowLabel">
                       Codice fiscale del gestore
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerTaxCode'].'
                    </div>

                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile IVA
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerVAT'].'
                    </div>

                </div> 
            </div>
    	    <div class="col-sm-12">
	        	<div class="col-sm-12 BoxRow">
                                        <div class="col-sm-2 BoxRowLabel">
                        Responsabile della città
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerCity'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Direttore Provinciale
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerProvince'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Indirizzo di Responsabile 
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerAddress'].'
                    </div>



  				</div> 
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile Nazione
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerCountry'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile Telefono
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerPhone'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile Fax
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerFax'].'
                    </div>


                </div> 
            </div> 
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile codice postale
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerZIP'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile Email
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerMail'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile PEC
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerPEC'].'
                    </div>
                </div> 
            </div>

            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">

                <div class="col-sm-2 BoxRowLabel">
                        Responsabile  web
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerWeb'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                         Responsabile  di informazioni
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerInfo'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile del processo
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerProcessName'].'
                    </div>

                </div> 
            </div>

            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Nome inserimento dati
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerDataEntryName'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Nome del firma
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ManagerSignName'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        NationalBankOwner
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['NationalBankOwner'].'
                    </div>                

                </div> 
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">

                    <div class="col-sm-2 BoxRowLabel">
                        Banca verbali italiani
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['NationalBankName'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        C/C
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['NationalBankAccount'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        IBAN
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['NationalBankIban'].'
                    </div>


                </div> 
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                                    <div class="col-sm-2 BoxRowLabel">
                       Codice SWIFT
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['NationalBankSwift'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Intestatario
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ForeignBankOwner'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Banca verbali esteri
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ForeignBankName'].'
                    </div>


                </div> 
            </div>    
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
            <div class="col-sm-2 BoxRowLabel">
                        C/C  
            </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ForeignBankAccount'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        IBAN
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ForeignBankIban'].'
                    </div>  
                    <div class="col-sm-2 BoxRowLabel">
                        Codice richiesta ente
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['Reference'].'
                    </div>
                </div>
            </div>


       	    <div class="col-sm-12">
	        	<div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Pagamenti italiani C/Sarida
                        <input name="NationalBankMgmt" type="checkbox" '.ChkCheckButton($customer['NationalBankMgmt']).' />
                    </div>
        			<div class="col-sm-2 BoxRowLabel">
                        Pagamenti esteri C/Sarida
        				<input name="ForeignBankMgmt" type="checkbox" '.ChkCheckButton($customer['ForeignBankMgmt']).' />
				    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Importo verbale forfettario
                        <input name="LumpSum" type="checkbox" '.ChkCheckButton($customer['LumpSum']).' />
                    </div>        			
                    <div class="col-sm-2 BoxRowLabel">
                        Stampa rif comune nei verbali
                        <input name="PDFRefPrint" type="checkbox" '.ChkCheckButton($customer['PDFRefPrint']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Protocollazione ente
                        <input name="ExternalRegistration" type="checkbox" '.ChkCheckButton($customer['ExternalRegistration']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Costi nazionali anticipati Sarida
                        <input name="NationalAnticipateCost" type="checkbox" '.ChkCheckButton($customer['NationalAnticipateCost']).' />
                    </div>
  				</div> 
            </div>            
            
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Costi esteri anticipati Sarida
                        <input name="ForeignAnticipateCost" type="checkbox" '.ChkCheckButton($customer['ForeignAnticipateCost']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Unione di enti
                        <input name="CityUnion" type="checkbox" '.ChkCheckButton($customer['CityUnion']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Tipo assegnazione importi come Da capitolato
                        <input name="FinePaymentSpecificationType" type="checkbox" '.ChkCheckButton($customer['FinePaymentSpecificationType']).' />
                    </div>                  
                    <div class="col-sm-2 BoxRowLabel">
                        Creazione file pdf con creazione verbali
                        <input name="FinePDFList" type="checkbox" '.ChkCheckButton($customer['FinePDFList']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Responsabile immissione dati e procedimento
                        <input name="ChiefControllerList" type="checkbox" '.ChkCheckButton($customer['ChiefControllerList']).' />
                    </div>
                </div> 
            </div> 

            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Codice per generazione quinto campo ente  
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['FifthField'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        SWIFT
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ForeignBankSwift'].'
                    </div>  
                    <div class="col-sm-2 BoxRowLabel">
                        Ritorno notifiche
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['ReturnPlace'].'
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Username MCTC  
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['MCTCUserName'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Password MCTC
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['MCTCPassword'].'
                    </div>  
                    <div class="col-sm-2 BoxRowLabel">
                        Data password MCTC
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$customer['MCTCDate'].'
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
 
        </div>                                                                    
';


echo $str_out;

include(INC."/footer.php");