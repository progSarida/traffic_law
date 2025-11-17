<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC . "/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$FormTypeId = CheckValue('FormTypeId', 'n');
$CityId = CheckValue('CityId', 's');
$RuleTypeId = CheckValue('RuleTypeId', 's');
$NationalityId = CheckValue('NationalityId', 's');
$LanguageId = CheckValue('LanguageId', 's');
$Deleted = CheckValue('Deleted', 's');
$Title = CheckValue('Title', 's');

$Base=CheckValue('Base','s');

$charge_rows = $rs->Select("FormDynamic", "FormTypeId=$FormTypeId AND Title='$Title' AND CityId='$CityId' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=$LanguageId AND Deleted=$Deleted");

$row = mysqli_fetch_array($charge_rows);

$content = StringOutDB($row['Content']);


$content = str_replace("", "&euro;", $content);

$rs_lang = $rs->Select("Language", "Id=".$row['LanguageId']);
$r_lang = mysqli_fetch_array($rs_lang);
$rs_rule = $rs->Select(MAIN_DB.".Rule", "Id=".$row['RuleTypeId']);
$r_rule = mysqli_fetch_array($rs_rule);
$Nationality = ($row['NationalityId'] == 1) ? "Nazionale" : "Straniero";
    
//     $rs_Keywords = $rs->Select("FormKeyword", "FormTypeId=". $FormTypeId ." AND LanguageId=". $row['LanguageId'] ." AND RuleTypeId=". $row['RuleTypeId'] ." AND CityId='".$CityId."' AND Disabled=0 AND Deleted=0 ORDER BY Title");
//     $rs_Variables = $rs->Select("FormVariable", "FormTypeId=". $FormTypeId ." AND LanguageId=". $row['LanguageId'] ." AND RuleTypeId=". $row['RuleTypeId'] ." AND CityId='".$CityId."' AND Disabled=0");

//     $rs_Keywords = $rs->SelectQuery("SELECT DISTINCT
//         COALESCE(t2.Title, t1.Title) AS Title, COALESCE(t2.Description, t1.Description) AS Description
//         FROM FormKeyword t1
//         LEFT OUTER JOIN FormKeyword t2 on t2.CityId = '".$CityId."' AND t2.FormTypeId=". $FormTypeId ." AND t2.NationalityId=". $NationalityId ." AND t2.LanguageId=". $row['LanguageId'] ." AND t2.RuleTypeId=". $row['RuleTypeId'] ." AND t2.Disabled=0 AND t2.Deleted=0
//         WHERE t1.CityId = '' AND t1.FormTypeId=". $FormTypeId ." AND t1.NationalityId=". $NationalityId ." AND t1.LanguageId=". $row['LanguageId'] ." AND t1.RuleTypeId=". $RuleTypeId ." AND t1.Disabled=0 AND t1.Deleted=0 ORDER BY Title");

$rs_Keywords = $rs->Select("FormKeyword", "FormTypeId=". $FormTypeId ." AND LanguageId=". $row['LanguageId'] ." AND RuleTypeId=". $row['RuleTypeId'] ." AND CityId='' AND NationalityId=". $NationalityId ." AND Disabled=0 AND Deleted=0 ORDER BY Title");

$rs_Variables = $rs->SelectQuery("SELECT DISTINCT
    COALESCE(t2.Id, t1.Id) AS Id, COALESCE(t2.Description, t1.Description) AS Description
    FROM FormVariable t1
    LEFT OUTER JOIN FormVariable t2 on t2.CityId = '".$CityId."' AND t2.FormTypeId=". $FormTypeId ." AND t2.NationalityId=". $NationalityId ." AND t2.LanguageId=". $row['LanguageId'] ." AND t2.RuleTypeId=". $row['RuleTypeId'] ." AND t2.Disabled=0
    WHERE t1.CityId = '' AND t1.FormTypeId=". $FormTypeId ." AND t1.NationalityId=". $NationalityId ." AND t1.LanguageId=". $row['LanguageId'] ." AND t1.RuleTypeId=". $RuleTypeId ." AND t1.Disabled=0");

$r_Variables = mysqli_fetch_all($rs_Variables,MYSQLI_ASSOC);
$a_Variables = array();

echo $str_out;
?>


    <div class="col-md-10 col-md-offset-1">
    	<div class="col-md-12">
            <div class="panel panel-primary" style="margin-top: 20px;">
            	<div class="panel-heading"><h5 style="margin:0;">Parametri di testo</h5></div>
            	<div class="panel-body">
                	<div class="col-sm-12">
                        <div class="form-group col-lg-4">
                        	<label>Genere</label>
                        	<div><?php echo $r_rule['Title'] ?></div>
                        </div>
                        <div class="form-group col-lg-4">
                        	<label>Nazionalità</label>
                        	<div><?php echo $Nationality ?></div>
                        </div>
                        <div class="form-group col-lg-4">
                        	<label>Lingua</label>
                        	<div><?php echo $r_lang['Title'] ?></div>
                        </div>
                        <div class="form-group col-lg-12">
                        	<label>Titolo</label>
                        	<div><?php echo StringOutDB($row['Title']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12" style="display: flex;margin-bottom: 30px;">
            <div class="panel panel-primary" style="flex: 1;display: flex;flex-direction: column;">
            	<div class="panel-heading"><h5 style="margin:0;">Testo</h5></div>
                    <form method="post" action="tbl_form_upd_exe.php" accept-charset="UTF-8" enctype='multipart/form-data'>
                    <input type="hidden" name="Filters" value="<?= $str_GET_Parameter.'&Base='.$Base; ?>">
                	<input type="hidden" name="FormTypeId" value="<?php echo $row['FormTypeId'];?>">
                	<input type="hidden" name="CityId" value="<?php echo $row['CityId'];?>">
                	<input type="hidden" name="RuleTypeId" value="<?php echo $row['RuleTypeId'];?>">
                	<input type="hidden" name="NationalityId" value="<?php echo $row['NationalityId'];?>">
                	<input type="hidden" name="LanguageId" value="<?php echo $row['LanguageId'];?>">
                	<input type="hidden" name="Deleted" value="<?php echo $row['Deleted'];?>">
                	<input type="hidden" name="Title" value="<?php echo $row['Title'];?>">

                    <div class="panel-body">

                        <textarea  id="Content" name="Content" rows="50" class="form-control"><?php  echo $content; ?></textarea>
                        

                        <div class="panel-footer">
                            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                                <button type="submit" name="update" class="btn btn-success ">Modifica</button>
                                <button type="button" class="btn btn-default" id="back">Indietro</button>
                            </div>
                        </div>
                        <?php

                        ?>
					</div>
                </form>
            </div>
            <div style="flex: 0.4;display: flex;flex-direction: column;">
                <div class="panel panel-info" style="flex-grow: 1;margin: 0;max-height: 50%;display: flex;flex-direction: column;">
                    <div class="panel-heading"><h5 style="margin:0;">Variabili</h5></div>
                    <ul class="list" id="keywords" style="overflow: scroll;height: 500px;">
        				<?php
        				while ($r_Keyword = mysqli_fetch_array($rs_Keywords)){
        				    echo '<li class="list-group-item"><b>'.StringOutDB($r_Keyword['Title']).'</b> -> '.StringOutDB($r_Keyword['Description']).'</li>';
        				}
        				?>
                    </ul>
                </div>
                <div class="panel panel-danger" style="flex-grow: 1;max-height: 50%;display: flex;flex-direction: column;">
                    <div class="panel-heading"><h5 style="margin:0;">Sottotesti</h5></div>
                    <ul class="list" id="variables" style="overflow: scroll;height: 500px;">
        				<?php
//         				while ($r_Variable = mysqli_fetch_array($rs_Variables)){
//         				    echo '<li class="list-group-item"><b>'.StringOutDB($r_Variable['Id']).'</b> -> '.StringOutDB($r_Variable['Description']).'</li>';
//         				}
        				foreach ($r_Variables as $values) {
        				    $a_Variables[$values['Id']][] = $values['Description'];
        				}
        				
        				foreach ($a_Variables as $Variable => $Description){
        				    echo '<li class="list-group-item"><b>'.StringOutDB($Variable).'</b> -> ';
        				    foreach ($Description as $Value){
        				        echo '</br>'.StringOutDB($Value);
        				    }
        				    echo '</li>';
        				}
//         				?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    

    <script>
        var edit = CKEDITOR.replace('Content', {

            customConfig: '',
            filebrowserBrowseUrl: './ckfinder/ckfinder.html',
            filebrowserImageBrowseUrl: './ckfinder/ckfinder.html?type=Images',
            disallowedContent: 'img{width,height,float}',
            extraAllowedContent: 'img[width,height,align];span{background}',
            extraPlugins: 'colorbutton,font,justify,print,tableresize,uploadimage,pastefromword,liststyle',
            height: 800,
            contentsCss: [
                'https://cdn.ckeditor.com/4.11.3/full-all/contents.css',
                'assets/css/pastefromword.css'
            ],
        });
        //edit.config.allowedContent = true;
        edit.config.removePlugins = 'Source';
        edit.config.forcePasteAsPlainText = true;
        edit.config.enterMode = CKEDITOR.ENTER_BR;
        edit.config.fontSize_sizes = '4/4px;5/5px;6/6px;7/7px;'+edit.config.fontSize_sizes;
        //edit.config.disallowedContent = 'row';
        edit.execCommand( 'shiftEnter' );

        
    </script>
    
<script type="text/javascript">


$('document').ready(function(){
    $('#back').click(function () {
    	window.location = "<?= impostaParametriUrl(array('Filter' => 1, 'Base' => $Base), $str_BackPage) ?>";
        return false;
    });
	
	$.fn.insertAtCaret = function (myValue) {
	    myValue = myValue.trim();
	    CKEDITOR.instances['Content'].insertText(myValue);
	};
	
    $(document).on("dblclick", "#keywords li b, #variables li b", function(){
    	$("#Content").insertAtCaret($(this).text());
    });
    
});

</script>

<?php
include(INC."/footer.php");
