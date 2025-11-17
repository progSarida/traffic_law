<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$url = $_SERVER['REQUEST_URI'];
$getLink = explode("&",$url);
$new_url = $getLink[0];

$strOrder = "Id DESC";

$order_Numero_Blocco = 'cast(Numero_blocco as unsigned) asc';
$order_Preffix = 'Preffix asc';
$order_Code = 'Code asc';
$order_Name = 'Name asc';
$order_Date = 'Date asc';

$link_Numero_Blocco = 'cast(Numero_blocco as unsigned) asc';
$link_Preffix = 'Preffix asc';
$link_Code = 'Code asc';
$link_Name = 'Name asc';
$link_Date = 'Date asc';


if(isset($_GET['order_Numero_Blocco']) && $_GET['order_Numero_Blocco']!=""){
    $order_Numero_Blocco = $_GET['order_Numero_Blocco'];
    $strOrder = $order_Numero_Blocco;
    $link_Numero_Blocco = $order_Numero_Blocco=='cast(Numero_blocco as unsigned) asc' ? $order_Numero_Blocco = 'cast(Numero_blocco as unsigned) desc': $order_Numero_Blocco = 'cast(Numero_blocco as unsigned) asc';
}

if(isset($_GET['order_Preffix']) && $_GET['order_Preffix']!=""){
    $order_Preffix = $_GET['order_Preffix'];
    $strOrder = $order_Preffix;
    $link_Preffix = $order_Preffix=='Preffix asc' ? $order_Preffix = 'Preffix desc': $order_Preffix = 'Preffix asc';
}

if(isset($_GET['order_Code']) && $_GET['order_Code']!=""){
    $order_Code = $_GET['order_Code'];
    $strOrder = $order_Code;
    $link_Code = $order_Code=='Code asc' ? $order_Code = 'Code desc': $order_Code = 'Code asc';
}

if(isset($_GET['order_Name']) && $_GET['order_Name']!=""){
    $order_Name = $_GET['order_Name'];
    $strOrder = $order_Name;
    $link_Name = $order_Name=='Name asc' ? $order_Code = 'Name desc': $order_Code = 'Name asc';
}

if(isset($_GET['order_Date']) && $_GET['order_Date']!=""){
    $order_Date = $_GET['order_Date'];
    $strOrder = $order_Date;
    $link_Date = $order_Date=='Date asc' ? $order_Date = 'Date desc': $order_Date = 'Date asc';
}


if (
    !isset($_GET['order_Numero_Blocco']) && 
    !isset($_GET['order_Preffix']) &&
    !isset($_GET['order_Code']) &&
    !isset($_GET['order_Name']) &&
    !isset($_GET['order_Date'])){
    $strOrder = "Id DESC";
}


?>

<div class="col-sm-12">

    <?php if(isset($_GET['answer'])){
        $answer = $_GET['answer'];
            ?><br><div class="alert alert-success"><?php echo $answer;?></div><?php
        } ?>
    <div class="deleted_message"></div>
    <div class="col-sm-12">
        <form class="form-inline boletario_search" method="post" action="">
            <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
                <div class="col-md-2 form-group BoxRowLabel">
                    <label>Tipo Atto (Scegliere tra):</label>
                </div>
                <div class="col-md-2 form-group BoxRowCaption">
                    <input type="radio"  name="select" value="0" id="select_tipo" style="position: initial;vertical-align: top;" checked> <label style="line-height:2;vertical-align: top;"> Tutti</label>
                </div>
                <div class="col-md-2 form-group BoxRowCaption">
                    <input type="radio"  name="select" value="2" id="select_tipo" style="position: initial;vertical-align: top;"> <label style="line-height:2;vertical-align: top;"> Preavvisi</label>
                </div>
                <div class="col-md-2 form-group BoxRowCaption">
                    <input type="radio"  name="select" value="3" id="select_tipo" style="position: initial;vertical-align: top;">  <label style="line-height:2;vertical-align: top;"> Verbali</label>
                </div>
                <div class="col-md-2 form-group BoxRowCaption">
                    <input type="radio"  name="select" value="1" id="select_tipo" style="position: initial;vertical-align: top;">  <label style="line-height:2;vertical-align: top;"> Verbali Generici</label>
                </div>
                <div class="col-md-2 form-group BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>

                <div class="col-md-2 form-group BoxRowLabel">
                    <label>Uso Accertatore</label>
                </div>
                <div class="col-md-4 form-group BoxRowCaption">
                    <select class="form-control bv-form" id="select_controller" name="controller" style="width: 20rem;">
                        <option value="">Seleziona Accertatori</option>
                        <?php
                        $cityid = $_SESSION['cityid'];
                        $accertratore = $rs->SelectQuery("SELECT * FROM Controller WHERE CityId = '".$cityid."'");
                        echo ' <option value="0">Promiscuo</option>';
                        while($row_acce = mysqli_fetch_array($accertratore)){
                            $Qualification = ($row_acce['Qualification'] != "") ? $row_acce['Qualification']." " : "";
                            ?>
                            <option value="<?php echo $row_acce['Id'];?>"><?php echo $Qualification.$row_acce['Name']?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>

                <div class="col-sm-2 form-group BoxRowLabel">
                    <label>Comprende</label>
                </div>
                <div class="BoxRowCaption col-sm-4">
                    <input type="text" class="prefix_1 form-control frm_field_string  bv-form" title="Sono permesse solo lettere!" name="prefix" id="prefix" style="width:8rem;margin-top: -1.1rem;" maxlength="2">
                    <input type="text" class="number form-control frm_field_numeric  bv-form" title="Sono permessi solo numeri!" name="number" id="number" style="width:15rem;margin-top: -1.1rem">
                </div>
            </div>
                <div class="col-sm-1 BoxRow" style="height:4.6rem;">
                    <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                        <i class="search_boletario glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3rem;"></i>
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
        </form>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12 main_block">
        <div class="table_label_H col-sm-1">Numero blocco
            <a href="<?php echo $str_CurrentPage; ?>&order_Numero_Blocco=<?php echo $link_Numero_Blocco; ?>">
                <span class="glyphicon glyphicon-sort SortBtnCodice" style="float: right; padding-top: 0.7rem; padding-right: 0.4rem; color: white"></span>
            </a>
        </div>
        <div class="table_label_H col-sm-1">Lettera
            <a href="<?php echo $str_CurrentPage; ?>&order_Preffix=<?php echo $link_Preffix; ?>">
                <span class="glyphicon glyphicon-sort SortBtnCodice" style="float: right; padding-top: 0.7rem; padding-right: 0.4rem; color: white"></span>
            </a>
        </div>
        <div class="table_label_H col-sm-1">DA/A
        </div>
        <div class="table_label_H col-sm-1">Matricola
            <a href="<?php echo $str_CurrentPage; ?>&order_Code=<?php echo $link_Code; ?>">
                <span class="glyphicon glyphicon-sort SortBtnCodice" style="float: right; padding-top: 0.7rem; padding-right: 0.4rem; color: white"></span>
            </a>
        </div>
        <div class="table_label_H col-sm-4">Accertatore
            <a href="<?php echo $str_CurrentPage; ?>&order_Name=<?php echo $link_Name; ?>">
                <span class="glyphicon glyphicon-sort SortBtnCodice" style="float: right; padding-top: 0.7rem; padding-right: 0.4rem; color: white"></span>
            </a>
        </div>
        <div class="table_label_H col-sm-3">Data
            <a href="<?php echo $str_CurrentPage; ?>&order_Date=<?php echo $link_Date; ?>">
                <span class="glyphicon glyphicon-sort SortBtnCodice" style="float: right; padding-top: 0.7rem; padding-right: 0.4rem; color: white"></span>
            </a>
        </div>
        <div class="table_add_button col-sm-1 right">
            <?php echo ChkButton($aUserButton, 'add','<a href="mgmt_receipt_add.php'.$str_GET_Parameter.'"><span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; color: white;"></span></a>');?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="open_results">
        <?php
        $year = $_SESSION['year'];

        $str_Where = "CityId='".$_SESSION['cityid']."' AND Session_Year=".$_SESSION['year'];
        
        $page_limit = 0;
        $page1 = 0;
        if (isset($_GET['page'])){
            $page1 = $_GET['page'];
            for ($i=1;$i<$page1;$i++){
                $page_limit = $page_limit+25;
            }
        }

        $boletario = $rs->Select('V_Boletario',$str_Where, $strOrder, $page_limit . ',' . PAGE_NUMBER);
        $Row_Number = mysqli_num_rows($boletario);

        if ($Row_Number ==0){
            echo '<div class="table_caption_H col-sm-12">
                Nessun record presente
            </div>';
        }else{
            while ($row_boletario = mysqli_fetch_array($boletario)){
                ?>
                <div class="append_search">
                    <div class="table_caption_H col-sm-1"><?php echo $row_boletario['Numero_blocco']; ?></div>
                    <div class="table_caption_H col-sm-1"><?php echo $row_boletario['Preffix']; ?></div>
                    <div class="table_caption_H col-sm-1"><?php echo $row_boletario['StartNumber']."/".$row_boletario['EndNumber']; ?></div>
                    <div class="table_caption_H col-sm-1"><?php echo $row_boletario['Code']; ?></div>
                    <div class="table_caption_H col-sm-4"><?php echo $name = $row_boletario['Name']!=''? $row_boletario['Name']: 'Promiscuo (Piu Accertatori)'; ?></div>
                    <div class="table_caption_H col-sm-3"><?php echo DateOutDB($row_boletario['Date']); ?></div>
                    <div class="table_caption_button col-sm-1">
                        <?php echo ChkButton($aUserButton, 'upd','<a href="mgmt_receipt_upd.php'.$str_GET_Parameter.'&boletario='.$row_boletario['Id'].'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="top:5px;"></span></a>'); ?>
                        <?php echo ChkButton($aUserButton, 'del','<a href="#Delete"><span data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r delete_boletario glyphicon glyphicon-remove-sign" id="'.$row_boletario['Id'].'" style="top:5px;"></span></a>'); ?>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <?php
            }

            $table_boletario_number = $rs->Select('V_Boletario',$str_Where);
            $BoletarioNumberTotal = mysqli_num_rows($table_boletario_number);
            echo CreatePagination(PAGE_NUMBER, $BoletarioNumberTotal, $page1, $new_url,"");
        }
        ?>
        </div>
    </div>
<div class="test"></div>
</div>


<script>
    $(document).ready(function () {

    	$("body").tooltip({ selector: '[data-toggle=tooltip]' });

        /////////////search
        var acl = [<?php foreach ($aUserButton as $key => $value){ echo "'".$value."',";} ?>];
        console.log(acl);
        $(document).on('click','.search_boletario',function(e){
            var tipo = '';
            if ($('#select_tipo[name="select"]:checked')){
                tipo  = $('#select_tipo[name="select"]:checked').val();
            }

            var prefix = $('#prefix').val();
            var controller = $('#select_controller').val();
            var number = $('#number').val();
            var search = 'search';
            var myData = {"tipo":tipo,"prefix":prefix,"controller":controller,"number":number,"search":search};
            $.ajax({
                url: 'mgmt_receipt_search_ajax.php',
                type: 'POST',
                data: myData,
                success: function(data){
                    $('.open_results').empty();
                    if (data == "NO") {
                        $('.open_results').append(` <div class="table_caption_H col-sm-12 text-center">Non esiste nessun bolletario  con le seguenti specifiche</div>`);
                    }else {

                        $.each(JSON.parse(data), function (i, {Id, Name, Data, Code, Block, Prefix, Extrems}) {
                            if (Name ==null){
                                var name = "Promiscuo";
                            } else{
                                name = Name;
                            }
                            var getdata = Data.split("-");
                            var day = getdata[2];
                            var month = getdata[1];
                            var year = getdata[0];
                            var europiandate = day+"/"+month+"/"+year;
                            $('.open_results').append(
                                `
                                <div class="table_caption_H col-sm-1">${Id}</div>
                                <div class="table_caption_H col-sm-1">${Block}</div>
                                <div class="table_caption_H col-sm-1">${Prefix}</div>
                                <div class="table_caption_H col-sm-1">${Extrems}</div>
                                <div class="table_caption_H col-sm-1">${Code}</div>
                                <div class="table_caption_H col-sm-4">${name}</div>
                                <div class="table_caption_H col-sm-1">${europiandate}</div>
                                <div class="table_caption_button col-sm-2">
                                    ${!!acl.find(a => a === 'upd')
                                    ? `
                                            <a href="mgmt_receipt_upd.php<?php echo $str_GET_Parameter;?>&boletario=${Id}">
                                                <span class="glyphicon glyphicon-pencil" style="left:25px;top:5px;"></span>
                                            </a>
                                            `
                                    : ''
                                    }
                                    ${!!acl.find(b => b === 'del')
                                    ? `
                                             <span class="delete_boletario glyphicon glyphicon-remove-sign" id="${Id}" style="left:40px;top:5px;"></span>
                                            `
                                    : ''
                                    }
                                </div>
                                 <div class="clean_row HSpace4"></div>
                            `
                            );

                        });
                    }

                },

            });
        });
        ///////////delete
        $(document).on('click','.delete_boletario',function(e){
            var deleted_id = $(this).attr('id');
            if (!confirm("Sei sicuro di voler procedere con la cancellazione?")){
                return false;
            }
            var myData = {"deleted_id":deleted_id};
            $.ajax({
                url: 'mgmt_receipt_del.php',
                type: 'POST',
                data: myData,
                success: function(data){
                    $.each(JSON.parse(data), function(i, item) {
                        if(i=='202'){
                            $('.deleted_message').append("<div class='alert alert-danger'>E stato cancellato con successo!</div>");
                            setTimeout(function(){
                                location.reload();
                            }, 1000);
                        }else{
                            alert(item);
                        }
                    });

                },

            });
        });
    });

</script>