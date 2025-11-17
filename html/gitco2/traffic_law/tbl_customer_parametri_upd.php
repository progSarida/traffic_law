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

$Parametri_posta = $rs->Select('traffic_law.CustomerCharge',"CityId='".$Id."'", "CityId");
$ParametriPosta = mysqli_fetch_array($Parametri_posta);

$Parametri_Mod23 = $rs->Select('traffic_law.Customer',"CityId='".$Id."'", "CityId");
$ParametriMod23 = mysqli_fetch_array($Parametri_Mod23);
$parametri_ricorsi = $rs->SelectQuery("select traffic_law.JudicialOffice.*,traffic_law.Office.*
       from traffic_law.JudicialOffice
       join traffic_law.Office on traffic_law.JudicialOffice.OfficeId = traffic_law.Office.Id where JudicialOffice.CityId = '$Id'");
$Parametriricorsi = mysqli_fetch_array($parametri_ricorsi);


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
    <li class="'.$class1.'"><a data-toggle="tab" href="#menu1">Parametri solleciti</a></li>
    <li class="'.$class2.'"><a data-toggle="tab" href="#menu2">Parametri 126 bis</a></li>
    <li class="'.$class3.'"><a data-toggle="tab" href="#menu3">Parametri Mod23</a></li>
    <li class="'.$class4.'"><a data-toggle="tab" href="#menu4">Parametri posta</a></li>
     <li class="'.$class5.'"><a data-toggle="tab" href="#menu5">Parametri ricorsi</a></li>
  </ul>
  <div class="tab-content" style="background-color: #cbe9ea">

    <div id="menu1" class="tab-pane fade '.$inclass1.'">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <div class="clean_row HSpace16"></div>
        <form name="f_customer4" id="f_customer1" method="post" action="tbl_customer_parametri_upd_exe.php">
          <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="1">';

                $str_out .='
            <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>         
            <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>           
            <div class="col-sm-12">
                <div class="col-sm-12">
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
                        <input type="button" onclick="window.location=\'tbl_customer_parametri.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>     
         </form>  
    </div>    
    <div id="menu2" class="tab-pane fade '.$inclass2.'">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <div class="clean_row HSpace16"></div>
        <form name="f_customer5" id="f_customer2" method="post" action="tbl_customer_parametri_upd_exe.php">
            <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="2">';

                $str_out .='
             <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>
              <div class="col-sm-12">
                <div class="col-sm-12">
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
            <div class="clean_row HSpace4"></div>
              <div class="col-sm-12">
                <div class="col-sm-12">
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
                        <input type="button" onclick="window.location=\'tbl_customer_parametri.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>
        </form>
    </div>
     <div id="menu3" class="tab-pane fade '.$inclass3.'">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <div class="clean_row HSpace16"></div>
        <form name="f_customer4" id="f_customer1" method="post" action="tbl_customer_parametri_upd_exe.php">
          <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="3">';

                $str_out .='
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Gestore ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="NationalMod23LSubject" class="frm_field_string" type="text" value="'.$ParametriMod23['NationalMod23LSubject'].'" style="width:28rem">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Gestore ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignMod23LSubject" class="frm_field_string" type="text" value="'.$ParametriMod23['ForeignMod23LSubject'].'" style="width:28rem">   
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
                        <input name="NationalMod23LCustomerName" type="text" value="'.$ParametriMod23['NationalMod23LCustomerName'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_string">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignMod23LCustomerName" type="text" value="'.$ParametriMod23['ForeignMod23LCustomerName'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class=" frm_field_string">   
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
                        <input name="NationalMod23LCustomerSubject" type="text" value="'.$ParametriMod23['NationalMod23LCustomerSubject'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_string">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Gestore Stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignMod23LCustomerSubject" type="text"  value="'.$ParametriMod23['ForeignMod23LCustomerSubject'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_string">   
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
                        <input name="NationalMod23LCustomerAddress"  type="text" value="'.$ParametriMod23['NationalMod23LCustomerAddress'].'" style="width:28rem" class="frm_field_string">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Indirizzo Stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignMod23LCustomerAddress"  type="text" value="'.$ParametriMod23['ForeignMod23LCustomerAddress'].'" style="width:28rem" class="frm_field_string">   
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
                        <input name="NationalMod23LCustomerCity" type="text" value="'.$ParametriMod23['NationalMod23LCustomerCity'].'" style="width:28rem" class="frm_field_string">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Citta stampatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignMod23LCustomerCity"  type="text" value="'.$ParametriMod23['ForeignMod23LCustomerCity'].'" style="width:28rem" class="frm_field_string">   
                    </div>
                </div>
            </div> 
             
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer_parametri.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>     
         </form>  
    </div>   
      <div id="menu4" class="tab-pane fade '.$inclass4.'">
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><center><b style="font-size: 15px">Dati nazionali</b></center></p>
        </div> 
        <div class="col-sm-6 BoxRowLabel" style="height: 4rem;text-transform: uppercase">
            <p><b><center style="font-size: 15px">Dati stranieri</center></b></p>
        </div>
        <div class="clean_row HSpace16"></div>
        <form name="f_customer4" id="f_customer1" method="post" action="tbl_customer_parametri_upd_exe.php">
          <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="4">';

                $str_out .='
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Totale forfettario nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="NationalTotalFee" class="frm_field_numeric" type="text" value="'.$ParametriPosta['NationalTotalFee'].'" style="width:28rem">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Totale forfettario estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignTotalFee" class="frm_field_numeric" type="text" value="'.$ParametriPosta['ForeignTotalFee'].'" style="width:28rem">   
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
                        <input name="NationalNotificationFee" type="text" value="'.$ParametriPosta['NationalNotificationFee'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_numeric">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                       Spese notifica estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignNotificationFee" type="text" value="'.$ParametriPosta['ForeignNotificationFee'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class=" frm_field_numeric">   
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
                        <input name="NationalResearchFee" type="text" value="'.$ParametriPosta['NationalResearchFee'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_numeric">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Spese ricerca estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignResearchFee" type="text"  value="'.$ParametriPosta['ForeignResearchFee'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_numeric">   
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
                        <input name="NationalPECNotificationFee" type="text" value="'.$ParametriPosta['NationalPECNotificationFee'].'" style="width:28rem" class="frm_field_numeric">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Spese Notifica PEC estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignPECNotificationFee"  type="text" value="'.$ParametriPosta['ForeignPECNotificationFee'].'" style="width:28rem" class="frm_field_numeric">   
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
                        <input name="NationalPECResearchFee" type="text" value="'.$ParametriPosta['NationalPECResearchFee'].'" style="width:28rem" class="frm_field_numeric">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Spese Ricerca PEC estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignPECResearchFee" type="text" value="'.$ParametriPosta['ForeignPECResearchFee'].'" style="width:28rem" class="frm_field_numeric">   
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
                        <input name="NationalPostalType" type="text" value="'.$ParametriPosta['NationalPostalType'].'" style="width:28rem" class="frm_field_numeric">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       bollettino estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignPostalType" type="text" value="'.$ParametriPosta['ForeignPostalType'].'" style="width:28rem" class="frm_field_numeric">   
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
                        <input name="NationalPostalAuthorization" type="text" value="'.$ParametriPosta['NationalPostalAuthorization'].'" style="width:28rem" class="frm_field_string">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Autorizzazione alla stampa bollettino estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignPostalAuthorization" type="text" value="'.$ParametriPosta['ForeignPostalAuthorization'].'" style="width:28rem" class="frm_field_string">   
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
                        <input name="NationalSmaName" type="text" value="'.$ParametriPosta['NationalSmaName'].'" style="width:28rem" class="frm_field_string">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Nome Sma estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignSmaName" type="text" value="'.$ParametriPosta['ForeignSmaName'].'" style="width:28rem" class="frm_field_string">   
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
                        <input name="NationalSmaName" type="text" value="'.$ParametriPosta['NationalSmaName'].'" style="width:28rem" class="frm_field_string">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Nome Sma estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignSmaName" type="text" value="'.$ParametriPosta['ForeignSmaName'].'" style="width:28rem" class="frm_field_string">   
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
                        <input name="NationalSmaAuthorization" type="text" value="'.$ParametriPosta['NationalSmaAuthorization'].'" style="width:28rem" class="frm_field_string">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Autorizzazione Sma estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignSmaAuthorization" type="text" value="'.$ParametriPosta['ForeignSmaAuthorization'].'" style="width:28rem" class="frm_field_string">   
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
                        <input name="NationalSmaPayment" type="text" value="'.$ParametriPosta['NationalSmaPayment'].'" style="width:43rem" class="frm_field_string">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Spese Sma estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignSmaPayment" type="text" value="'.$ParametriPosta['ForeignSmaPayment'].'" style="width:43rem" class="frm_field_string">   
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer_parametri.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>     
         </form>  
      </div>   
      <div id="menu5" class="tab-pane fade '.$inclass5.'">
        <form name="f_customer4" id="f_customer1" method="post" action="tbl_customer_parametri_upd_exe.php">
          <input name="CityId" type="hidden" value="'.$Id.'">
            <input name="steps" type="hidden" value="5">';

                $str_out .='
             <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Officio
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <select class=" frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="OfficeId" style="width:28rem"><option></option>';

                            $taxcode = $rs->SelectQuery("SELECT * FROM traffic_law.Office");

                            while ($row = mysqli_fetch_array($taxcode)) {
                                if ($Parametriricorsi['TitleIta'] == $row['TitleIta']) {
                                    $str_out .= '<option value="'.$row['Id'].'" selected>'.$row['TitleIta'].'</option>';
                                } else {
                                    $str_out .= '<option value="'.$row['Id'].'">'.$row['TitleIta'].'</option>';
                                }
                            }
                            $str_out .='</select> 
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Citta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="City" class="frm_field_string" type="text" value="'.$Parametriricorsi['City'].'" style="width:28rem">   
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
                        <input name="Province" type="text" value="'.$Parametriricorsi['Province'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_string">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                       Indirizzo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Address" type="text" value="'.$Parametriricorsi['Address'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class=" frm_field_string">   
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
                        <input name="ZIP" type="text" value="'.$Parametriricorsi['ZIP'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_numeric">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Telefono
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Phone" type="text"  value="'.$Parametriricorsi['Phone'].'" style="width:28rem;padding-left: 2rem;color: #294A9C;font-weight: 400;height: 2rem;background-color: #C7EBE0;font-size: 1.2rem;" class="frm_field_numeric">   
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
                        <input name="Fax" type="text" value="'.$Parametriricorsi['Fax'].'" style="width:28rem" class="frm_field_numeric">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                      Mail
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Mail"  type="text" value="'.$Parametriricorsi['Mail'].'" style="width:28rem" class="frm_field_string">   
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
                        <input name="PEC" type="text" value="'.$Parametriricorsi['PEC'].'" style="width:28rem" class="frm_field_numeric">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                       Web
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Web" type="text" value="'.$Parametriricorsi['Web'].'" style="width:28rem" class="frm_field_string">   
                    </div>
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>     
              <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                       Disattivazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                          <input name="Disabled" type="checkbox" '.ChkCheckButton($Parametriricorsi['Disabled']).'> 
                    </div>
                    <div class="col-sm-6 BoxRowCaption"></div>
                    
                </div>
            </div> 
            <div class="clean_row HSpace4"></div>     
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer_parametri.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
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

?>

    <script>
        $('document').ready(function () {
            $('.tab_button').click(function () {
                $('#tab_value').val($(this).attr('tab_position'));

            });


        });

    </script>

<?php
include(INC . "/footer.php");