<?php

//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                     NOTIFICATION
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

$str_CSSNotification = 'data-toggle="tab"';
$str_Notification = "";

$rs_Notification = $rs->Select('V_FineNotification', "FineId=" . $Id);
if(mysqli_num_rows($rs_Notification)>0){
    $r_FineNotification = mysqli_fetch_array($rs_Notification);




    $str_Folder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE_HTML : FOREIGN_FINE_HTML;
    $str_DocumentationFolder = ($r_FineNotification['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/".$r_FineNotification['FineId'] : FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$r_FineNotification['FineId'];
    $rs_Documentation = $rs->Select('FineDocumentation', "FineId=" . $Id." AND DocumentationTypeId IN(10,11,12,82)");
    $str_Front= "";
    $str_Back = "";
    $str_Single = "";
    $str_CanCad = "";
    while($r_Documentation = mysqli_fetch_array($rs_Documentation)) {
        $NotificationMimeType = @mime_content_type($str_DocumentationFolder."/".$r_Documentation['Documentation']) ?: '';
        
        if ($r_Documentation['DocumentationTypeId'] == 10) {
            if($NotificationMimeType == "image/jpeg" || $NotificationMimeType == "image/png"){
                $str_Front ='<img id="img_front" class="iZoom"  src="'.$str_DocumentationFolder."/".$r_Documentation['Documentation'].'"/>';
            } else $str_Front ='<iframe style="width:100%; height:100%;background:white;" src="'.$str_DocumentationFolder."/".$r_Documentation['Documentation'].'"></iframe>';
        }

        if ($r_Documentation['DocumentationTypeId'] == 11) {
            if($NotificationMimeType == "image/jpeg" || $NotificationMimeType == "image/png"){
                $str_Back ='<img id="img_back" class="iZoom"  src="'.$str_DocumentationFolder."/".$r_Documentation['Documentation'].'"/>';
            } else $str_Back ='<iframe style="width:100%; height:100%;background:white;" src="'.$str_DocumentationFolder."/".$r_Documentation['Documentation'].'"></iframe>';
        }
        
        if ($r_Documentation['DocumentationTypeId'] == 12) {
            if($NotificationMimeType == "image/jpeg" || $NotificationMimeType == "image/png"){
                $str_CanCad ='<img id="img_back" class="iZoom"  src="'.$str_DocumentationFolder."/".$r_Documentation['Documentation'].'"/>';
            } else $str_CanCad ='<iframe style="width:100%; height:100%;background:white;" src="'.$str_DocumentationFolder."/".$r_Documentation['Documentation'].'"></iframe>';
        }
        
        if ($r_Documentation['DocumentationTypeId'] == 82) {
            if($NotificationMimeType == "image/jpeg" || $NotificationMimeType == "image/png"){
                $str_Single ='<img id="img_back" class="iZoom"  src="'.$str_DocumentationFolder."/".$r_Documentation['Documentation'].'"/>';
            } else $str_Single ='<iframe style="width:100%; height:100%;background:white;" src="'.$str_DocumentationFolder."/".$r_Documentation['Documentation'].'"></iframe>';
        }
    }

    $rs_FineHistory = $rs->Select("FineHistory", "FineId=".$Id." AND NotificationTypeId=6");
    $r_FineHistory = mysqli_fetch_array($rs_FineHistory);

    $doubleImages = '
                    <div class="col-sm-6 table_caption_I">Retro</div>
                    <div class="col-sm-6 table_caption_I">Fronte</div>

                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-6 BoxRowLabel" style="height:41rem;padding:0;">
                        <div class="imgWrapper" style="height:100%;overflow:auto;">
                            '.$str_Back.'
                        </div>
                    </div>          
                    <div class="col-sm-6 BoxRowLabel" style="height:41rem;padding:0;">
                        <div class="imgWrapper" style="height:100%;overflow:auto;">
                            '.$str_Front.'
                        </div>
                    </div>';
    
    $singleImage = '
                    <div class="col-sm-12 table_caption_I">Documento singolo</div>

                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-12 BoxRowLabel" style="height:41rem;padding:0;">
                        <div class="imgWrapper" style="height:100%;overflow:auto;">
                            '.$str_Single.'
                        </div>
                    </div>';

    $cancadImage = '
                    <div class="col-sm-12 table_caption_I">CAN/CAD</div>
        
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-12 BoxRowLabel" style="height:41rem;padding:0;">
                        <div class="imgWrapper" style="height:100%;overflow:auto;">
                            '.$str_CanCad.'
                        </div>
                    </div>';
    
    $str_Images = (($str_Back != "" || $str_Front != "") ? $doubleImages : "").(($str_Single != "") ? $singleImage : "").(($str_CanCad != "") ? $cancadImage : "");
    
    $str_Notification='
                <div class="col-sm-12 BoxRowTitle" >
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    NOTIFICA
                </div>
            </div>
            <div class="clean_row HSpace4"></div>  
            <div class="col-sm-12">
                <div class="col-sm-1 BoxRowLabel">
                    Protocollo
                </div>
                <div class="col-sm-1 BoxRowCaption">
                '.$r_FineNotification['ProtocolId'].'/'.$r_FineNotification['ProtocolYear'].'
                </div>
                <div class="col-sm-1 BoxRowLabel">
                      Notifica
                </div>
                <div class="col-sm-1 BoxRowCaption">
                      '.$r_FineHistory['NotificationFee'].'
                </div>                               
                <div class="col-sm-1 BoxRowCaption">
                      CAN
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    '.$r_FineHistory['CanFee'].'
                </div>                
                <div class="col-sm-1 BoxRowCaption">
                      CAD
                </div>
                <div class="col-sm-1 BoxRowCaption">
                   '.$r_FineHistory['CadFee'].'
                </div>                  
                <div class="col-sm-1 BoxRowCaption">
                      Notificatore
                </div>
                <div class="col-sm-1 BoxRowCaption">
                   '.$r_FineHistory['NotifierFee'].'
                </div>                 
                <div class="col-sm-1 BoxRowCaption">
                      Altro
                </div>
                <div class="col-sm-1 BoxRowCaption">
                   '.$r_FineHistory['OtherFee'].'
                </div>    
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Data Spedizione
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_FineNotification['SendDate']).'
                </div>            
                <div class="col-sm-2 BoxRowLabel">
                    Data Notifica
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_FineNotification['NotificationDate']).'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Data LOG
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_FineNotification['LogDate']).'
                </div>                
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Raccomandata
                </div>
                <div class="col-sm-2 BoxRowCaption">
                '.$r_FineNotification['LetterNumber'].'
                </div>            
                <div class="col-sm-2 BoxRowLabel">
                    Ricevuta
                </div>
                <div class="col-sm-2 BoxRowCaption">
                '.$r_FineNotification['ReceiptNumber'].'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Esito
                </div>
                <div class="col-sm-2 BoxRowCaption">
                 '.$r_FineNotification['Title'].'
                </div>                
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Scatola
                </div>
                <div class="col-sm-2 BoxRowCaption">
                '.$r_FineNotification['Box'].'
                </div>            
                <div class="col-sm-2 BoxRowLabel">
                    Lotto
                </div>
                <div class="col-sm-2 BoxRowCaption">
                '.$r_FineNotification['Lot'].'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Posizione
                </div>
                <div class="col-sm-2 BoxRowCaption">
                 '.$r_FineNotification['Position'].'
                </div>                
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12" >
            '.
            $str_Images
            .'
            </div>
    ';
} else $str_CSSNotification = ' style="color:#C43A3A; cursor:not-allowed;" ';


$str_Notification_Data = '
<div class="tab-pane" id="Notification">            
    <div class="col-sm-12">
        '.$str_Notification.'
    </div>
</div>
';