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

$Parametri_posta = $rs->Select('traffic_law.CustomerCharge',"CityId='".$id."'", "CityId");
$ParametriPosta = mysqli_fetch_array($Parametri_posta);

$Parametri_Mod23 = $rs->Select('traffic_law.Customer',"CityId='".$id."'", "CityId");
$ParametriMod23 = mysqli_fetch_array($Parametri_Mod23);

$parametri_ricorsi = $rs->SelectQuery("select traffic_law.JudicialOffice.*,traffic_law.Office.*
       from traffic_law.JudicialOffice
       join traffic_law.Office on traffic_law.JudicialOffice.OfficeId = traffic_law.Office.Id where JudicialOffice.CityId = '$id'");
$Parametriricorsi = mysqli_fetch_array($parametri_ricorsi);
echo $str_out;


$str_out ='

<section style="background-color: #cbe9ea">
  <div class="col-sm-12 BoxRowTitle" style="text-align:center">';

$str_out .='
  </div>
  <ul class="nav nav-tabs" style="background-color: #cbe9ea">
    <li class="active"><a data-toggle="tab" href="#menu1">Parametri solleciti</a></li>
    <li><a data-toggle="tab" href="#menu2">Parametri 126 bis</a></li>
     <li><a data-toggle="tab" href="#menu3">Parametri Mod23</a></li>
    <li><a data-toggle="tab" href="#menu4">Parametri posta</a></li>
     <li><a data-toggle="tab" href="#menu5">Parametri ricorsi</a></li>
  </ul>
  <div class="tab-content" style="background-color: #cbe9ea">
    <div id="menu1" class="tab-pane fade in active">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <div class="clean_row HSpace16"></div>
        <form>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($ProcessingDataPaymentNationals['Automatic']) == 'checked') {
                            $Automatic = 'Si';
                        } else {
                            $Automatic = 'No';
                        }
                        $str_out .='
                       '.$Automatic.'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($ProcessingDataPaymentForeigns['Automatic']) == 'checked') {
                            $Automaticf = 'Si';
                        } else {
                            $Automaticf = 'No';
                        }
                        $str_out .='
                      '.$Automaticf.'
                     </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentNationals['WaitDay'].' 
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentForeigns['WaitDay'].'
                    </div>                              
                </div>
            </div>         
            <div class="clean_row HSpace4"></div>  
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentNationals['RangeDayMin'].'  
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentForeigns['RangeDayMin'].'  
                    </div>                              
                </div>
            </div>        
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentNationals['RangeDayMax'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentForeigns['RangeDayMax'].'
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento normale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentNationals['PaymentDayAccepted'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento normale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentForeigns['PaymentDayAccepted'].'  
                    </div>
                </div>
            </div>    
            <div class="clean_row HSpace4"></div>       
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento ridotti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentNationals['ReducedPaymentDayAccepted'].'  
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento ridotti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingDataPaymentForeigns['ReducedPaymentDayAccepted'].'
                    </div>
                </div>
            </div>  
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <input type="button" onclick="window.location=\'tbl_customer_parametri.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
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
        <form>
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    ';
                        if (ChkCheckButton($ProcessingData126BisNationals['Automatic']) == 'checked') {
                            $Automaticf1 = 'Si';
                        } else {
                            $Automaticf1 = 'No';
                        }
                        $str_out .='
                        '.$Automaticf1.'
                     </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    ';
                        if (ChkCheckButton($ProcessingData126BisForeigns['Automatic']) == 'checked') {
                            $Automaticf2 = 'Si';
                        } else {
                            $Automaticf2 = 'No';
                        }
                        $str_out .='
                        '.$Automaticf2.'
                    </div>                              
                </div>
            </div>       
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingData126BisNationals['RangeDayMin'].'  
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingData126BisForeigns['RangeDayMin'].'  
                    </div>                              
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingData126BisNationals['RangeDayMax'].'  
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingData126BisForeigns['RangeDayMax'].' 
                    </div>                              
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
              <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingData126BisNationals['WaitDay'].'   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingData126BisForeigns['WaitDay'].'  
                    </div>                              
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
              <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Accertarore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingData126BisNationals['ControllerId'].' 
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Accertarore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ProcessingData126BisForeigns['ControllerId'].'
                    </div>                              
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <input type="button" onclick="window.location=\'tbl_customer_parametri.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="menu3" class="tab-pane fade">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <div class="clean_row HSpace16"></div>
        <form>';
                $str_out .='
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Gestore ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$ParametriMod23['NationalMod23LSubject'].'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Gestore ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$ParametriMod23['ForeignMod23LSubject'].'
                    </div>                              
                </div>
            </div>  
            <div class="clean_row HSpace4"></div>         
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriMod23['NationalMod23LCustomerName'].'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriMod23['ForeignMod23LCustomerName'].'  
                    </div>                              
                </div>
            </div>        
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Gestore Stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriMod23['NationalMod23LCustomerSubject'].' 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Gestore Stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriMod23['ForeignMod23LCustomerSubject'].' 
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Indirizzo Stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriMod23['NationalMod23LCustomerAddress'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Indirizzo Stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriMod23['ForeignMod23LCustomerAddress'].'
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>           
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Citta stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriMod23['NationalMod23LCustomerCity'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Citta stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriMod23['ForeignMod23LCustomerCity'].'
                    </div>
                </div>
            </div> 
             
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <input type="button" onclick="window.location=\'tbl_customer_parametri . php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>     
         </form>  
    </div> 
      <div id="menu4" class="tab-pane fade">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <div class="clean_row HSpace16"></div>
        <form name="f_customer4" id="f_customer1" method="post" action="tbl_customer_parametri_upd_exe.php">
          <input name="CityId" type="hidden" value="'.$id.'">
            <input name="steps" type="hidden" value="4">';

                $str_out .='
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Totale forfettario nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalTotalFee'].'  
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Totale forfettario estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$ParametriPosta['ForeignTotalFee'].'
                    </div>                              
                </div>
            </div>  
            <div class="clean_row HSpace4"></div>         
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Spese notifica nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalNotificationFee'].'   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                       Spese notifica estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignNotificationFee'].'  
                    </div>                              
                </div>
            </div>        
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Spese ricerca nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalResearchFee'].'  
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Spese ricerca estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignResearchFee'].'
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Spese Notifica PEC nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalPECNotificationFee'].' 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Spese Notifica PEC estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignPECNotificationFee'].'  
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>           
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Spese Ricerca PEC nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalPECResearchFee'].' 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Spese Ricerca PEC estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignPECResearchFee'].' 
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>     
              <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       bollettino nazionale 
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalPostalType'].' 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       bollettino estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignPostalType'].'  
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>     
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Autorizzazione alla stampa bollettino nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalPostalAuthorization'].'  
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Autorizzazione alla stampa bollettino estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$ParametriPosta['ForeignPostalAuthorization'].' 
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div> 
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Nome Sma nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalSmaName'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Nome Sma estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignSmaName'].' 
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div> 
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Nome Sma nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalSmaName'].' 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Nome Sma estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignSmaName'].'  
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div> 
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                     Autorizzazione Sma nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalSmaAuthorization'].'  
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Autorizzazione Sma estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignSmaAuthorization'].'
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div> 
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                    Spese Sma nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['NationalSmaPayment'].' 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Spese Sma estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$ParametriPosta['ForeignSmaPayment'].' 
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
            
                        <input type="button" onclick="window.location=\'tbl_customer_parametri . php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>     
         </form>  
    </div>
    <div id="menu5" class="tab-pane fade">
        <form>
            <input name="steps" type="hidden" value="5">';

                $str_out .='
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Officio
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['TitleIta'].'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Citta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['City'].' 
                    </div>                              
                </div>
            </div>  
            <div class="clean_row HSpace4"></div>         
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                      Provincia
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['Province'].' 
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                       Indirizzo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['Address'].' 
                    </div>                              
                </div>
            </div>        
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       ZIP
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['ZIP'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Telefono
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['Phone'].'  
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Fax
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['Fax'].' 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Mail
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['Mail'].'
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>           
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       PEC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$Parametriricorsi['PEC'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Web
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$Parametriricorsi['Web'].'
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>     
              <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Disattivazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">';
                        if (ChkCheckButton($Parametriricorsi['Disabled']) == 'checked') {
                        $disabled = 'Si';
                        } else {
                            $disabled = 'No';
                        }
                        $str_out .='
                       '.$disabled.'
                    </div>
                    <div class="col-sm-6 BoxRowCaption"></div>
                    
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>     
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer_parametri . php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>     
         </form>  
    </div>
</div>
</section>
';


echo $str_out;

include(INC."/footer.php");