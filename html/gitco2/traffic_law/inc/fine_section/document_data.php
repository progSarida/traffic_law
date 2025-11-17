<?php



//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         PAYMENT
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////


$str_CSSDocument = 'data-toggle="tab"';
$str_Document = "";

$s_select_FD = "SELECT FD.Id AS Id, Documentation, Note, FineId, FD.DocumentationTypeId AS DocumentationTypeId, Title, FD.Attachment ";
$s_From_FD = "FROM FineDocumentation as FD INNER JOIN DocumentationType as DT on FD.DocumentationTypeId = DT.Id ";

$s_select_FP = "SELECT FP.Id AS Id, Documentation, Note, FineId, FP.DocumentationTypeId AS DocumentationTypeId, Title, FP.Attachment ";
$s_From_FP = "FROM FinePresentation as FP INNER JOIN DocumentationType as DT on FP.DocumentationTypeId = DT.Id ";

$s_select_DD = "SELECT DD.Id AS Id, Documentation, '' AS Note, FineId, 60 AS DocumentationTypeId, 'Ricorso' AS Title, DD.Attachment ";
$s_From_DD = "FROM DisputeDocumentation as DD ";

$str_where = "WHERE FineId=$Id";

$doc_rows = $rs->SelectQuery("$s_select_FD $s_From_FD $str_where UNION $s_select_FP $s_From_FP $str_where UNION $s_select_DD $s_From_DD $str_where");
$doc_n = mysqli_num_rows($doc_rows);

if($doc_n>0){
    $str_Document .= '
    <div class="col-sm-12 collapse-div" id="accordion" >
        <div class="col-sm-12 BoxRowLabel" style="text-align:center">
		  LISTA DOCUMENTI
        </div>
        <div class="clean_row HSpace4"></div>';
    
    while ($doc_row = mysqli_fetch_array($doc_rows)){
        $a_documents[] = $doc_row;
        if($doc_row['DocumentationTypeId'] != 36){
            $path = getDocumentationPath($r_Fine['CountryId'],$doc_row['DocumentationTypeId'],$Id,$_SESSION['cityid'],$doc_row['Documentation']);
        }
        else{   //Per le rateizzazioni bisogna aggiungere al percorso il PaymentRateId
            $path = getDocumentationPath($r_Fine['CountryId'],36,$Id,$_SESSION['cityid'],''); //Il nome file viene agganciato alla fine
            
            $documentId = $doc_row['Id'];
            $rs_PaymentRate = $rs->Select('PaymentRate','FineId = '.$Id.' AND SignedRequestDocumentId = '.$documentId,'Id ASC');   //Aggiungo i numeri rateizzazione al vettore
            $r_PaymentRate = $rs->getArrayLine($rs_PaymentRate);
            $installmentId = $r_PaymentRate['Id'];
            //In questo modo posso impostare il numero della rateizzazione nonostante il documento non disponga del dato
            $path .= $installmentId.'/'.$doc_row['Documentation'];
        }
        //TODO controllare se funziona correttamente
        //Recupera il tipo mime del file, se non viene trovato il file valorizza con stringa vuota
        $MimeType = @mime_content_type($path) ? mime_content_type($path) : '';
        
        $str_Document .= '  <div id="FileName_'.$doc_row['Id'].'" collapse-header">
                        <div class="col-sm-1 BoxRowLabel">
                            Tipo
                        </div>
                        <div id="docType" class="col-sm-2 BoxRowCaption" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'.$doc_row['Title'].'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nome documento
                        </div>
                        <div class="col-sm-5 BoxRowCaption" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;width:45%">'.$doc_row['Documentation'].'
                        </div>
                        <div class="col-sm-1 BoxRowHTitle" style="padding:0;text-align:center;background-color: #294A9C;color:white;padding-top:4px;width:5%">
                            <i class="fas fa-angle-down caret-toggle" id="heading'.$doc_row['Id'].'" data-toggle="collapse" data-target="#collapse'.$doc_row['Id'].'" aria-expanded="false" aria-controls="collapse'.$doc_row['Id'].'"></i>
                        </div>
                        <div class="col-sm-1 BoxRowLabel" style="padding-top:3px;">';
        if ($MimeType === 'application/msword'){
            $str_Document .= '<span data-toggle="tooltip" data-placement="top" title="Scarica" data-mimetype="'.$MimeType.'" path="'.$path.'" docid="'.$doc_row['Id'].'" doctype="'.$doc_row['DocumentationTypeId'].'" class="tooltip-r glyphicon glyphicon-arrow-down col-sm-4" style="text-align:center;"></span>';
        } else {
            $str_Document .= '<span data-toggle="tooltip" data-placement="top" title="Visualizza" data-mimetype="'.$MimeType.'" path="'.$path.'" docid="'.$doc_row['Id'].'" doctype="'.$doc_row['DocumentationTypeId'].'" class="tooltip-r glyphicon glyphicon-eye-open col-sm-4" style="text-align:center;"></span>';
        }
        if ($MimeType === 'image/jpeg' || $MimeType === 'image/png'){
            $str_Document .= '<span data-toggle="tooltip" data-placement="top" title="Stampa" class="tooltip-r glyphicon glyphicon-print col-sm-3" style="text-align:center;"></span>';
        } else {
            $str_Document .= '<span class="disabled glyphicon glyphicon-print col-sm-3" style="text-align:center;color: #909090;"></span>';
        }
        if ($doc_row['DocumentationTypeId'] == 1 && ($MimeType === 'image/jpeg' || $MimeType === 'image/png')) {
            $str_Document .= '<span data-toggle="tooltip" data-placement="top" title="Stampa integrata fotogramma" class="tooltip-r glyphicon glyphicon-film col-sm-3" style="text-align:center;"></span>'
                //.'	<input type="hidden" id="Testo" value="'.$testo.'">'
                .'	<input type="hidden" id="FineId" value="'.$doc_row['FineId'].'">';
        } else {
            $str_Document .= '<span class="disabled glyphicon glyphicon-film col-sm-3" style="text-align:center;color: #909090;"></span>';
        }
        
        $str_Document .= '     </div>
                   </div>
                   <div class="clean_row HSpace4"></div>
  	               <div class="col-sm-12 collapse" id="collapse'.$doc_row['Id'].'" aria-labelledby="heading'.$doc_row['Id'].'" data-parent="#accordion">
                       <div class="BoxRowLabel" style="text-align:center">
            				NOTE
    				   </div>
                       <div id="note" class="BoxRowCaption" style="min-height:10rem;">
                            '.$doc_row['Note'].'
                       </div>
  			           <div class="clean_row HSpace4"></div>
                   </div>';
    }
    $str_Document .= '</div>';
    
} else $str_CSSDocument = ' style="color:#C43A3A; cursor:not-allowed;" ';


$str_Document_Data = '
<div class="tab-pane" id="Document">
    <div class="col-sm-12">
        '.$str_Document.'
    </div>
</div>
';








?>


<script>

function printTest(){
	$("#preview_iframe_img").contents().find("img").css({width: "210mm"});
	document.getElementById("preview_iframe_img").contentWindow.print();
}

function printTestArricchito(){
	$("#preview_iframe_img").contents().find("img").css({width: "210mm"});
	document.getElementById("preview_iframe_img").contentWindow.print();
}

$(document).ready(function () {
	var DocId;

    $('.caret-toggle').on("click", function(){
    	$(this).toggleClass('fa-angle-up fa-angle-down');
    });

	$('.glyphicon-eye-open, .glyphicon-arrow-down, .glyphicon-print:not(.disabled), .glyphicon-film:not(.disabled)').on("click", function(){
        var PrevDocId = DocId;
        //Controlla se è stato cliccato l'occhio o altre icone
        if ($(this).hasClass("glyphicon-eye-open")){
        	DocId = $(this).attr('docid');
        	var file = $(this).attr('path');
        } else {
        	DocId = $(this).siblings(".glyphicon-eye-open").attr('docid');
        	var file = $(this).siblings(".glyphicon-eye-open").attr('path');
        }
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

    $('.glyphicon-print:not(.disabled)').on("click", function(){
    	var file = $(this).siblings(".glyphicon-eye-open").attr('path');
    	console.log(file);
    	$("#preview_iframe_img").attr("src",file);
        setTimeout(function(){
        	printTest();
        }, 500);
    });

    $(".fa-angle-up, .fa-angle-down, .glyphicon-eye-open, .glyphicon-arrow-down, .glyphicon-print:not(.disabled),  .glyphicon-film:not(.disabled), .glyphicon-remove:not(.disabled)").hover(function(){
        $(this).css("cursor","pointer");
    },function(){
        $(this).css("cursor","");
    });
    
    $('.glyphicon-film:not(.disabled)').on('click', function (){
    	if(confirm('Si per stampare il fotogramma con informazioni integrate, continuare?')){
    		var file = $(this).siblings(".glyphicon-print:not(.disabled)").siblings(".glyphicon-eye-open").attr('path');
        	console.log(file);
        	var fineId = $('#FineId').val();
        	var url = 'https://'+ window.location.hostname + '/traffic_law/fotogramma/'+ fineId +'/'+file;
        	console.log(url);
        	$("#preview_iframe_img").contents().find("img").attr("src",url);
        	setTimeout(function(){
            	printTestArricchito();
            }, 500);
    	}
    	
    });
})
</script>

<?php 