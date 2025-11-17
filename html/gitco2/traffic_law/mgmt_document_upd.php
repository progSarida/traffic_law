<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//$a_AllowedTypes = unserialize(GENERIC_DOCUMENT_ALLOWED_TYPES);
$Id= CheckValue('Id','n');
$Pos= CheckValue('pos','n');

$str_Where = '1=1';
$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];

if ($Search_FromProtocolId != '')
    $str_Where .= " AND ProtocolId  >= '" . $Search_FromProtocolId . "'";
if ($Search_ToProtocolId != '')
    $str_Where .= " AND ProtocolId  <= '" . $Search_ToProtocolId . "'";
if ($Search_Id > 0)
    $str_Where .= " AND FineId  = $Search_Id";
if ($s_TypePlate != ''){
    if ($s_TypePlate == "N") {
        $str_Where .= " AND VehicleCountryId='Z000'";
    } else {
        $str_Where .= " AND VehicleCountryId!='Z000'";
    }
}
if($Search_Plate != "")
    $str_Where .= " AND VehiclePlate LIKE '%".addslashes($Search_Plate)."%' ";
if($Search_Ref != "")
    $str_Where .= " AND Code LIKE '".addslashes($Search_Ref)."%'";
if($Search_Trespasser != '')
    $str_Where .= " AND CONCAT_WS(' ',CompanyName,Surname,Name) like '%{$Search_Trespasser}%'";
if($Search_Violation>0)
    $str_Where .= " AND ViolationTypeId=".$Search_Violation;

$viw_Mgmt_FineDocumentation = new CLS_VIEW(MGMT_FINEDOCUMENTATION);
$str_Query = $viw_Mgmt_FineDocumentation->generateSelect(" FineId=".$Id);
$rs_Fine = $rs->SelectQuery($str_Query);
$r_Fine = mysqli_fetch_array($rs_Fine);

$next_Pos = $Pos+1;
$previous_Pos = $Pos-1;


$str_Next = "";
$str_Previous = "";

$viw_Mgmt_FineDocumentation = new CLS_VIEW(MGMT_FINEDOCUMENTATION);
$str_Query = $viw_Mgmt_FineDocumentation->generateSelect($str_Where, null, 'ProtocolId DESC', "$next_Pos, 2");
$rs_nextId = $rs->SelectQuery($str_Query);
$r_nextId = mysqli_fetch_array($rs_nextId);
if (!is_null($r_nextId)){
    $str_Next = '<a href="'.$str_GET_FilterPage.'&Id='.$r_nextId['FineId'].'&pos='.$next_Pos.'"><span data-toggle="tooltip" data-placement="top" title="Avanti" class="tooltip-r glyphicon glyphicon-arrow-right" style="font-size:2rem;color:#fff"></span></a>';
}

if ($previous_Pos >= 0) {
    $viw_Mgmt_FineDocumentation = new CLS_VIEW(MGMT_FINEDOCUMENTATION);
    $str_Query = $viw_Mgmt_FineDocumentation->generateSelect($str_Where, null, 'ProtocolId DESC', "$previous_Pos, 1");
    $rs_previousId = $rs->SelectQuery($str_Query);
    $r_previousId = mysqli_fetch_array($rs_previousId);
    $str_Previous = '<a href="'.$str_GET_FilterPage.'&Id='.$r_previousId['FineId'].'&pos='.$previous_Pos.'"><span data-toggle="tooltip" data-placement="top" title="Indietro" class="tooltip-r glyphicon glyphicon-arrow-left" style="font-size:2rem;color:#fff"></span></a>';
}


$str_Trespasser = $r_Fine['CompanyName'] .' '.$r_Fine['Surname'] .' '.$r_Fine['Name'];
$str_Trespasser = (strlen($str_Trespasser)>42) ? substr($str_Trespasser,0,40).'...' : $str_Trespasser;

$str_Folder = ($r_Fine['CountryId']=='Z000') ? 'doc/national/fine' : 'doc/foreign/fine';
$str_DisputeFolder = ($r_Fine['CountryId']=='Z000') ? 'doc/national/dispute' : 'doc/foreign/dispute';

$str_tree = "";
$a_documents = array();
$s_select_FD = "SELECT FD.Id AS Id, Documentation, Note, FineId, FD.DocumentationTypeId AS DocumentationTypeId, Title, FD.Attachment ";
$s_From_FD = "FROM FineDocumentation as FD INNER JOIN DocumentationType as DT on FD.DocumentationTypeId = DT.Id ";

$s_select_FP = "SELECT FP.Id AS Id, Documentation, Note, FineId, FP.DocumentationTypeId AS DocumentationTypeId, Title, FP.Attachment ";
$s_From_FP = "FROM FinePresentation as FP INNER JOIN DocumentationType as DT on FP.DocumentationTypeId = DT.Id ";

$s_select_DD = "SELECT DD.Id AS Id, Documentation, '' AS Note, FineId, 60 AS DocumentationTypeId, 'Ricorso' AS Title, DD.Attachment ";
$s_From_DD = "FROM DisputeDocumentation as DD ";

$str_where = "WHERE FineId=$Id";

$doc_rows = $rs->SelectQuery("$s_select_FD $s_From_FD $str_where UNION $s_select_FP $s_From_FP $str_where UNION $s_select_DD $s_From_DD $str_where");
$doc_n = mysqli_num_rows($doc_rows);

$str_out .= '
        <form name="f_doc_upd" id="f_doc_upd" action="mgmt_document_upd_exe.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="FineId" id="FineId" value="' . $Id . '">
    	<div class="row-fluid">
        	<div class="col-sm-6">
              	<div class="col-sm-1 BoxRowCaption">
    				' . $str_Previous . '
				</div>
  	            <div class="col-sm-10" >
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;background-color: #294A9C;">
        				DATI
					</div>
  				</div>
              	<div class="col-sm-1 BoxRowCaption">
    				' . $str_Next . '
				</div>
    
                <div class="clean_row HSpace4"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Cronologico
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . $r_Fine['ProtocolId'] . '
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Targa
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . StringOutDB($r_Fine['VehiclePlate']) .'
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . DateOutDB($r_Fine['FineDate']) . '
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . TimeOutDB($r_Fine['FineTime']) . '
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>';
if($doc_n>0){
$str_out .= '   <div class="col-sm-12 collapse-div" id="accordion" >
                    <div class="col-sm-11 BoxRowLabel" style="text-align:center;border-right: solid white 1px;background-color: #294A9C;">
        				LISTA DOCUMENTI
					</div>
                    <div class="col-sm-1 BoxRowLabel" style="text-align:center;background-color: #294A9C;">
        				Allega
					</div>
                    <div class="clean_row HSpace4"></div>
        ';
        
        while ($doc_row = mysqli_fetch_array($doc_rows)){
            $a_documents[] = $doc_row;
            $path = getDocumentationPath($r_Fine['VehicleCountryId'],$doc_row['DocumentationTypeId'],$r_Fine['FineId'],$_SESSION['cityid'],$doc_row['Documentation']);  
            
            //TODO controllare se funziona correttamente
            //Recupera il tipo mime del file, se non viene trovato il file valorizza con stringa vuota
            $MimeType = @mime_content_type($path) ? mime_content_type($path) : '';
            
            $str_out .= '<div id="FileName_'.$doc_row['Id'].'" class="col-sm-12 collapse-header" >
                    <div class="col-sm-1 BoxRowLabel">
                        Tipo
                    </div>
                    <div id="docType" class="col-sm-2 BoxRowCaption" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        '.$doc_row['Title'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Nome documento
                    </div>
                    <div class="col-sm-4 BoxRowCaption" style="white-space:nowrap;overflow: hidden;text-overflow: ellipsis;width:40%">'.$doc_row['Documentation'].'
                    </div>
                    <div class="col-sm-1 BoxRowHTitle" style="padding:0;text-align:center;background-color: #294A9C;color:white;padding-top:4px;width:5%">
                        <i class="fas fa-angle-down caret-toggle" id="heading'.$doc_row['Id'].'" data-toggle="collapse" data-target="#collapse'.$doc_row['Id'].'" aria-expanded="false" aria-controls="collapse'.$doc_row['Id'].'"></i>
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="padding-top:3px;width:5%;">';
                    if ($MimeType === 'application/msword')
                        $str_out .= '<span data-toggle="tooltip" data-placement="top" title="Scarica" data-mimetype="'.$MimeType.'" path="'.$path.'" docid="'.$doc_row['Id'].'" doctype="'.$doc_row['DocumentationTypeId'].'" class="tooltip-r glyphicon glyphicon-arrow-down col-sm-12" style="text-align:center;"></span>';
                    else
                        $str_out .= '<span data-toggle="tooltip" data-placement="top" title="Visualizza" data-mimetype="'.$MimeType.'" path="'.$path.'" docid="'.$doc_row['Id'].'" doctype="'.$doc_row['DocumentationTypeId'].'" class="tooltip-r glyphicon glyphicon-eye-open col-sm-12" style="text-align:center;"></span>';

$str_out .= '       </div>
                    <div id="docType" class="col-sm-1 BoxRowCaption" style="text-align: center;"><input type="checkbox" name="Attach['.$doc_row['Id'].']" value="'.$doc_row['Attachment'].'" '.($doc_row['Attachment'] ? 'checked':'').'/>'.'
                    <input type="hidden" name="DocId['.$doc_row['Id'].']" value="'.$doc_row['Id'].'" />'.'
                    <input type="hidden" name="DocTypeId['.$doc_row['Id'].']" value="'.$doc_row['DocumentationTypeId'].'" />'.'
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
  	            <div class="col-sm-12 collapse" id="collapse'.$doc_row['Id'].'" aria-labelledby="heading'.$doc_row['Id'].'" data-parent="#accordion">
                        <div class="col-sm-12 BoxRowLabel" style="text-align:center">
            				NOTE
    					</div>
                        <div id="note" class="col-sm-12 BoxRowCaption" style="min-height:10rem;">
                            '.$doc_row['Note'].'
                        </div>
                        <div class="clean_row HSpace4"></div>';
                    if($doc_row['DocumentationTypeId'] == 20
                        ||($doc_row['DocumentationTypeId'] >=25 && $doc_row['DocumentationTypeId'] <=28)){
                    $str_out .= '  <div class="col-sm-12">
                                        <div class="col-sm-3 BoxRowLabel">
                                            Nominativo
                                        </div>
                                        <div class="col-sm-3 BoxRowCaption">
                                            ' . StringOutDB($str_Trespasser) . '
                                        </div>
                                        <div class="col-sm-6 BoxRowLabel">
                                        </div>
                                    </div>';
                    }
            $str_out .= '    </div>';
    }
    $str_out .= '</div>';
}
$str_out .= '</div>';


$str_out .= '
    
        <div class="col-sm-6">
            <div class="col-sm-12 BoxRowLabel" style="text-align:center;background-color: #294A9C;">
                DOCUMENTAZIONE
            </div>
            <div id="File" class="col-sm-8 BoxRow" style="text-align:center;"></div>
            <div id="FileType" class="col-sm-3 BoxRow" style="text-align:center;background-color: #287296;"></div>
            <div id="TypeIcon" class="col-sm-1 BoxRow" style="text-align:center;background-color: #294A9C;"></div>';

if ($doc_n <= 0){
    $str_out .= '
        <div class="col-sm-12 BoxRow BoxRowLabel" style="height:10rem;text-align:center;">
            <h2>Nessun documento trovato</h2>
        </div>
            ';
}

$str_out .= '
    
            <div class="clean_row HSpace4"></div>
    
            <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem; position:relative;">
                <i id="Missing" class="fas fa-file-excel" style="display:none;position: absolute;left: 35%;top: 50%;font-size: 40rem;line-height: inherit;opacity: 0.2;"></i>
                <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
                    <img id="preview" class="iZoom"  />
                    <iframe id="preview_iframe_img" style="display:none;"  ></iframe>
                </div>
                <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12" style="text-align:center;line-height:6rem;background-color: rgb(40, 114, 150);">
            <button'.($doc_n <= 0 ? ' disabled' : '').' class="btn btn-success" id="save" type="submit">Salva</button>
            <button type="button" class="btn btn-default" id="back">Indietro</button>
        </div>
  	</div>
     </form>
</div>';

echo $str_out;

?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.3.200/pdf.min.js"></script>

<script type="text/javascript">
    $('document').ready(function(){
    	var DocId;

//     	$('.collapse::first').collapse({
//     		  toggle: true
//     	});
    	
        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        setTimeout(function() {
        	$("#accordion .glyphicon-eye-open").first().click();
        },10);

        $('#back').click(function(){
            window.location="<?= $str_BackPage.$str_GET_Parameter ?>"
        });

        $(".fa-cogs").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });

        $('.caret-toggle').on("click", function(){
        	$(this).toggleClass('fa-angle-up fa-angle-down');
        });

        $('.glyphicon-eye-open, .glyphicon-arrow-down').on("click", function(){
            var PrevDocId = DocId;
            DocId = $(this).attr('docid');
        	var file = $(this).attr('path');
        	var FileName = file.split("/").pop();
        	var FileType = FileName.split('.').pop();
        	
       		if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc' || FileType.toLowerCase()=='html'){     
                $("#preview_img").hide();
                $("#preview_doc").html("<iframe style='width:100%; height:100%; background:white' src='"+file+"'></iframe>");
                $("#preview_doc").show();
            } else {
                $("#preview_doc").hide();
                $("#preview").attr("src",file);
                $("#preview_iframe_img").attr("src",file);
                $("#preview_img").show();
            }

            if (DocId != PrevDocId){
                $('#FileName_' + DocId + ' .BoxRowCaption').css("background-color", "#eaacc1");
                $('#FileName_' + DocId + ' .BoxRowLabel').css("background-color", "#96283c");
                $('#FileName_' + DocId + ' .BoxRowHTitle').css("background-color", "#6d1830");
                $('#FileName_' + PrevDocId + ' .BoxRowCaption').css("background-color", "");
                $('#FileName_' + PrevDocId + ' .BoxRowLabel').css("background-color", "");
                $('#FileName_' + PrevDocId + ' .BoxRowHTitle').css("background-color", "#294A9C");
            }

            $('#File').html(FileName);

			switch(FileType){
				case "application/pdf":
	            	$('#FileType').html('File PDF');
            		$('#TypeIcon').html('<i class="fas fa-2x fa-file-pdf"></i>');
            		break;
				case "image/jpeg":
				case "image/png":
	            	$('#FileType').html('Immagine');
            		$('#TypeIcon').html('<i class="fas fa-2x fa-image"></i>');
            		break;
        		case "application/msword":
	            	$('#FileType').html('File DOC');
            		$('#TypeIcon').html('<i class="fas fa-2x fa-file-word"></i>');
            		break;
        		case "text/html":
	            	$('#FileType').html('File HTML');
            		$('#TypeIcon').html('<i class="fas fa-2x fa-file-code"></i>');
            		break;
        		default:
	            	$('#FileType').html('Non riconosciuto');
            		$('#TypeIcon').html('<i class="fas fa-2x fa-question-circle"></i>');
			}
            
            $('#Missing').hide();
            $('#preview').show();
        });

        $('img').on("error", function() {
            $(this).hide();
            $('#Missing').show();
      	});

        $(".fa-angle-up, .fa-angle-down, .glyphicon-eye-open, .glyphicon-arrow-down").hover(function(){
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("cursor","");
        });

    });

//     $(window).load(function(){
//         $(".jqueryFileTree div:first a").trigger("click");
//         });

</script>

<?php
include(INC."/footer.php");
