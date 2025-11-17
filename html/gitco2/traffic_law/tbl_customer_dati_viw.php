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
                        '.$customer['ManagerName'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel" required>
                        Stato
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    '.$customer['ManagerCountry'].'
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
                     '.$customer['ManagerTaxCode'].'
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Codice fiscale
                    </div>
                    <div class="col-sm-3 BoxRowCaption" required>
                     '.$customer['ManagerVAT'].'
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
                    '.$customer['ManagerWeb'].'
                    </div>
                   <div class="col-sm-3 BoxRowLabel">Codice Istat</div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$codice_Istat.'
                     </div>
                </div>
            </div>
             <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">Lettera Protocollo Atto CDS Nazionale</div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$NationalProtocolLetterType1.'
                     </div>
                     <div class="col-sm-3 BoxRowLabel">Lettera Protocollo Atto CDS Estero</div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$ForeignProtocolLetterType1.'
                     </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">Lettera Protocollo Atto extra CDS Nazionale</div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$NationalProtocolLetterType2.'
                     </div>
                     <div class="col-sm-3 BoxRowLabel"> Lettera Protocollo Atto extra CDS Estero</div>
                    <div class="col-sm-3 BoxRowCaption">
                       '.$ForeignProtocolLetterType2.'
                     </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
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
