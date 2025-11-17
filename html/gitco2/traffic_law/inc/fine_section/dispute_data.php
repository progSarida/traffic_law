<?php
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         DISPUTE
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////


$str_CSSDispute = 'data-toggle="tab"';
$str_Dispute = '';

$a_GradeType = array("","I","II","III");

$a_FineSuspension= array("NO","SI");


$str_DocumentationDispute ='
            <div class="col-sm-6">
                <div class="col-sm-12 BoxRow" style="width:100%;height:9rem;">
                    <div class="example">
                        <div id="fileTreeDispute" class="BoxRowLabel" style="height:8rem;overflow:auto"></div>
                    </div>
                </div>
                <div class="col-sm-12 BoxRow" style="width:100%;height:40rem;">
                    <div class="imgWrapper" style="height:38rem;overflow:auto">
                        <img id="preview_dispute" class="iZoom"  />
                    </div>
                </div>
            </div>';

$rs_Dispute = $rs->Select('V_FineDispute', "FineId=" . $Id, "GradeTypeId");

if(mysqli_num_rows($rs_Dispute)>0){
    
    $str_Dispute .= '
        <div class="col-sm-12 BoxRowTitle" >
            <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                RICORSO
            </div>
        </div>
        <div class="col-sm-6">';
    
    while($r_Dispute = mysqli_fetch_array($rs_Dispute)){

        $DisputeId = $r_Dispute['DisputeId'];
        $GradeTypeId = $r_Dispute['GradeTypeId'];

        if($GradeTypeId==1){



            $str_Folder = ($r_Dispute['CountryId']=='Z000') ? 'doc/national/dispute' : 'doc/foreign/dispute';

            $str_tree_dispute = "";
            $str_Img_dispute = "";
            $doc_rows = $rs->Select('DisputeDocumentation',"FineId=" . $r_Dispute['FineId'], "Id");
            $doc_n = mysqli_num_rows($doc_rows);

            if($doc_n>0){

                $doc_row = mysqli_fetch_array($doc_rows);

                $File = $str_Folder.'/'.$_SESSION['cityid'].'/'.$r_Dispute['FineId'].'/'.$doc_row['Documentation'];
                if (strtolower(substr($doc_row['Documentation'],-3))=="jpg") {
                    $str_Img_dispute =' $("#preview_dispute").attr("src","'.$File.'");';
                }else{
                    $str_Img_dispute ='$("#preview_dispute").html("<iframe style=\"width:100%; height:100%\" src=\"'.$File.'\"></iframe>");';
                }

                $str_tree_dispute ='
                    $("#fileTreeDispute").fileTree({ root:\''.$str_Folder.'/'.$_SESSION['cityid'].'/'.$r_Dispute['FineId'].'/\', script: \'jqueryFileTree.php\' }, function(file) {
                    var FileType = file.substr(file.length - 3);
        
                    if(FileType.toLowerCase()==\'pdf\' || FileType.toLowerCase()==\'doc\'){
                        $("#preview").html("<iframe style=\"width:100%; height:100%\" src=\'"+file+"\'></iframe>");
                    }else{
                        $("#preview").attr("src",file);
                    } 
                });
                ';



            }
        }




        $str_Dispute .= '
                <div class="clean_row HSpace4"></div>                  
                <div class="col-sm-12 table_caption_I" style="text-align:center">
                    '.$a_GradeType[$r_Dispute['GradeTypeId']].' Grado 
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-3 BoxRowLabel">
                    Ufficio giudicante
                </div>          
                <div class="col-sm-9 BoxRowCaption">
                    '.$r_Dispute['OfficeTitle'].' '.$r_Dispute['OfficeCity'].'
                </div>  
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-3 BoxRowLabel">
                    Altri dati ufficio
                </div>   
                <div class="col-sm-9 BoxRowCaption">
                    '.$r_Dispute['OfficeAdditionalData'].'
                </div>               
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel">
                    Spedizione
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_Dispute['DateSend']).'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Ricezione
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_Dispute['DateReceive']).'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Deposito
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_Dispute['DateFile']).'
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-3 BoxRowLabel">
                    RG
                </div>          
                <div class="col-sm-3 BoxRowCaption">
                    '.$r_Dispute['Number'].'
                </div>                
                <div class="col-sm-3 BoxRowLabel">
                    SEZ
                </div>          
                <div class="col-sm-3 BoxRowCaption">
                    '.$r_Dispute['Division'].'
                </div>                
                <div class="clean_row HSpace4"></div>    
        
                <div class="col-sm-4 BoxRowLabel">
                    Data provvedimento
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                     '.DateOutDB($r_Dispute['DisputeDateMeasure']).'
                </div>            
                <div class="col-sm-4 BoxRowLabel">
                    Numero provvedimento
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    '.$r_Dispute['MeasureNumber'].'
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-4 BoxRowLabel">
                    Sospensione verbale
                </div>          
                <div class="col-sm-8 BoxRowCaption">
                    '.$a_FineSuspension[$r_Dispute['FineSuspension']].'
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    Ente
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-3 BoxRowLabel">
                    Data Protocollo ente
                </div>          
                <div class="col-sm-3 BoxRowCaption">
                    '.DateOutDB($r_Dispute['DateProtocolEntity']).'
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Numero protocollo ente
                </div>          
                <div class="col-sm-3 BoxRowCaption">
                    '.$r_Dispute['EntityProtocolNumber'].'
                </div>                             
                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    Presa in carico
                </div>
                <div class="clean_row HSpace4"></div>

                <div class="col-sm-2 BoxRowLabel">
                    Registrazione
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_Dispute['RegDate']).'
                </div>  
                <div class="col-sm-2 BoxRowLabel">
                    Numero protocollo
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    '.$r_Dispute['ProtocolNumber'].'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Data Protocollo
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_Dispute['DateProtocol']).'
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    Udienze
                </div>
                <div class="clean_row HSpace4"></div>
        ';

        $rs_DisputeDate = $rs->Select('V_DisputeDate', "DisputeId=" . $DisputeId . " AND GradeTypeId=".$GradeTypeId, "DateHearing");


        while($r_DisputeDate = mysqli_fetch_array($rs_DisputeDate)){

            if($r_DisputeDate['DisputeResultId']<=1) {
                $str_Dispute .= '
        

            <div class="col-sm-2 BoxRowLabel">
                Data
            </div>          
            <div class="col-sm-2 BoxRowCaption">
                '.DateOutDB($r_DisputeDate['DateHearing']).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ora
            </div>          
            <div class="col-sm-2 BoxRowCaption">
                '.$r_DisputeDate['TimeHearing'].'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Tipo
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.$r_DisputeDate['TypeHearing'].'
            </div>
            <div class="clean_row HSpace4"></div>
            ';
                if($r_DisputeDate['DisputeResultId']>0){

                    $str_Dispute .= '
                    <div class="col-sm-2 BoxRowLabel">
                        Atto
                    </div>  
                    <div class="col-sm-3 BoxRowLabel">
                    '. $r_DisputeDate['DisputeActTitle'] .'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Esito
                    </div>  
                    <div class="col-sm-5 BoxRowLabel">
                    '. $r_DisputeDate['DisputeResultTitle'] .'
                    </div>
                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-2 BoxRowLabel">
                        Note aggiuntive
                    </div>  
                    <div class="col-sm-10 BoxRowLabel">
                        '. $r_DisputeDate['Note'] .'
                    </div>  
                    <div class="clean_row HSpace4"></div>
                    ';
                }
            }

            if($r_DisputeDate['DisputeResultId']>1){
                $str_Dispute .= '

                <div class="col-sm-5 BoxRowLabel">
                    '. $r_DisputeDate['DisputeActTitle'] .' n. '. $r_DisputeDate['DisputeDateNumber'] .'
                    del '.DateOutDB($r_DisputeDate['DateMeasure']).'
                </div>  

                <div class="col-sm-2 BoxRowLabel">
                    Data deposito
                </div>  
                <div class="col-sm-2 BoxRowCaption">
                    '.DateOutDB($r_DisputeDate['DateAction']).'
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Esito
                </div>  
                <div class="col-sm-2 BoxRowCaption">
                    '. $r_DisputeDate['DisputeResultTitle'] .'
                </div>
                <div class="clean_row HSpace4"></div>

                <div class="col-sm-2 BoxRowLabel">
                    Data notifica
                </div>  
                <div class="col-sm-2 BoxRowCaption">
                     '.DateOutDB($r_DisputeDate['DateNotification']).'
                </div>               
                <div class="col-sm-2 BoxRowLabel">
                    Importo da pagare
                </div>  
                <div class="col-sm-2 BoxRowCaption">
                     '.$r_DisputeDate['Amount'].'
                </div> 
                <div class="col-sm-4 BoxRowCaption"></div> 
                <div class="clean_row HSpace4"></div>        
             ';
            }

        }
    }
    $str_Dispute .=  '</div>'. $str_DocumentationDispute;
}else $str_CSSDispute = ' style="color:#C43A3A; cursor:not-allowed;" ';



$str_Dispute_data = '
<div class="tab-pane" id="Dispute">            
    <div class="col-sm-12">
        '.$str_Dispute.'
    </div>
</div>
';