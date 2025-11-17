<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");

if(isset($_POST['UpdateDatiEnte'])){
    header('Content-type: text/html;charset=utf-8');
    $CityId = CheckValue('CityId','s');
    $steps = CheckValue('steps','s');
    $check = CheckValue('check','s');
    $rs->SetCharset('utf8');
    $a_Customer = array(
        array('field'=>'ManagerName','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerName']),
        array('field'=>'ManagerTaxCode','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerTaxCode']),
        array('field'=>'ManagerVAT','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerVAT']),
        array('field'=>'ManagerCountry','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerCountry']),
        array('field'=>'ManagerWeb','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerWeb']),
    );
    $rs->Update('Customer',$a_Customer,"CityId='".$CityId."'");
    $a_City = array(
        array('field'=>'NationalProtocolLetterType1','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['NationalProtocolLetterType1']),
        array('field'=>'ForeignProtocolLetterType1','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ForeignProtocolLetterType1']),
        array('field'=>'NationalProtocolLetterType2','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['NationalProtocolLetterType2']),
        array('field'=>'ForeignProtocolLetterType2','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ForeignProtocolLetterType2']),
    );
    $rs->Update('sarida.City',$a_City,"Id='".$CityId."'");
    header("Location:tbl_customer_dati.php?answer=Modificato con successo");
}


include(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$Id= CheckValue('Id','s');
$rs= new CLS_DB();
$customers = $rs->Select('Customer',"CityId='".$Id."'", "CityId");
$customer = mysqli_fetch_array($customers);

$codice_Istat = $rs->SelectQuery("SELECT * FROM sarida.City WHERE Id = '$Id'");
$row = mysqli_fetch_array($codice_Istat);

$codice_Istat = $row['IstatCode'];
$NationalProtocolLetterType1 = $row['NationalProtocolLetterType1'];
$ForeignProtocolLetterType1 = $row['ForeignProtocolLetterType1'];
$NationalProtocolLetterType2 = $row['NationalProtocolLetterType2'];
$ForeignProtocolLetterType2 = $row['ForeignProtocolLetterType2'];
echo $str_out;
$str_out ='
<div class="col-sm-12 BoxRowTitle" style="text-align:center">Dati Ente</div>
            <div class="clean_row HSpace4"></div>
            <div class="clean_row HSpace4"></div>

  <div class="tab-content" style="background-color: #cbe9ea">
        <form name="f_customer1" id="f_customer1" method="post">
        <input type="hidden" name="UpdateDatiEnte" value="true">
            <input name="CityId" type="hidden" value="'.$Id.'">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                    Nome ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto"  name="ManagerName" type="text" value="'.$customer['ManagerName'].'" style="width:28rem" required>
                    </div>
                    <div class="col-sm-3 BoxRowLabel" required>
                            Stato
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                        <select class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerCountry" style="width:28rem">';
                            $taxcode = $rs->SelectQuery("SELECT Country FROM sarida.City WHERE Country <> '' ORDER BY Title");

                            while ($row = mysqli_fetch_array($taxcode)) {
                                if ($customer['ManagerCountry'] == $row['Country']) {
                                    $str_out .= '<option value="'.$row['Country'].'" selected>'.$row['Country'].'</option>';
                                } else {
                                    $str_out .= '<option value="'.$row['Country'].'">'.$row['Country'].'</option>';
                                }
                            }
                            $str_out .='</select>  
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Partita iva
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                     <select class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerTaxCode" style="width:28rem">';
                        $taxcode = $rs->SelectQuery("SELECT TaxCode FROM sarida.City WHERE TaxCode <> '' ORDER BY Title");

                        while ($row = mysqli_fetch_array($taxcode)) {
                            if ($customer['ManagerTaxCode'] == $row['TaxCode']) {
                                $str_out .= '<option value="'.$row['TaxCode'].'" selected>'.$row['TaxCode'].'</option>';
                            } else {
                                $str_out .= '<option value="'.$row['TaxCode'].'">'.$row['TaxCode'].'</option>';
                            }
                        }
                        $str_out .='</select>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Codice fiscale
                    </div>
                    <div class="col-sm-3 BoxRowCaption" required>
                     <select class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerVAT" style="width:28rem">';
                        $taxcode = $rs->SelectQuery("SELECT VAT FROM sarida.City WHERE VAT <> '' ORDER BY Title");
                        while ($row = mysqli_fetch_array($taxcode)) {
                            if ($customer['ManagerVAT'] == $row['VAT']) {
                                $str_out .= '<option value="'.$row['VAT'].'" selected>'.$row['VAT'].'</option>';
                            } else {
                                $str_out .= '<option value="'.$row['VAT'].'">'.$row['VAT'].'</option>';
                            }
                        }
                        $str_out .='</select>  
                    </div> 
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Sito internet
                    </div>
                    <div class="col-sm-3 BoxRowCaption">                   
                        <select class=" frm_field_required frm_field_string" data-bv-notempty="true" data-bv-notempty-message="Richiesto" name="ManagerWeb" style="width:28rem">';
                        $taxcode = $rs->SelectQuery("SELECT Web FROM sarida.City WHERE Web <> '' ORDER BY Title");

                        while ($row = mysqli_fetch_array($taxcode)) {
                            if ($customer['ManagerWeb'] == $row['Web']) {
                                $str_out .= '<option value="'.$row['Web'].'" selected>'.$row['Web'].'</option>';
                            } else {
                                $str_out .= '<option value="'.$row['Web'].'">'.$row['Web'].'</option>';
                            }
                        }
                        $str_out .='</select> 
                    </div>
                    <div class="col-sm-3 BoxRowLabel">Codice Istat</div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input   name="IstatCode" type="text" value="'.$codice_Istat.'" style="width:28rem">
                     </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">Lettera Protocollo Atto CDS Nazionale</div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input   name="NationalProtocolLetterType1" type="text" value="'.$NationalProtocolLetterType1.'" style="width:28rem">
                     </div>
                     <div class="col-sm-3 BoxRowLabel">Lettera Protocollo Atto CDS Estero</div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input   name="ForeignProtocolLetterType1" type="text" value="'.$ForeignProtocolLetterType1.'" style="width:28rem">
                     </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">Lettera Protocollo Atto extra CDS Nazionale</div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input   name="NationalProtocolLetterType2" type="text" value="'.$NationalProtocolLetterType2.'" style="width:28rem">
                     </div>
                     <div class="col-sm-3 BoxRowLabel"> Lettera Protocollo Atto extra CDS Estero</div>
                    <div class="col-sm-3 BoxRowCaption">
                       <input   name="ForeignProtocolLetterType2" type="text" value="'.$ForeignProtocolLetterType2	.'" style="width:28rem">
                     </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button type="submit" class="btn btn-default" style="margin-top:1rem;">Modifica</button>
                        <input type="button" onclick="window.location=\'tbl_customer_dati.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>
        </form>
    </div>
';
echo $str_out;
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
