<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$rs= new CLS_DB();

$Search_CityTitle = CheckValue('Search_CityTitle','s');
$Search_Title = CheckValue('Search_Title','s');
$Search_ZIP = CheckValue('Search_ZIP','s');
$Search_Street = CheckValue('Search_Street','s');

echo $str_out;
// $str_PageTitle = $_GET['PageTitle'];
// $a_PageTitle = explode("/",$str_PageTitle);

$min = 1;
if(isset($_GET['page'])){
    $max = $_GET['page'] * 50; 
    $min = $max-50;
   // $zipcity = $rs->SelectQuery("SELECT sarida.ZIPCity.*,sarida.City.Id as C_Id,sarida.City.Title as CityName,sarida.Toponym.Title as ToponymName from sarida.ZIPCity left JOIN sarida.City on sarida.ZIPCity.CityId = sarida.City.Id left JOIN sarida.Toponym ON sarida.ZIPCity.ToponymId=sarida.Toponym.Id 
   //     WHERE sarida.ZIPCity.CityId = '".$_SESSION['cityid']."' order by Id desc LIMIT 50 OFFSET $min");
    $zipcity = $rs->SelectQuery("SELECT sarida.ZIPCity.*,sarida.City.Id as C_Id,sarida.City.Title as CityName,sarida.Toponym.Title as ToponymName from sarida.ZIPCity left JOIN sarida.City on sarida.ZIPCity.CityId = sarida.City.Id left JOIN sarida.Toponym ON sarida.ZIPCity.ToponymId=sarida.Toponym.Id 
          WHERE sarida.ZIPCity.CityId = '*' order by Id desc LIMIT 50 OFFSET $min");
} else {
   // $zipcity = $rs->SelectQuery("SELECT sarida.ZIPCity.*,sarida.City.Id as C_Id,sarida.City.Title as CityName,sarida.Toponym.Title as ToponymName from sarida.ZIPCity left JOIN sarida.City on sarida.ZIPCity.CityId = sarida.City.Id left JOIN sarida.Toponym ON sarida.ZIPCity.ToponymId=sarida.Toponym.Id
   //  WHERE sarida.ZIPCity.CityId = '".$_SESSION['cityid']."' order by Id desc LIMIT 50");
    $zipcity = $rs->SelectQuery("SELECT sarida.ZIPCity.*,sarida.City.Id as C_Id,sarida.City.Title as CityName,sarida.Toponym.Title as ToponymName from sarida.ZIPCity left JOIN sarida.City on sarida.ZIPCity.CityId = sarida.City.Id left JOIN sarida.Toponym ON sarida.ZIPCity.ToponymId=sarida.Toponym.Id 
          WHERE sarida.ZIPCity.CityId = '*' order by Id desc LIMIT 50 OFFSET $min");
}
//$pages = $rs->ExecuteQuery("SELECT * FROM sarida.ZIPCity WHERE sarida.ZIPCity.CityId = '".$_SESSION['cityid']."'");
$pages = $rs->ExecuteQuery("SELECT * FROM sarida.ZIPCity WHERE sarida.ZIPCity.CityId = '*'");
$pages = intval(ceil($pages->num_rows/50));



?>
<div class="" id="table">
    <?php if(isset($_GET['answer'])){
        $answer =$_GET['answer'];
        ?>
        <div class="alert alert-success message">
            <?php echo $answer;?>
        </div>
    <?php }?>
    <div class="deleted_message"></div>
    <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
        <div class="col-sm-1 BoxRowLabel">
            <label>Comune</label>
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input type="text" class="frm_field_string form-control bv-form" value="<?php echo $Search_CityTitle?>" id="search_city" name="Search_CityTitle">
        </div>
        <div class="col-sm-2 BoxRowLabel">
            <label>Denominazione strada</label>
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" class="frm_field_string form-control bv-form" value="<?php echo $Search_Street?>" id="search_street" name="Search_Street">
        </div>
        <div class="col-sm-2 BoxRowLabel">
            <label>Denominazione</label>
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" class="frm_field_string form-control bv-form" value="<?php echo $Search_Title?>" id="search_title" name="Search_Title">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            <label>CAP</label>
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input type="text" class="frm_field_string form-control bv-form" value="<?php echo $Search_ZIP?>" id="search_zip" name="Search_ZIP">
        </div>

        <div class="clean_row HSpace4"></div>
    </div>
    <div class="col-sm-1 BoxRow" style="height:4.6rem;">
        <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            <i class="search_button glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:2rem;"></i>
        </div>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="table_label_H col-sm-1">ID</div>
    <div class="table_label_H col-sm-2">Comune</div>
    <div class="table_label_H col-sm-3">Nome della strada</div>
    <div class="table_label_H col-sm-1">Toponimo</div>
    <div class="table_label_H col-sm-3">Denominazione</div>
    <div class="table_label_H col-sm-1">CAP</div>
    <div class="table_add_button col-sm-1">
        <a href="mgmt_zipcity_add.php?PageTitle=Gestione/Zipcity"><span class="glyphicon glyphicon-plus-sign add_button open_modal" style="margin-right:0.3rem; "></span></a>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12 open_results">
        <?php
        while($row=mysqli_fetch_array($zipcity)){
            $id  =$row['Id'];
            //$streetname = $row['Strada'].' '.$row['StreetName'];
            $streetname = $row['StreetName'];
            $title = $row['Title'];
            $zip = $row['ZIP'];
            $cityname = $row['CityName'];
            $toponymname = $row['ToponymName'];
            ?>
            <div class="append_search">
                <div class="table_caption_H col-sm-1"><?php echo $id;?></div>
                <div class="table_caption_H col-sm-2"><?php echo $cityname ;?></div>
                <div class="table_caption_H col-sm-3"><?php echo $streetname ;?></div>
                <div class="table_caption_H col-sm-1"><?php echo $toponymname;?></div>
                <div class="table_caption_H col-sm-3"><?php echo $title;?></div>
                <div class="table_caption_H col-sm-1"><?php echo $zip;?></div>
                <div class="table_caption_button col-sm-1">
                    <a href="mgmt_zipcity_upd.php?PageTitle=Gestione/Zipcity&zipcity=<?php echo $id;?>">
                        <span class="glyphicon glyphicon-pencil" style="font-size:17px;left: 15px; "></span>
                    </a>
                    <span class="glyphicon glyphicon-remove-sign delete" id="<?php echo $id;?>" style="left: 25px;font-size: 17px;"></span>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <?php
        }
        ?>
    </div>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php
            if(isset($_GET['page'])) $page = $_GET['page'];
            else $page = 1;
            if($page!=1) {
                ?>
                <li class="page-item"><a class="page-link" href="mgmt_zipcity.php?PageTitle=Gestione/Zipcity&page=<?= $page - 1 ?>">Previous</a></li>
                <?php
            }
            ?>
            <li class="page-item"><a class="page-link" href="mgmt_zipcity.php?PageTitle=Gestione/Zipcity&page=<?= $page + 1 ?>">Next</a>
        </ul>
    </nav>
</div>


<script>
    setTimeout(function(){ $('.message').hide()}, 2000);
    $(document).on('click','.delete',function(e){
        var id = $(this).attr('id');
        if (!confirm("Sei sicuro di voler procedere con la cancellazione?")){
            return false;
        }
        var myData = {"delete_zipcity":"delete","id":id};
        $.ajax({
            url: 'mgmt_zipcity_del.php',
            type: 'POST',
            data: myData,
            success: function(data){
                $.each(JSON.parse(data), function(i, item) {
                    if(i=='202'){

                        $('.deleted_message').append("<div class='alert alert-danger'>E stato cancellato con successo!</div>");
                        setTimeout(function(){
                            location.reload();
                        }, 1000);

                    }else if(i=='errore'){
                        alert(item);
                    }
                });

            },

        });
    })
</script>

<script>

    $(document).ready(function () {
    	if ($("#search_city").val() != "" || $("#search_street").val() != "" || $("#search_title").val() != "" || $("#search_zip").val() != ""){
    		$(".search_button").click()
    	}
    });

    $(document).on('click','.search_button',function(e){

    	var params = "";
        var city = $('#search_city').val();
        if (city != "") params += "&Search_CityTitle=" + city;
        var street = $('#search_street').val();
        if (street != "") params += "&Search_Street=" + street;
        var title = $('#search_title').val();
        if (title != "") params += "&Search_Title=" + title;
        var zip = $('#search_zip').val();
        if (zip != "") params += "&Search_ZIP=" + zip;

        $('.add_button').parent().attr('href',function(i,str) {
     	   return str + params;
     	});
        
        if (city !="" || street !="" || title!="" || zip!="") {
            var myData = {"city":city,"street":street,"title":title,"zip":zip,"params":params};
            $.ajax({
                url: 'mgmt_zipcity_search.php',
                type: 'POST',
                data: myData,
                success: function(data){
                    console.log(data);
                    $('.open_results').empty();
                    $.each(JSON.parse(data), function(i, {Id,City,StreetName,Toponimo,Title,ZIP,Params}) {
                        $('.open_results').append(`<div class="clean_row HSpace4"></div>
                            <div class="table_caption_H col-sm-1">${Id}</div>
                            <div class="table_caption_H col-sm-2">${City}</div>
                            <div class="table_caption_H col-sm-3">${StreetName}</div>
                            <div class="table_caption_H col-sm-1">${Toponimo}</div>
                            <div class="table_caption_H col-sm-3">${Title}</div>
                            <div class="table_caption_H col-sm-1">${ZIP}</div>
                            <div class="table_caption_button col-sm-1">
                            <a href="mgmt_zipcity_upd.php?PageTitle=Gestione/Zipcity${Params}&zipcity=${Id}">
                                <span class="glyphicon glyphicon-pencil" style="font-size:17px;left: 15px; "></span>
                            </a>
                            <span class="glyphicon glyphicon-remove-sign delete" id="${Id}" style="left: 35px;font-size: 17px;"></span></div>`);
                    });

                },

            });
        }else{
        	if(city == null || city == "")
           		alert("Si prega di inserire un comune");
        }

    })
</script>