<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$rs= new CLS_DB();

echo $str_out;
$cityid = $_SESSION['cityid'];

if(isset($_GET['page'])){
    $page = $_GET['page'];
} else {
    $page = 1;
}
$min = $page*PAGE_NUMBER-PAGE_NUMBER;
$query = "select traffic_law.JudicialOffice.*,traffic_law.Office.* 
        from traffic_law.JudicialOffice 
        join traffic_law.Office on traffic_law.JudicialOffice.OfficeId = traffic_law.Office.Id";
if ($_SESSION['usertype'] >= 50) {
    $judicalOffice = $rs->SelectQuery(" $query order by traffic_law.JudicialOffice.CityId LIMIT ".PAGE_NUMBER." OFFSET ".$min);
    $judicalOfficeCount = $rs->SelectQuery("$query order by traffic_law.JudicialOffice.CityId");
}else{
    $judicalOffice = $rs->SelectQuery("$query where traffic_law.JudicialOffice.CityId = '$cityid' order by traffic_law.JudicialOffice.CityId LIMIT ".PAGE_NUMBER." OFFSET ".$min);
    $judicalOfficeCount = $rs->SelectQuery("$query where traffic_law.JudicialOffice.CityId = '$cityid'");
}
$UserNumberTotal = mysqli_num_rows($judicalOfficeCount);
?>
<div class="" id="table">
    <?php if(isset($_GET['answer'])){
        $answer =$_GET['answer'];
        ?>
        <div class="alert alert-success message">
            <?php echo $answer;?>
        </div>
    <?php }?>
    <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
        <div class="col-sm-1 BoxRowLabel">
            <label>Ufficio</label>
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input type="text" class="frm_field_string form-control bv-form" id="search_ufficio">
        </div>
        <div class="col-sm-2 BoxRowLabel">
            <label>Indirizzo</label>
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" class="frm_field_string form-control bv-form" id="search_indirizio">
        </div>
        <div class="col-sm-2 BoxRowLabel">
            <label>Zip</label>
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" class="frm_field_string form-control bv-form" id="search_zip">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            <label>E-mail</label>
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input type="text" class="frm_field_string form-control bv-form" id="search_email">
        </div>

        <div class="clean_row HSpace4"></div>
    </div>
    <div class="col-sm-1 BoxRow" style="height:4.6rem;">
        <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            <i class="search_button glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:2rem;"></i>
        </div>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="deleted_message"></div>

    <div class="clean_row HSpace4"></div>
    <div class="table_label_H col-sm-2">Ufficio</div>
    <div class="table_label_H col-sm-1">Provincia</div>
    <div class="table_label_H col-sm-2">Indirizzo</div>
    <div class="table_label_H col-sm-2">Zip</div>
    <div class="table_label_H col-sm-2">Fax</div>
    <div class="table_label_H col-sm-2">E-mail</div>

    <div class="table_add_button col-sm-1">
        <?php
        echo ChkButton($aUserButton, 'add','<a href="mgmt_judicial_office_add.php?PageTitle=Gestione/Judical Office Inserimento"><span class="glyphicon glyphicon-plus-sign add_button open_modal" style="margin-right:0.3rem; "></span></a>')
        ?>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12 open_results">
        <?php
        while($row=mysqli_fetch_array($judicalOffice)){
            $officeId = $row['OfficeId'];
            $cityid = $row['CityId'];

            ?>
                <div class="table_caption_H col-sm-2"><?php echo $row['TitleIta'];?></div>
                <div class="table_caption_H col-sm-1"><?php echo $row['Province'];?></div>
                <div class="table_caption_H col-sm-2"><?php echo $row['Address'];?></div>
                <div class="table_caption_H col-sm-2"><?php echo $row['ZIP'];?></div>
                <div class="table_caption_H col-sm-2"><?php echo $row['Fax'];?></div>
                <div class="table_caption_H col-sm-2"><?php echo $row['Mail'];?></div>
                <div class="table_caption_button col-sm-1">
                    <?php
                    echo ChkButton($aUserButton, 'upd','<a href="mgmt_judicial_office_upd.php?PageTitle=Gestione/JudicalOffice&id='.$officeId.'&city='.$cityid.'"><span class="glyphicon glyphicon-pencil" style="font-size:17px;left: 15px; "></span></a>');
                    echo ChkButton($aUserButton, 'del','<span class="glyphicon glyphicon-remove-sign delete" id="'.$officeId.'" City="'.$cityid.'" style="left: 25px;font-size: 17px;"></span>');
                    ?>
                </div>
            <div class="clean_row HSpace4"></div>
            <?php

        }
        ?>
    </div>
    <div class="col-sm-12">
        <?php  echo CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, "mgmt_judicial_office.php?PageTitle=Gestione/JUdical%20Office","");?>
    </div>

</div>
<script>

    $(document).on('click','.delete',function(e){
        var id = $(this).attr('id');
        var city = $(this).attr('City');

        if (!confirm("Sei sicuro di voler procedere con la cancellazione?")){
            return false;
        }
        var myData = {"delete":"delete","id":id,"city":city};
        $.ajax({
            url: 'mgmt_judicial_office_del.php',
            type: 'POST',
            data: myData,
            success: function(data){
               $(".deleted_message").append("<div class='alert alert-danger'>E stato cancellato con successo</div>");
            },

        });
    })
</script>

<script>
    $(document).on('click','.search_button',function(e){

        var ufficio = $('#search_ufficio').val();
        var indirizio = $('#search_indirizio').val();
        var zip = $('#search_zip').val();
        var email = $('#search_email').val();
        if (ufficio !="" || indirizio !="" || email!="" || zip!="") {
            var myData = {"search":"search","ufficio":ufficio,"indirizio":indirizio,"email":email,"zip":zip};
            $.ajax({
                url: 'mgmt_judicial_office_del.php',
                type: 'POST',
                data: myData,
                success: function(data){

                    $('.open_results').empty();
                    $.each(JSON.parse(data), function(i, {Office,Address,Provincia,Fax,Mail,ZIP,CityId}) {
                        $('.open_results').append(`<div class="clean_row HSpace4"></div>
                            <div class="table_caption_H col-sm-2">${Office}</div>
                            <div class="table_caption_H col-sm-1">${Provincia}</div>
                            <div class="table_caption_H col-sm-2">${Address}</div>
                            <div class="table_caption_H col-sm-2">${ZIP}</div>
                            <div class="table_caption_H col-sm-2">${Fax}</div>
                            <div class="table_caption_H col-sm-2">${Mail}</div>
                            <div class="table_caption_button col-sm-1">
                            <a href="mgmt_judicial_office_upd.php?id=${Office}&city=${CityId}">
                                <span class="glyphicon glyphicon-pencil" style="font-size:17px;left: 15px; "></span>
                            </a>
                            <span class="glyphicon glyphicon-remove-sign delete" id="${Office}" City="${CityId}" style="left: 35px;font-size: 17px;"></span></div>`);
                    });

                },

            });
        }else{
            alert("Si prega di inserire una parola!");
        }

    })
</script>