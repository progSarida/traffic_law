<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC."/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Search_Year = CheckValue('Search_Year','s');
$Search_CityId = CheckValue('Search_CityId','s');
$PageTitle = CheckValue('PageTitle','s');
$Filter = CheckValue('Filter','s');

$str_where = "1=1";

if ($Search_CityId != ''){
    $str_where .= " AND UC.CityId='".$Search_CityId."'";
}

$chh_FindFilter = trim($str_where);

$rs_UserCity = $rs->SelectQuery(
    "SELECT 
    UC.UserId UserId,
    UC.CityYear CityYear,
    UC.CityId CityId,
    U.UserName UserName 
    FROM sarida.UserCity UC
    JOIN sarida.User U on UC.UserId = U.Id
    JOIN sarida.City C on UC.CityId = C.Id 
    WHERE ".$str_where." ORDER BY U.UserName,UC.CityYear desc");
$RowNumber = mysqli_num_rows($rs_UserCity);

$UserId = null;
$NoResults = true;

$a_UserIds = array();
while ($Result = mysqli_fetch_assoc($rs_UserCity)){
    if ($Result['CityYear'] == $Search_Year){
        if (!in_array($Result['UserId'], $a_UserIds))
            $a_UserIds[] = $Result['UserId'];
    }
}
mysqli_data_seek($rs_UserCity, 0);

echo $str_out;

?>

<div class="row-fluid">
	<form id="f_usercity" action="tbl_customer_year_add.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <input type="hidden" name="Filter" value="1">
        <input type="hidden" name="CityId" value="<?= $Search_CityId; ?>">
        <input type="hidden" name="CityYear" value="<?= $Search_Year; ?>">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <div>
            <div class="col-sm-11 BoxRow" style="height:4.6rem;">
                <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;font-size: large;line-height: 4rem;">
                    Ente
                </div>
                <div class="col-sm-3 BoxRowCaption" style="height:4.6rem;">
                    <select id="Search_CityId" name="Search_CityId" class="form-control frm_field_required" style="height:4rem;font-size:large;">
                    	<?php
                    	$cities = $rs->SelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle;");
                    	while($city = mysqli_fetch_array($cities)){
                    	    if ($Search_CityId != ""){
                    	        echo '<option'.($city['CityId'] == $Search_CityId ? " selected" : "" ).' value="'.$city['CityId'].'">'.$city['CityTitle'].'</option>';
                    	    } else {
                    	        echo '<option'.($city['CityId'] == $_SESSION['cityid'] ? " selected" : "" ).' value="'.$city['CityId'].'">'.$city['CityTitle'].'</option>';
                    	    }
                    	}
                    	?>
                    </select>
                </div>
                <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;font-size: large;line-height: 4rem;">
                    Anno da inserire
                </div>
                <div class="col-sm-3 BoxRowCaption" style="height:4.6rem;">
    	        	<select name="Search_Year" id="Search_Year" class="form-control frm_field_required" style="height:4rem;font-size:large;">
                    	<?php 
                    	foreach (range(2000, 2050) as $number) {
                    	    if ($Search_Year != '')
                    	        echo '<option'.($Search_Year == $number ? ' selected' : '').' value="'.$number.'">' . $number . "</option>";
                    	    else
                    	        echo '<option'.($_SESSION['year'] == $number ? ' selected' : '').' value="'.$number.'">' . $number . "</option>";
                    	}
                    	?>
                	</select>
                </div>
        		<div class="col-sm-2 BoxRowCaption" style="height:4.6rem;line-height: 4rem;">
        		</div>
            </div>
            <div class="col-sm-1 BoxRow" style="height:4.6rem;">
                <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
                </div>
            </div>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="table_label_H col-sm-1">Id</div>
        <div class="table_label_H col-sm-9">Utente</div>
        <div class="table_label_H col-sm-1">Anno</div>
        <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll"/></div>
        
        <?php if($chh_FindFilter=="1=1" && $Filter!=1):?>
	        <div class="table_caption_H col-sm-12" style="font-size:2rem;color:orange;text-align: center">
        		Inserire criteri ricerca
        	</div>
        <?php else: ?>
            <?php if ($RowNumber > 0): ?>
    			<?php while ($r_UserCity = mysqli_fetch_assoc($rs_UserCity)): ?>
    				<?php if (!in_array($r_UserCity['UserId'], $a_UserIds)):?>
    					<?php $NoResults = false; ?>
        				<?php if ($UserId != $r_UserCity['UserId']): ?>
        					<div class="clean_row HSpace4"></div>
                            <div class="tableRow">
                            	<div class="table_caption_H col-sm-1"><?= $r_UserCity['UserId']; ?></div>
            					<div class="table_caption_H col-sm-9"><?= $r_UserCity['UserName']; ?></div>
            					<div class="table_caption_H col-sm-1"><?= $r_UserCity['CityYear']; ?></div>
            					<div class="table_caption_H col-sm-1 text-center">
            						<input type="checkbox" name="check[]" value="<?= $r_UserCity['UserId']; ?>">
            					</div>
                            </div>
                        <?php else: ?>
                            <div class="tableRow">
                            	<div class="table_caption_H col-sm-1"></div>
            					<div class="table_caption_H col-sm-9"></div>
            					<div class="table_caption_H col-sm-1"><?= $r_UserCity['CityYear']; ?></div>
            					<div class="table_caption_H col-sm-1"></div>
                            </div>
                        <?php endif; ?>
                    <?php endif;?>
                    <?php $UserId = $r_UserCity['UserId']; ?>
                <?php endwhile; ?>
                <?php if($NoResults): ?>
	            	<div class="table_caption_H col-sm-12">
                		Nessun record da mostrare.
                	</div>
                	<div class="clean_row HSpace4"></div>
                <?php else: ?>
                	<div class="clean_row HSpace4"></div>
            	    <div class="table_label_H HSpace4" style="height:8rem;">
                    	<?= ChkButton($aUserButton, 'add','<button type="submit" id="insert" class="btn btn-success" style="margin-top:2rem;" disabled>Inserisci</button>'); ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
            	<div class="table_caption_H col-sm-12">
            		Nessun record da mostrare.
            	</div>
            	<div class="clean_row HSpace4"></div>
            <?php endif; ?>
        <?php endif; ?>
	</form>
</div>

<script type="text/javascript">

	$(document).ready(function () {

		$('#insert').on('click', function(e){
			var id = $(this).attr('id')

			if (id == 'insert'){
				if (confirm('Si sta per abilitare l\'anno specificato per gli utenti selezionati, continuare?')){
					$('#f_usercity').attr('action', 'tbl_customer_year_add_exe.php');
					$('#f_usercity').submit();
				} else {
					e.preventDefault();
					return false;
				}
			} else {
				$('#f_usercity').submit();
			}
		});

	    $('#f_usercity').bootstrapValidator({
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
	        }
	    });

        $('#checkAll').click(function() {
            $('input[name=check\\[\\]]').prop('checked', this.checked);
            $("#f_usercity").trigger( "check" );
        });

        $('input[name=check\\[\\]]').change(function() {
            $("#f_usercity").trigger( "check" );
        });

        $("#f_usercity").on('check', function(){
        	if ($('input[name=check\\[\\]]:checked').length > 0)
        		$('#insert').prop('disabled', false);
        	else
        		$('#insert').prop('disabled', true);
        });
	    
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

	});
</script>

<?php
include(INC."/footer.php");

