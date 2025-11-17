<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_table.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

echo $str_out;
$Id= CheckValue('Id','n');

$rs= new CLS_DB();

$str_out ='
    <div class="container-fluid" style="padding: 0px">
        
        <div class="row-fluid">
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" >
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                        Nuovo Customer
                    </div>
                </div>
            </div> 
        </div> 
        <div class="row-fluid" style="margin-top:2rem;">
            <form name="f_article" method="post" action="" enctype="multipart/form-data">
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow">
                        <div class="col-sm-2 BoxRowLabel">
                            Codice catastale ente
                        </div>
                        <div class="col-sm-2 BoxRowCaption ">
                            <input class=" frm_field_required  frm_field_string" name="CityId" type="text" value="" style="width:28rem" required>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Logo
                        </div>
                        <div class="col-sm-2 BoxRowCaption ">
                            <input type="file" name="Blazon" id="Blazon" style="width:28rem" required>
                        </div>
                        <div class="col-sm-4 BoxRowLabel">
                       
                        </div> 
                    </div> 
                </div> 
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow" style="height:6rem;">
                        <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                            <button type="submit" name="checkcityid" class="btn btn-default" style="margin-top:1rem;">Crea</button>
                            <input type="button" onclick="window.location=\'tbl_customer.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                        </div>    
                    </div>
                </div>
            </form>
        </div>
    </div>
            ';

if (isset($_POST['checkcityid'])) {
    $CityId = $_POST['CityId'];
    $cid = $rs->Select('Customer',"CityId='$CityId'");
    $row = mysqli_fetch_array($cid);
    if ($row != null) {
        echo '<div class="container">
                <div class="row">
                    <div class="alert alert-danger">This city exists in db</div>
                </div>
            </div>';
    } else {
        $a_Folder[] = "public/_PAYMENT_/";
        $a_Folder[] = "public/_SELEA_/";
        $a_Folder[] = "public/";
        $a_Folder[] = "doc/national/fine/";
        $dirL = "img/blazon/";
        $toDirL = $dirL.'/'.$CityId .'/'; 
        mkdir($toDirL, 0777, true);
        chmod($toDirL, 0777);
        move_uploaded_file($_FILES['Blazon']['tmp_name'], $toDirL . basename($_FILES['Blazon']['name']));
        $a_Customer = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str', 'value'=>$CityId),
            array('field'=>'Blazon','selector'=>'value','type'=>'str', 'value'=>basename($_FILES['Blazon']['name'])),
            array('field'=>'ManagerCountry','selector'=>'value','type'=>'str', 'value'=>'Italia'),
            array('field'=>'ManagerProcessName','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ManagerDataEntryName','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignBankOwner','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignBankName','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignBankAccount','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignBankIban','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignBankSwift','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'Reference','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ReturnPlace','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'NationalMod23LSubject','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'NationalMod23LCustomerName','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'NationalMod23LCustomerSubject','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'NationalMod23LCustomerAddress','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'NationalMod23LCustomerCity','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignMod23LSubject','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignMod23LCustomerName','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignMod23LCustomerSubject','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignMod23LCustomerAddress','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'ForeignMod23LCustomerCity','selector'=>'value','type'=>'str', 'value'=>''),
            array('field'=>'FifthField','selector'=>'value','type'=>'int', 'value'=>(int)''),

        );
        $a_ProcessingDataPaymentNational = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str', 'value'=>$CityId),
            array('field'=>'Rigid','selector'=>'value','type'=>'int', 'value'=>(int)''),
        );
        $a_ProcessingDataPaymentForeign = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str', 'value'=>$CityId),
            array('field'=>'Rigid','selector'=>'value','type'=>'int', 'value'=>(int)''),
        );
        $a_ProcessingData126BisNational = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str', 'value'=>$CityId),
            array('field'=>'Rigid','selector'=>'value','type'=>'int', 'value'=>(int)''),
        );
        $a_ProcessingData126BisForeign = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str', 'value'=>$CityId),
            array('field'=>'Rigid','selector'=>'value','type'=>'int', 'value'=>(int)''),
        );
        $CustomerId = $rs->Insert('Customer',$a_Customer);
        $rs->Insert('ProcessingDataPaymentNational',$a_ProcessingDataPaymentNational);
        $rs->Insert('ProcessingDataPaymentForeign',$a_ProcessingDataPaymentForeign);
        $rs->Insert('ProcessingData126BisNational',$a_ProcessingData126BisNational);
        $rs->Insert('ProcessingData126BisForeign',$a_ProcessingData126BisForeign);
        foreach ($a_Folder as $folder) {
            if (!file_exists($folder.'/'.$CityId)) {
                mkdir($folder.'/'.$CityId, 0777, true);
            }
        }
        echo '<script>window.location = "tbl_customer_upd.php?Id='.$CityId.'&check=true&tab=1";</script>';
    }
}


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
