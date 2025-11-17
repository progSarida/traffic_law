<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$CityId= $_SESSION['cityid'];

$rs_CustomerParameter = $rs->Select('V_CustomerParameter',"CityId='".$CityId."'");
$r_CustomerParameter = mysqli_fetch_array($rs_CustomerParameter);

$a_ActiveTab = array("","","","","","","","","");

$n_Tab = CheckValue('tab','n');
if ($n_Tab==0) $n_Tab = 1;

$a_ActiveTab[$n_Tab] = "active";
$controllerQuery = "SELECT Name, Id FROM Controller WHERE CityId = '".$_SESSION['cityid']."'";
$controllerNat126Bis = CreateSelectQuery($controllerQuery,"Data126BisNationalControllerId","Id","Name",$r_CustomerParameter['Data126BisNationalControllerId'],false);
$controllerFor126Bis = CreateSelectQuery($controllerQuery,"Data126BisForeignControllerId","Id","Name",$r_CustomerParameter['Data126BisForeignControllerId'],false);
$controllerNat180 = CreateSelectQuery($controllerQuery,"Data180NationalControllerId","Id","Name",$r_CustomerParameter['Data180NationalControllerId'],false);
$controllerFor180 = CreateSelectQuery($controllerQuery,"Data180ForeignControllerId","Id","Name",$r_CustomerParameter['Data180ForeignControllerId'],false);

$a_disputeCheckType = array(
        0=>"Modalità Ministeriale"
, 1=>"Modalità Giurisprudenziale"
);
$disputeCheckNat126Bis = CreateArraySelect($a_disputeCheckType,true,"Data126BisNationalDisputeCheckType","Data126BisNationalDisputeCheckType",$r_CustomerParameter['Data126BisNationalDisputeCheckType'],true);
$disputeCheckFor126Bis = CreateArraySelect($a_disputeCheckType,true,"Data126BisForeignDisputeCheckType","Data126BisForeignDisputeCheckType",$r_CustomerParameter['Data126BisForeignDisputeCheckType'],true);
$a_elaborate = array(
    0=>"Non elaborare",
    1=>"Elabora"
);
$communicationDelayCheckNat126Bis = CreateArraySelect($a_elaborate,true,"Data126BisNationalCommunicationDelay","Data126BisNationalCommunicationDelay",$r_CustomerParameter['Data126BisNationalCommunicationDelay'],true);
$communicationDelayCheckFor126Bis = CreateArraySelect($a_elaborate,true,"Data126BisForeignCommunicationDelay","Data126BisForeignCommunicationDelay",$r_CustomerParameter['Data126BisForeignCommunicationDelay'],true);
$incompletedCommunicationCheckNat126Bis = CreateArraySelect($a_elaborate,true,"Data126BisNationalIncompletedCommunication","Data126BisNationalIncompletedCommunication",$r_CustomerParameter['Data126BisNationalIncompletedCommunication'],true);
$incompletedCommunicationCheckFor126Bis = CreateArraySelect($a_elaborate,true,"Data126BisForeignIncompletedCommunication","Data126BisForeignIncompletedCommunication",$r_CustomerParameter['Data126BisForeignIncompletedCommunication'],true);

$presentationDelayCheckNat180 = CreateArraySelect($a_elaborate,true,"Data180NationalPresentationDelay","Data180NationalPresentationDelay",$r_CustomerParameter['Data180NationalPresentationDelay'],true);
$presentationDelayCheckFor180 = CreateArraySelect($a_elaborate,true,"Data180ForeignPresentationDelay","Data180ForeignPresentationDelay",$r_CustomerParameter['Data180ForeignPresentationDelay'],true);
$incompletedPresentationCheckNat180 = CreateArraySelect($a_elaborate,true,"Data180NationalIncompletedPresentation","Data180NationalIncompletedPresentation",$r_CustomerParameter['Data180NationalIncompletedPresentation'],true);
$incompletedPresentationCheckFor180 = CreateArraySelect($a_elaborate,true,"Data180ForeignIncompletedPresentation","Data180ForeignIncompletedPresentation",$r_CustomerParameter['Data180ForeignIncompletedPresentation'],true);

$a_delayType = array(
    0=>"Giorni verbale",
    1=>"Giorni parametri"
);
$presentationDelayTypeCheckNat180 = CreateArraySelect($a_delayType,true,"Data180NationalPresentationDelayType","Data180NationalPresentationDelayType",$r_CustomerParameter['Data180NationalPresentationDelayType'],true);
$presentationDelayTypeCheckFor180 = CreateArraySelect($a_delayType,true,"Data180ForeignPresentationDelayType","Data180ForeignPresentationDelayType",$r_CustomerParameter['Data180ForeignPresentationDelayType'],true);

$rs_CustomerCharge = $rs->Select('CustomerCharge',"CityId='".$CityId."' AND CreationType=1 AND ToDate IS NULL");
$r_CustomerCharge = mysqli_fetch_array($rs_CustomerCharge);

$rs_ProcessingDataPayment = $rs->SelectQuery("SELECT N.AmountLimit AmountLimitNational, F.AmountLimit AmountLimitForeign FROM ProcessingDataPaymentNational N JOIN ProcessingDataPaymentForeign F ON N.CityId=F.CityId WHERE N.CityId='$CityId'");
$r_ProcessingDataPayment = mysqli_fetch_array($rs_ProcessingDataPayment);

$rs_PostalCharge = $rs->Select('PostalCharge',"ToDate IS NULL and CityId='$CityId'");
$r_PostalCharge = mysqli_fetch_array($rs_PostalCharge);

$str_out .='
    <form name="f_upd_customer" id="f_upd_customer" class="form-horizontal" action="tbl_customer_prc_upd_exe.php" method="post">
        <input type="hidden" name="tab_value" id="tab_value" value="'.$n_Tab.'">
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Parametri
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="mioTab">
                <li tab_position="1" class="tab_button '. $a_ActiveTab[1] .'" id="tab_CustomerPayment"><a href="#CustomerPayment" data-toggle="tab">Pagamenti</a></li>
                <li tab_position="2" class="tab_button '. $a_ActiveTab[2] .'" id="tab_CustomerReminder"><a href="#CustomerReminder" data-toggle="tab">Solleciti</a></li>
                <li tab_position="3" class="tab_button '. $a_ActiveTab[3] .'" id="tab_CustomerAvvBon"><a href="#CustomerAvvBon" data-toggle="tab">Avvisi bonari</a></li>    
                <li tab_position="4" class="tab_button '. $a_ActiveTab[4] .'" id="tab_Customer126Bis"><a href="#Customer126Bis" data-toggle="tab">Art. 126 Bis</a></li>
                <li tab_position="5" class="tab_button '. $a_ActiveTab[5] .'" id="tab_Customer180"><a href="#Customer180" data-toggle="tab">Art. 180</a></li>
                <li tab_position="6" class="tab_button '. $a_ActiveTab[6] .'" id="tab_CustomerPostNational"><a href="#CustomerPostNational" data-toggle="tab">Postalizzazione</a></li>
                <li tab_position="7" class="tab_button '. $a_ActiveTab[7] .'" id="tab_CustomerPostForeign"><a href="#CustomerPostForeign" data-toggle="tab">Postalizzazione estera</a></li>
                <li tab_position="8" class="tab_button '. $a_ActiveTab[8] .'" id="tab_CustomerDecurtation"><a href="#CustomerDecurtation" data-toggle="tab">Decurtazione punti</a></li>
                <li tab_position="9" class="tab_button '. $a_ActiveTab[9] .'" id="tab_CustomerRole"><a href="#CustomerRole" data-toggle="tab">Ruoli</a></li>
            </ul>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="tab-content">
            <div class="tab-pane '. $a_ActiveTab[1] .'" id="CustomerPayment">
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri NAZIONALE
                    </div> 
                    <div class="col-sm-6 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri ESTERO
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input name="DataPaymentNationalAutomatic" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['DataPaymentNationalAutomatic']).'>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input name="DataPaymentForeignAutomatic" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['DataPaymentForeignAutomatic']).'> 
                     </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input  class="form-control frm_field_required frm_field_numeric " id="DataPaymentNationalWaitDay" name="DataPaymentNationalWaitDay" type="text" value="'.$r_CustomerParameter['DataPaymentNationalWaitDay'].'" style="width:28rem">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_required frm_field_numeric " name="DataPaymentForeignWaitDay" type="text" value="'.$r_CustomerParameter['DataPaymentForeignWaitDay'].'" style="width:28rem">   
                    </div>                              
        
                    <div class="clean_row HSpace4"></div>         

                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentNationalRangeDayMin" type="text" value="'.$r_CustomerParameter['DataPaymentNationalRangeDayMin'].'" style="width:28rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentForeignRangeDayMin" type="text" value="'.$r_CustomerParameter['DataPaymentForeignRangeDayMin'].'" style="width:28rem;">   
                    </div>                              

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Giorni aggiuntivi alla data della sentenza del ricorso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentNationalRangeDayMax" type="text" value="'.$r_CustomerParameter['DataPaymentNationalRangeDayMax'].'" style="width:28rem;">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni aggiuntivi alla data della sentenza del ricorso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentForeignRangeDayMax" type="text" value="'.$r_CustomerParameter['DataPaymentForeignRangeDayMax'].'" style="width:28rem;">   
                    </div>
                </div>
            </div>
            <div class="tab-pane '. $a_ActiveTab[2] .'" id="CustomerReminder">
                <div class="col-sm-12">
                    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                        <div class="col-sm-11" style="font-size: 1.2rem;">
                            <ul style="list-style-position: inside;">
                                <li>Nota: "Percentuale maggiorazione semestrale art. 206 C.d.S." e "Importo minimo esclusione sollecito" hanno lo stesso valore di quanto è definito nella scheda "Ruoli"</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-12 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri intestazione
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Telefono
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ReminderPhone" type="text" maxlength="15" pattern="^[\d/+]" value="'.$r_CustomerParameter['ReminderPhone'].'" style="width:28rem">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        E-mail
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ReminderEmail" type="text" value="'.$r_CustomerParameter['ReminderEmail'].'" style="width:28rem">   
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel" style="height:6.4rem">
                        Informazioni ufficio
                    </div>
                    <div class="col-sm-9 BoxRowCaption" style="height:6.4rem">
                        <textarea class="form-control frm_field_string" name="ReminderOfficeInfo" style="height:5.8rem;margin-left:0;">'.StringOutDB($r_CustomerParameter['ReminderOfficeInfo']).'</textarea>
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold;text-align:center;">
                        Solleciti NAZIONALE
                    </div>
                    <div class="col-sm-6 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Solleciti ESTERO
                    </div>
                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento sollecito
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentNationalPaymentDayReminder" type="text" value="'.$r_CustomerParameter['DataPaymentNationalPaymentDayReminder'].'" style="width:14rem">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento sollecito
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentForeignPaymentDayReminder" type="text" value="'.$r_CustomerParameter['DataPaymentForeignPaymentDayReminder'].'" style="width:14rem">   
                    </div>

                    <div class="clean_row HSpace4"></div>           

                    <div class="col-sm-3 BoxRowLabel">
                        Giorni di tolleranza sul termine per pagamento ridotto
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentNationalReducedPaymentDayAccepted" type="text" value="'.$r_CustomerParameter['DataPaymentNationalReducedPaymentDayAccepted'].'" style="width:14rem">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni di tolleranza sul termine per pagamento ridotto
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentForeignReducedPaymentDayAccepted" type="text" value="'.$r_CustomerParameter['DataPaymentForeignReducedPaymentDayAccepted'].'" style="width:14rem">   
                    </div>
                    
                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Giorni di tolleranza sul termine per pagamento normale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentNationalPaymentDayAccepted" type="text" value="'.$r_CustomerParameter['DataPaymentNationalPaymentDayAccepted'].'" style="width:14rem">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni di tolleranza sul termine per pagamento normale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="DataPaymentForeignPaymentDayAccepted" type="text" value="'.$r_CustomerParameter['DataPaymentForeignPaymentDayAccepted'].'" style="width:14rem">   
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Percentuale maggiorazione semestrale art. 206 C.d.S.
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$r_CustomerCharge['NationalPercentualReminder'].' %
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Percentuale maggiorazione semestrale art. 206 C.d.S.
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$r_CustomerCharge['ForeignPercentualReminder'].' %
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Importo minimo esclusione sollecito
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$r_ProcessingDataPayment['AmountLimitNational'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Importo minimo esclusione sollecito
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$r_ProcessingDataPayment['AmountLimitForeign'].'
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Spese di invio sollecito
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" name="NationalReminderNotificationFee" type="text" value="'.$r_CustomerCharge['NationalReminderNotificationFee'].'" style="width:14rem">   
                    </div>
                    <div class="col-sm-6 BoxRowLabel">
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Spese aumentate per ulteriori solleciti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="IncreaseNationalNotificationFee" value="1" type="checkbox" style="width:14rem" '.ChkCheckButton($r_CustomerCharge['IncreaseNationalNotificationFee']).'>
                     </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Spese aumentate per ulteriori solleciti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="IncreaseForeignNotificationFee" value="1" type="checkbox" style="width:14rem" '.ChkCheckButton($r_CustomerCharge['IncreaseForeignNotificationFee']).'>
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                         Applica maggiorazione semestrale su importi fissati nel ricorso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="DataPaymentNationalApplyPercentualOnPrefectureFee" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['DataPaymentNationalApplyPercentualOnPrefectureFee']).'>
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                         Applica maggiorazione semestrale su importi fissati nel ricorso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="DataPaymentForeignApplyPercentualOnPrefectureFee" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['DataPaymentForeignApplyPercentualOnPrefectureFee']).'>
                    </div> 

                    <div class="clean_row HSpace4"></div>
 
                    <div class="col-sm-3 BoxRowLabel">
                         Aggiungere spese di ricerca e notifica su importi fissati nel ricorso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input  name="DataPaymentNationalIncludeNotificationResearch" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['DataPaymentNationalIncludeNotificationResearch']).'>
                    </div>   
                    <div class="col-sm-3 BoxRowLabel">
                         Aggiungere spese di ricerca e notifica su importi fissati nel ricorso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="DataPaymentForeignIncludeNotificationResearch" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['DataPaymentForeignIncludeNotificationResearch']).'>
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                         Intestazione SARIDA
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="NationalReminderHeaderSarida" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['NationalReminderHeaderSarida']).'>
                    </div> 
                    <div class="col-sm-3 BoxRowLabel">
                         Intestazione SARIDA
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignReminderHeaderSarida" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['ForeignReminderHeaderSarida']).'>
                    </div> 

                    <div class="col-sm-12 BoxRow" style="border-right: 1px solid #E7E7E7;text-align:center;">
                        Altri parametri comuni
                    </div>
                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-5 BoxRowLabel">
                        Mantieni la sanzione al minimo edittale
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input name="ReminderAdditionalFee"  type="checkbox" '.ChkCheckButton($r_CustomerParameter['ReminderAdditionalFee']).' />
                    </div>     
                </div>
            </div> 
            <div class="tab-pane '. $a_ActiveTab[3] .'" id="CustomerAvvBon">
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri NAZIONALE
                    </div> 
                    <div class="col-sm-6 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri ESTERO
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Giorni elaborazione
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                       <input class="form-control frm_field_numeric" name="ElaborationDaysNational" value="'.$r_CustomerParameter['ElaborationDaysNational'].'" type="text">
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni elaborazione
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                       <input class="form-control frm_field_numeric" name="ElaborationDaysForeign" value="'.$r_CustomerParameter['ElaborationDaysForeign'].'" type="text"> 
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                       <input class="form-control frm_field_numeric" name="PaymentDaysNational" value="'.$r_CustomerParameter['PaymentDaysNational'].'" type="text">
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni pagamento
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                       <input class="form-control frm_field_numeric" name="PaymentDaysForeign" value="'.$r_CustomerParameter['PaymentDaysForeign'].'" type="text"> 
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Spese di invio avviso bonario nazionale
                    </div>
                    <div class="col-sm-1 BoxRowCaption">                   
                       <input class="form-control frm_field_currency" name="NationalKindFineSendFee" value="'.$r_CustomerParameter['NationalKindFineSendFee'].'" type="text">
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                    </div>
                    <div class="col-sm-12 BoxRow" style="border-right: 1px solid #E7E7E7;text-align:center;">
                        Altri parametri comuni
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-5 BoxRowLabel">
                        Attiva l’invio dei verbali esteri tramite avviso bonario da spedire tramite posta ordinaria
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input name="EnableForeignKindSending"  type="checkbox" '.ChkCheckButton($r_CustomerParameter['EnableForeignKindSending']).' />
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-5 BoxRowLabel">
                        Attiva procedura speciale per art. 193/2 e 80/14
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input name="EnableKindOldProcedure"  type="checkbox" '.ChkCheckButton($r_CustomerParameter['EnableKindOldProcedure']).' />
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-5 BoxRowLabel">
                        Disabilita elaborazione PagoPA per i preinserimenti di inviti in AG (per art. 193/2 e 80/14)
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input name="DisableKindPagoPAProcessing"  type="checkbox" '.ChkCheckButton($r_CustomerParameter['DisableKindPagoPAProcessing']).' />
                    </div>
                </div>
            </div>    
            <div class="tab-pane '. $a_ActiveTab[4] .'" id="Customer126Bis">
            <div class="col-sm-3 BoxRowLabel">
                        Decurtazione punti abilitata su atti non notificati già pagati
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                       <input name="LicensePointPaymentCompletion" value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['LicensePointPaymentCompletion']).'>
                    </div>
                    <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri 126 Bis Nazionale
                    </div> 
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri 126 Bis Estero
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisNationalAutomatic" disabled value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['Data126BisNationalAutomatic']).'>
                     </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisForeignAutomatic" disabled value="1"  type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['Data126BisForeignAutomatic']).'>
                    </div>                              
       
                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisNationalRangeDayMin" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data126BisNationalRangeDayMin'].'" style="width:28rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisForeignRangeDayMin" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data126BisForeignRangeDayMin'].'" style="width:28rem;">   
                    </div>                              

                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisNationalWaitDay" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data126BisNationalWaitDay'].'" style="width:28rem">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisForeignWaitDay" class="form-control frm_field_required frm_field_numeric" type="text" value="'.$r_CustomerParameter['Data126BisForeignWaitDay'].'" style="width:28rem">   
                    </div>                              

                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisNationalRangeDayMax" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data126BisNationalRangeDayMax'].'" style="width:28rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisForeignRangeDayMax" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data126BisForeignRangeDayMax'].'" style="width:28rem;">   
                    </div>    
                                        
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni per comunicazione dati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisNationalCommunicationDays" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data126BisNationalCommunicationDays'].'" style="width:28rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni per comunicazione dati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisForeignCommunicationDays" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data126BisForeignCommunicationDays'].'" style="width:28rem;">   
                    </div>    
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Ritardo nella comunicazione dei dati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$communicationDelayCheckNat126Bis.'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Ritardo nella comunicazione dei dati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$communicationDelayCheckFor126Bis.'
                    </div>  
                                        <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Comunicazione dei dati incompleta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$incompletedCommunicationCheckNat126Bis.'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Comunicazione dei dati incompleta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$incompletedCommunicationCheckFor126Bis.'
                    </div>  
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Modalità controllo ricorso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$disputeCheckNat126Bis.'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Modalità controllo ricorso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$disputeCheckFor126Bis.'
                    </div>  
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Luogo di elaborazione dei verbali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisNationalAddress" type="text" class="form-control frm_field_required frm_field_string" value="'.$r_CustomerParameter['Data126BisNationalAddress'].'" style="width:100%;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Luogo di elaborazione dei verbali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data126BisForeignAddress" type="text" class="form-control frm_field_required frm_field_string" value="'.$r_CustomerParameter['Data126BisForeignAddress'].'" style="width:100%;">   
                    </div>         
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Accertatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$controllerNat126Bis.'
                     </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Accertatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$controllerFor126Bis.'
                    </div>                              
                </div>
            </div> 

    
            <div class="tab-pane '. $a_ActiveTab[5] .'" id="Customer180">
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri art. 180 Nazionale
                    </div> 
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri art. 180 Estero
                    </div>

                    <div class="clean_row HSpace4"></div>
                   
                    <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180NationalAutomatic" disabled type="checkbox" value="1" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['Data180NationalAutomatic']).'>
                     </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Elaborazione automatica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180ForeignAutomatic" disabled value="1" type="checkbox" style="width:28rem" '.ChkCheckButton($r_CustomerParameter['Data180ForeignAutomatic']).'>
                    </div>                              

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180NationalRangeDayMin" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data180NationalRangeDayMin'].'" style="width:28rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) minimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180ForeignRangeDayMin" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data180ForeignRangeDayMin'].'" style="width:28rem;">   
                    </div>                              
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180NationalDayAccepted" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data180NationalDayAccepted'].'" style="width:28rem">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni ulteriori attesa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180ForeignDayAccepted" class="form-control frm_field_required frm_field_numeric" type="text" value="'.$r_CustomerParameter['Data180ForeignDayAccepted'].'" style="width:28rem">   
                    </div>   
                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180NationalRangeDayMax" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data180NationalRangeDayMax'].'" style="width:28rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Tempo (gg) massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180ForeignRangeDayMax" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data180ForeignRangeDayMax'].'" style="width:28rem;">   
                    </div>               
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Modalità giorni per presentazione dati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$presentationDelayTypeCheckNat180.'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Modalità giorni per presentazione dati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$presentationDelayTypeCheckFor180.'
                    </div>                 
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Giorni per presentazione dati se modalità impostata su giorni parametri
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180NationalPresentationDays" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data180NationalPresentationDays'].'" style="width:28rem;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Giorni per presentazione dati se modalità impostata su giorni parametri
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180ForeignPresentationDays" type="text" class="form-control frm_field_required frm_field_numeric" value="'.$r_CustomerParameter['Data180ForeignPresentationDays'].'" style="width:28rem;">   
                    </div>   
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Ritardo nella presentazione dei dati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$presentationDelayCheckNat180.'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Ritardo nella presentazione dei dati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$presentationDelayCheckFor180.'
                    </div>  
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Presentazione dei dati incompleta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$incompletedPresentationCheckNat180.'
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Presentazione dei dati incompleta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$incompletedPresentationCheckFor180.'
                    </div>  
                                               
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Luogo di elaborazione dei verbali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180NationalAddress" type="text" class="form-control frm_field_required frm_field_string" value="'.$r_CustomerParameter['Data180NationalAddress'].'" style="width:100%;">   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Luogo di elaborazione dei verbali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="Data180ForeignAddress" type="text" class="form-control frm_field_required frm_field_string" value="'.$r_CustomerParameter['Data180ForeignAddress'].'" style="width:100%;">   
                    </div>  
                     
                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Accertatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.$controllerNat180.'   
                    </div>   
                     <div class="col-sm-3 BoxRowLabel">
                        Accertatore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                         '.$controllerFor180.'     
                    </div>                              
                </div>
            </div> 
            
            <div class="tab-pane '. $a_ActiveTab[6] .'" id="CustomerPostNational">
                <input type="hidden" name="CustomerChargeId" value="'.$r_CustomerCharge['Id'].'">
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Postalizzazione NAZIONALE
                    </div> 

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                       Totale forfettario
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="NationalTotalFee" class="form-control frm_field_numeric" type="text" value="'.$r_CustomerCharge['NationalTotalFee'].'" style="width:28rem">   
                    </div>                           

                    <div class="clean_row HSpace4"></div>         

                    <div class="col-sm-6 BoxRowLabel">
                       Spese notifica nazionale
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="NationalNotificationFee" type="text" value="'.$r_CustomerCharge['NationalNotificationFee'].'" style="width:28rem;" class="form-control frm_field_numeric">   
                    </div>   

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                       Spese ricerca nazionale
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="NationalResearchFee" type="text" value="'.$r_CustomerCharge['NationalResearchFee'].'" style="width:28rem;" class="form-control frm_field_numeric">   
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                       Spese Notifica PEC nazionale
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="NationalPECNotificationFee" type="text" value="'.$r_CustomerCharge['NationalPECNotificationFee'].'" style="width:28rem" class="form-control frm_field_numeric">   
                    </div>

                    <div class="clean_row HSpace4"></div>           

                    <div class="col-sm-6 BoxRowLabel">
                        Spese Ricerca PEC nazionale
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="NationalPECResearchFee" type="text" value="'.$r_CustomerCharge['NationalPECResearchFee'].'" style="width:28rem" class="form-control frm_field_numeric">   
                    </div>

                    <div class="clean_row HSpace4"></div>     

                    <div class="col-sm-6 BoxRowLabel">
                       Bollettino nazionale 
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.CreateArraySelect(array(123 => 'TD 123', 451 => 'TD 451', 674 => 'TD 674', 896 => 'TD 896'), true, 'NationalPostalType', 'NationalPostalType', $r_CustomerCharge['NationalPostalType']).'
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    </div>

                    <div class="clean_row HSpace4"></div>     

                    <div class="col-sm-6 BoxRowLabel">
                       Bollettino nazionale PagoPA
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.CreateArraySelect(array(123 => 'TD 123', 451 => 'TD 451', 674 => 'TD 674', 896 => 'TD 896'), true, 'NationalPostalTypePagoPA', 'NationalPostalTypePagoPA', $r_CustomerCharge['NationalPostalTypePagoPA']).'
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    </div>

                    <div class="clean_row HSpace4"></div>     

                    <div class="col-sm-6 BoxRowLabel">
                        Spese CAN.
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="CanFee" id="CanFee" type="text" value="'.$r_PostalCharge['CanFee'].'">
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                        Spese CAD.
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="CadFee" id="CadFee" type="text" value="'.$r_PostalCharge['CadFee'].'">
                    </div>
                </div>
            </div>

            <div class="tab-pane '. $a_ActiveTab[7] .'" id="CustomerPostForeign">
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Postalizzazione ESTERO
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                       Spese ricerca estero
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="ForeignResearchFee" type="text"  value="'.$r_CustomerCharge['ForeignResearchFee'].'" style="width:28rem;" class="form-control frm_field_numeric">   
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                        Spese Ricerca PEC estero
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="ForeignPECResearchFee" type="text" value="'.$r_CustomerCharge['ForeignPECResearchFee'].'" style="width:28rem" class="form-control frm_field_numeric">   
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                       Bollettino estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.CreateArraySelect(array(123 => 'TD 123', 451 => 'TD 451', 674 => 'TD 674', 896 => 'TD 896'), true, 'ForeignPostalType', 'ForeignPostalType', $r_CustomerCharge['ForeignPostalType']).'
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    </div>

                    <div class="clean_row HSpace4"></div>     

                    <div class="col-sm-6 BoxRowLabel">
                       Bollettino estero PagoPA
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '.CreateArraySelect(array(123 => 'TD 123', 451 => 'TD 451', 674 => 'TD 674', 896 => 'TD 896'), true, 'ForeignPostalTypePagoPA', 'ForeignPostalTypePagoPA', $r_CustomerCharge['ForeignPostalTypePagoPA']).'
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-12 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Spese postali
                    </div> 

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                        Totale forfettario
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="ForeignTotalFee" class="form-control frm_field_numeric" type="text" value="'.$r_CustomerCharge['ForeignTotalFee'].'" style="width:28rem">   
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                       Spese notifica estero
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="ForeignNotificationFee" type="text" value="'.$r_CustomerCharge['ForeignNotificationFee'].'" style="width:28rem;" class="form-control frm_field_numeric">   
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel">
                      Spese Notifica PEC estero
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="ForeignPECNotificationFee"  type="text" value="'.$r_CustomerCharge['ForeignPECNotificationFee'].'" style="width:28rem" class="form-control frm_field_numeric">   
                    </div>

                    <div class="clean_row HSpace4"></div>
        
                    <div class="col-sm-6 BoxRow" style="border-right: 1px solid #E7E7E7;text-align:center;">
                        VERBALI
                    </div>
                    <div class="col-sm-6 BoxRow" style="text-align:center;">
                        SOLLECITI
                    </div>
        
                    <div class="clean_row HSpace4"></div>
        
                    <div class="col-sm-3 BoxRowLabel">
                        Zona 0
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="Zone0" id="Zone0" type="text" value="'.$r_PostalCharge['Zone0'].'">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Zona 0
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="ReminderZone0" id="ReminderZone0" type="text" value="'.$r_PostalCharge['ReminderZone0'].'">
                    </div>
        
                    <div class="clean_row HSpace4"></div>
        
                    <div class="col-sm-3 BoxRowLabel">
                        Zona 1
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="Zone1" id="Zone1" type="text" value="'.$r_PostalCharge['Zone1'].'">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Zona 1
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="ReminderZone1" id="ReminderZone1" type="text" value="'.$r_PostalCharge['ReminderZone1'].'">
                    </div>
        
                    <div class="clean_row HSpace4"></div>
        
                    <div class="col-sm-3 BoxRowLabel">
                        Zona 2
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="Zone2" id="Zone2" type="text" value="'.$r_PostalCharge['Zone2'].'">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Zona 2
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="ReminderZone2" id="ReminderZone2" type="text" value="'.$r_PostalCharge['ReminderZone2'].'">
                    </div>
        
                    <div class="clean_row HSpace4"></div>
        
                    <div class="col-sm-3 BoxRowLabel">
                        Zona 3
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="Zone3" id="Zone3" type="text" value="'.$r_PostalCharge['Zone3'].'">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Zona 3
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="ReminderZone3" id="ReminderZone3" type="text" value="'.$r_PostalCharge['ReminderZone3'].'">
                    </div>
                </div>                   
            </div>                 
            <div class="tab-pane ' . $a_ActiveTab[8] . '" id="CustomerDecurtation">
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                            Giorni di ritardo sulla trasmissione dei punti da decurtare, da aggiungere alla data di definizione del verbale                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="LicensePointDecurtationDays" type="text" value="'.$r_CustomerParameter['LicensePointDecurtationDays'].'" />
                    </div>
                </div>
            </div>
            <div class="tab-pane '. $a_ActiveTab[9] .'" id="CustomerRole">
                <div class="col-sm-12">
                    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                        <div class="col-sm-11" style="font-size: 1.2rem;">
                            <ul style="list-style-position: inside;">
                                <li>Nota: "Percentuale maggiorazione semestrale art. 206 C.d.S." e "Importo minimo per iscrizione a ruolo" vengono utilizzati anche dalle elaborazioni dei solleciti.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-12 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri generali
                    </div>

                    <div class="clean_row HSpace4"></div>

            		<div class="col-sm-1 BoxRowLabel">
                        Concessionario
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="RoleConcessionaire" type="text" class="form-control frm_field_string" value="'.$r_CustomerParameter['RoleConcessionaire'].'">
                    </div>
            		<div class="col-sm-2 BoxRowLabel">
                        Codice ente (Tracciato 290)
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input name="RoleCityCode" type="text" class="form-control frm_field_string" value="'.$r_CustomerParameter['RoleCityCode'].'"/>
                    </div>
            		<div class="col-sm-2 BoxRowLabel font_small">
                        N. Rate minime scadute per iscrizione a ruolo
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input name="RoleMinExpiredInstallments" type="text" class="form-control frm_field_numeric" value="'.$r_CustomerParameter['RoleMinExpiredInstallments'].'"/>
                    </div>
                    <div class="col-sm-1 BoxRowLabel"></div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri NAZIONALE
                    </div> 
                    <div class="col-sm-6 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        Parametri ESTERO
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Percentuale maggiorazione semestrale art. 206 C.d.S.
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="NationalPercentualReminder" type="text" value="'.$r_CustomerCharge['NationalPercentualReminder'].'" style="width:14rem">
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        %
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Percentuale maggiorazione semestrale art. 206 C.d.S.
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" name="ForeignPercentualReminder" type="text" value="'.$r_CustomerCharge['ForeignPercentualReminder'].'" style="width:14rem">   
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        %
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Importo minimo per iscrizione a ruolo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" name="AmountLimitNational" type="text" value="'.$r_ProcessingDataPayment['AmountLimitNational'].'" style="width:14rem">   
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Importo minimo per iscrizione a ruolo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_currency frm_field_required" name="AmountLimitForeign" type="text" value="'.$r_ProcessingDataPayment['AmountLimitForeign'].'" style="width:14rem">   
                    </div>
                </div>
            </div>
        
        </div>
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="table_label_H HSpace4" style="height:8rem;">
            	<button id="update" type="submit" class="btn btn-success" style="margin-top:2rem;width:inherit;"><i id="UpdateIcon" class="fas fa-save fa-fw"></i> Salva</button>
            </div>
        </div>
    </form>  
';
echo $str_out;

?>

    <script>
        $('document').ready(function () {
            $('.tab_button').click(function () {
                $('#tab_value').val($(this).attr('tab_position'));

            });
		$('#f_upd_customer').bootstrapValidator({
			live: 'disabled',
			fields: {
				frm_field_required: {
					selector: '.frm_field_required',
					validators: {
						notEmpty: {
							message: 'Richiesto'
						}
					}
				},
				frm_field_numeric: {
					selector: '.frm_field_numeric',
					validators: {
						notEmpty: {
							message: 'Non numerico'
						}
					}
				},
			}
		});
        });

    </script>

<?php
include(INC . "/footer.php");
