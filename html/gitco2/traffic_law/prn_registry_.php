<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$a_RadioChk = array("","","");


$a_ChkTypePayment = array("","","","","");
$a_ChkTypeViolation = array("","","");
$a_ChkTypeRule = array("","","");
$a_ChkTypeNotification = array("","","","");

$str_Radio = "";
$str_CheckGenre1 = "";
$str_CheckGenre2 = "";

$Search_Genre = CheckValue('Search_Genre','n');

$Search_FromNotificationDate    = CheckValue('Search_FromNotificationDate','s');
$Search_ToNotificationDate      = CheckValue('Search_ToNotificationDate','s');

$Search_Address                 = CheckValue('Search_Address','s');
$Search_Trespasser              = CheckValue('Search_Trespasser','s');
$Search_Locality                = CheckValue('Search_Locality','s');
$Search_ArticleId               = CheckValue('Search_ArticleId','n');

$btn_search                     = CheckValue('btn_search','n');

$d_PrintDate                    = (CheckValue('PrintDate','s')=="") ? date("d/m/Y") : CheckValue('PrintDate','s');

$n_TypeViolation                = CheckValue('Search_TypeViolation','n');
$n_TypePayment                  = CheckValue('Search_TypePayment','n');
$n_TypeRule                     = CheckValue('Search_TypeRule','n');
$n_TypeNotification             = CheckValue('Search_TypeNotification','n');

$FineArchive                    = CheckValue('FineArchive','n');
$FineDispute                    = CheckValue('FineDispute', 'n');

$str_ChkFineArchive0 = $str_ChkFineArchive1 = $str_ChkFineArchive2 = "";
$str_ChkFineDispute0 = $str_ChkFineDispute1 = $str_ChkFineDispute2 = "";

$a_ChkTypePayment[$n_TypePayment] = "SELECTED";
$a_ChkTypeViolation[$n_TypeViolation] = "SELECTED";
$a_ChkTypeRule[$n_TypeRule] = "SELECTED";
$a_ChkTypeNotification[$n_TypeNotification] = "SELECTED";







$str_TypePayment ='
    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
        Pagamento:
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <select name="Search_TypePayment">
            <option value="0"></option>
            <option value="1" '.$a_ChkTypePayment[1].'>Parziale</option>
            <option value="2" '.$a_ChkTypePayment[2].'>Totale</option>
            <option value="3" '.$a_ChkTypePayment[3].'>Non pagato</option>
            <option value="4" '.$a_ChkTypePayment[4].'>A credito</option>
        </select>
    </div>
';

$str_TypePayment ='
    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
        Pagamento:
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <select name="Search_TypePayment">
            <option value="0"></option>
            <option value="2" '.$a_ChkTypePayment[2].'>Pagato</option>
            <option value="3" '.$a_ChkTypePayment[3].'>Non pagato</option>
        </select>
    </div>
';




$str_TypeViolation ='
    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
        Tipo:
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <select name="Search_TypeViolation">
            <option value="0"></option>
            <option value="1" '.$a_ChkTypeViolation[1].'>Preavvisi</option>
            <option value="2" '.$a_ChkTypeViolation[2].'>Verbali</option>
        </select>
    </div>
';


$str_TypeRule ='
    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
        Ruolo:
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <select name="Search_TypeViolation">
            <option value="0"></option>
            <option value="1" '.$a_ChkTypeRule[1].'>Iscritti</option>
            <option value="2" '.$a_ChkTypeRule[2].'>Non iscritti</option>
        </select>
    </div>
';


$str_TypeNotification ='
    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
        Notifica:
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <select name="Search_TypeNotification">
            <option value="0"></option>
            <option value="1" '.$a_ChkTypeNotification[1].'>Notificati</option>
            <option value="2" '.$a_ChkTypeNotification[2].'>Ancora da notificare</option>
            <option value="3" '.$a_ChkTypeNotification[3].'>Non notificati</option>
        </select>
    </div>
';



















///////////////////////////////////////
// CREATION QUERY
///////////////////////////////////////
$str_Where = " F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=".$_SESSION['year'];


if($Search_Address != "")    $str_Where .= " AND F.Address='".$Search_Address."'";
if($Search_FromProtocolId>0)    $str_Where .= " AND F.ProtocolId>=".$Search_FromProtocolId;
if($Search_ToProtocolId>0)      $str_Where .= " AND F.ProtocolId<=".$Search_ToProtocolId;
if($Search_Trespasser!="")      $str_Where .= " AND (T.CompanyName LIKE '%".addslashes($Search_Trespasser)."%' OR T.Surname LIKE '%".addslashes($Search_Trespasser)."%')";
if($Search_Locality!="")        $str_Where .= " AND F.Locality='".$Search_Locality."' ";



$str_QueryFineHistory = "";
$str_QueryArticle = "";
if($n_TypeNotification>0 || $Search_FromNotificationDate != "" || $Search_ToNotificationDate != ""){

    $str_QueryFineHistory = " JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6 ";

    if($Search_FromNotificationDate != "")  $str_Where .= " AND FH.DeliveryDate>='".DateInDB($Search_FromNotificationDate)."'";
    if($Search_ToNotificationDate != "")    $str_Where .= " AND FH.DeliveryDate<='".DateInDB($Search_ToNotificationDate)."'";


    if($n_TypeNotification==1){
        $str_Where .= " AND ResultId>=1 AND ResultId<=5 ";
    } else if($n_TypeNotification==3){
        $str_Where .= " AND ResultId>5 ";
    } else if($n_TypeNotification==2){
        $str_Where .= " AND ResultId IS NULL ";
    }

}
if($Search_ArticleId>0){
    $str_Where .= " AND ArticleId=".$Search_ArticleId;

}


if($Search_FromFineDate != "")  $str_Where .= " AND F.FineDate>='".DateInDB($Search_FromFineDate)."'";
if($Search_ToFineDate != "")    $str_Where .= " AND F.FineDate<='".DateInDB($Search_ToFineDate)."'";


if($n_TypePayment>0){
    if($n_TypePayment==2){
        $str_Where .= " AND F.Id IN(SELECT FineId FROM FinePayment WHERE CityId='".$_SESSION['cityid']."') ";
    }else if($n_TypePayment==3){
        $str_Where .= " AND F.Id NOT IN(SELECT FineId FROM FinePayment WHERE CityId='".$_SESSION['cityid']."') ";
    }

}


if ($FineDispute == 0) {
    $str_Where .= " AND F.Id NOT IN (SELECT FineId FROM FineDispute WHERE DisputeStatusId=1)";
    $str_ChkFineDispute0 = " CHECKED ";

} else if ($FineDispute == 2) {
    $str_Where .= " AND F.Id IN (SELECT FineId FROM FineDispute WHERE DisputeStatusId=1)";
    $str_ChkFineDispute2 = " CHECKED ";

} else {
    $str_ChkFineDispute1 = " CHECKED ";
}



if($FineArchive==0){
    $str_Where .= " AND F.StatusTypeId>=12 AND F.StatusTypeId<=30 ";
    $str_ChkFineArchive0 =" CHECKED ";

}else if($FineArchive==2){
    $str_Where .= " AND F.StatusTypeId>=35 AND F.StatusTypeId<=37 ";
    $str_ChkFineArchive2 =" CHECKED ";

}else{
    $str_Where .= " AND F.StatusTypeId>=12 AND F.StatusTypeId<=37 ";
    $str_ChkFineArchive1 =" CHECKED ";
}


if($Search_Genre==1){
    $str_Where .= " AND Genre != 'D'";

    $str_CheckGenre1 = " SELECTED ";
}else if($Search_Genre==2){
    $str_Where .= " AND Genre = 'D'";

    $str_CheckGenre2 = " SELECTED ";
}





$strOrder = "F.ProtocolYear, F.ProtocolId";





$rs_Row = $rs->Select(MAIN_DB.'.City',"UnionId='".$_SESSION['cityid']."'", "Title");
$n_Code = mysqli_num_rows($rs_Row);

$str_Locality = '
    <div class="col-sm-2 BoxRowCaption">
    </div>
';
if($n_Code>0) {
    $str_Locality = '
                <div class="col-sm-1 BoxRowLabel">
                    Comune
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <select class="form-control" name="Search_Locality">
                    <option></option>
';

    while ($r_Row = mysqli_fetch_array($rs_Row)) {

        $str_Locality .= '<option value="' . $r_Row['Id'].'"';

        if($Search_Locality==$r_Row['Id']) $str_Locality .=' SELECTED ';


        $str_Locality .= '>' . $r_Row['Title'] . '</option>';

    }

    $str_Locality .= '        </select>
                    </div>';

}




if($_SESSION['userlevel']>0){
    $str_UserLevelFilter = '                            
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            Data stampa:
        </div>
        <div class="col-sm-1 BoxRowCaption">
              <input class="form-control frm_field_date" name="PrintDate" type="text" style="width:9rem" value="'.$d_PrintDate.'">
        </div>
       ';

} else {
    $str_UserLevelFilter = '
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            Data stampa:
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_date" name="PrintDate" type="text" style="width:9rem" value="'.$d_PrintDate.'" readonly>
        </div>                        
';
}






$str_out .='

<form id="f_Search" action="'.$str_CurrentPage.'" method="post">
<input type="hidden" name="btn_search" value="1">

<div class="row-fluid">        
    <div class="col-sm-12" >
        <div class="col-sm-11" style="height:11.6rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Accertamento:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_FromFineDate" type="text" style="width:9rem" value="'.$Search_FromFineDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_ToFineDate" type="text" style="width:9rem" value="'.$Search_ToFineDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Genere:
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select name="Search_Genre">
                    <option value="0">Tutti</option>
                    <option value="1" '.$str_CheckGenre1.'>Persona fisica</option>
                    <option value="2" '.$str_CheckGenre2.'>Persona giuridica</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" style="width:20rem" value="'.$Search_Trespasser.'">
            </div>  
            '.$str_UserLevelFilter.'
            <div class="clean_row HSpace4"></div>      
            <div class="col-sm-1 BoxRowLabel">
                Notifica:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_FromNotificationDate" type="text" style="width:9rem" value="'.$Search_FromNotificationDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_ToNotificationDate" type="text" style="width:9rem" value="'.$Search_ToNotificationDate.'">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Luogo infrazione:
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.CreateSelectQuery("SELECT DISTINCT Address FROM Fine WHERE CityId='".$_SESSION['cityid']."' AND ProtocolYear=". $_SESSION['year'] ." ORDER BY ADDRESS ","Search_Address","Address","Address",$Search_Address,false) .'
            </div>                    
            '.$str_Locality.'                        
                                       
                                       
                                       
            <div class="clean_row HSpace4"></div>                 
 
            <div class="col-sm-1 BoxRowLabel">
                Cronologico:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>   
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_FromProtocolId. '" id="Search_FromProtocolId" name="Search_FromProtocolId" style="width:8rem">
			</div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>   
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_ToProtocolId. '" id="Search_ToProtocolId" name="Search_ToProtocolId" style="width:8rem">
		    </div> 
            <div class="col-sm-1 BoxRowLabel">
                Articolo:
            </div>
            <div class="col-sm-6 BoxRowCaption">
                '.CreateSelectQuery("SELECT DISTINCT A.Id ArticleId, A.Article,A.Letter,A.Paragraph, CONCAT(A.Article,' ',A.Letter,' ',A.Paragraph) ArticleTitle FROM Fine F JOIN FineArticle FA ON F.Id=FA.FineId JOIN Article A ON FA.ArticleId=A.Id WHERE F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=". $_SESSION['year'] ." ORDER BY A.Article,A.Letter,A.Paragraph ","Search_ArticleId","ArticleId","ArticleTitle",$Search_ArticleId,false) .'
            </div> 
              
             		               
            <div class="clean_row HSpace4"></div> 
            
            <div class="col-sm-1 BoxRowLabel">
                Archiviati:
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input type="radio" name="FineArchive" value="0" ' . $str_ChkFineArchive0 . '><span  style="position:relative;top:-1rem">Escludi</span> 
                <input type="radio" name="FineArchive" value="1" ' . $str_ChkFineArchive1 . '><span  style="position:relative;top:-1rem">Includi</span>
                <input type="radio" name="FineArchive" value="2" ' . $str_ChkFineArchive2 . '><span  style="position:relative;top:-1rem">Solo loro</span>
            </div>             		               
            <div class="col-sm-1 BoxRowLabel">
                Ricorsi:
            </div>
            <div class="col-sm-7 BoxRowCaption">
                <input type="radio" name="FineDispute" value="0" ' . $str_ChkFineDispute0 . '><span  style="position:relative;top:-1rem">Escludi</span> 
                <input type="radio" name="FineDispute" value="1" ' . $str_ChkFineDispute1 . '><span  style="position:relative;top:-1rem">Includi</span>
                <input type="radio" name="FineDispute" value="2" ' . $str_ChkFineDispute2 . '><span  style="position:relative;top:-1rem">Solo loro</span>
            </div>               		               
             		               
		    <div class="clean_row HSpace4"></div>  
		            
            '.$str_TypePayment.'
            '.$str_TypeViolation.'
            '.$str_TypeRule.'
            '.$str_TypeNotification.'
            
            
            
            
           
                
        </div>
        <div class="col-sm-1 table_caption_H" style="height:9.2rem;">
            <img src="'.IMG.'/progress.gif" style="width:70px;display: none;position:absolute;left:20px;top:10px" id="Progress"/>
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">

       
                <button class="btn btn-primary" id="btn_src">
                    <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3.5rem;"></i>
                </button>
       
            </div>
            <button class="btn btn-primary" id="btn_pdf" style="position:absolute;bottom:0px;left:10px;">    
                <i class="fa fa-file-pdf-o" style="font-size:1.5rem"></i>            
            </button>
            <button class="btn btn-primary" id="btn_xls" style="position:absolute;bottom:0px;left:60px;">
                <i class="fa fa-file-excel-o" style="font-size:1.5rem"></i>
            </button>
        </div>   
    </div>
</div>
</form>
<div class="clean_row HSpace4"></div>
';

$str_out .='        
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">Protocollo</div>
				<div class="table_label_H col-sm-1">Rif.to</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-2">Targa</div>
				<div class="table_label_H col-sm-6">Localita</div>

				<div class="clean_row HSpace4"></div>';


if($btn_search==1){

    $str_Query ='
    
        SELECT
        F.Id FineId,
        F.Code,
        F.CityId,
        F.Address,
        F.ProtocolId,
        F.ProtocolYear,
        F.StatusTypeId,
        F.FineDate,
        F.FineTime,
        F.VehiclePlate,
        F.Locality,
        F.CountryId VehicleCountryId,
        F.ExternalProtocol,
        F.ExternalYear,
        F.PreviousId,
        
        FT.TrespasserTypeId,
        FT.Note,
        FT.OwnerAdditionalFee,
        FT.CustomerAdditionalFee,
        FT.ReceiveDate,
        
        FA.ArticleId, 
        
        T.Genre,
        T.ZIP,
        T.Id TrespasserId,
        T.CompanyName,
        T.Surname,
        T.Name,

        T.City,
        T.CountryId,
        
        C.Title CountryTitle
        
        FROM Fine F INNER JOIN FineTrespasser FT ON F.Id = FT.FineId
        

        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN FineArticle FA ON F.Id = FA.FineId
        ';

    $str_Query .= $str_QueryFineHistory;
    $str_Query .= $str_QueryArticle;

    $str_Query .='
        WHERE (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2) AND 
    ';

    $str_Query .= $str_Where;

    $str_Query .= " ORDER BY ".$strOrder;

    $table_rows = $rs->SelectQuery($str_Query);
	$RowNumber = mysqli_num_rows($table_rows);

	if ($RowNumber == 0) {
		$str_out.=
			'<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
	} else {



        $rs_Result = $rs->Select('Result', "1=1");
        while ($r_Result = mysqli_fetch_array($rs_Result)){
            $a_Result[$r_Result['Id']] = $r_Result['Title'];
        }
        $a_GradeType = array("","I","II","III");



        $n_CountFine = 0;
        $n_CountNotification = 0;
        $n_CountPayment = 0;

        $f_TotalPayment = 0.00;


		while ($table_row = mysqli_fetch_array($table_rows)) {
            $n_CountFine++;


            $rs_Row = $rs->Select('V_FineHistory',"Id=".$table_row['FineId']." AND NotificationTypeId=6");
            $r_Row = mysqli_fetch_array($rs_Row);

            $str_PreviousId = "";
            if($table_row['PreviousId']>0){
                $rs_Previous = $rs->Select('Fine',"Id=".$table_row['PreviousId']);
                $r_Previous = mysqli_fetch_array($rs_Previous);
                $str_PreviousId = 'Verbale collegato Cron '. $r_Previous['ProtocolId'].'/'.$r_Previous['ProtocolYear'];
            }

            $str_Archive = "";
            if($table_row['StatusTypeId']==35){
                $rs_Archive = $rs->SelectQuery("
                SELECT FA.ArchiveDate, FA.Note, R.TitleIta ReasonTitle
                FROM FineArchive FA JOIN Reason R ON FA.ReasonId = R.Id
                WHERE FA.FineId=".$table_row['FineId']);
                $r_Archive = mysqli_fetch_array($rs_Archive);

                $str_Archive = 'Archiviato: '. DateOutDB($r_Archive['ArchiveDate']);



            }else if($table_row['StatusTypeId']==36){
                $rs_Previous = $rs->Select('Fine',"PreviousId=".$table_row['FineId']);
                $r_Previous = mysqli_fetch_array($rs_Previous);

                $str_PreviousId = 'Noleggio ristampato con Cron '. $r_Previous['ProtocolId'].'/'.$r_Previous['ProtocolYear'];

            }


            $str_Article = "Art. ";
            $rs_article = $rs->Select('Article',"Id=".$table_row['ArticleId']);
            $r_article = mysqli_fetch_array($rs_article);

            $str_Article .= $r_article['Article'].' '.str_replace("0","",$r_article['Paragraph']).' '.$r_article['Letter'];



            $rs_Locality = $rs->Select(MAIN_DB.".City","Id='".$table_row['Locality']."'");
            $r_Locality = mysqli_fetch_array($rs_Locality);

            $str_Locality = $r_Locality['Title'] .' - '.$table_row['Address'];

            $str_Protocol = '';

            $ExternalProtocol = ($table_row['ExternalProtocol']>0)? 'N. '.$table_row['ExternalProtocol'].'/'.$table_row['ExternalYear'] : '';
            $str_Protocol = (!is_null($r_Row['ExternalDate'])) ? 'Protocollo: ' . DateOutDB($r_Row['ExternalDate']).' '.$ExternalProtocol : '';


            $str_Flow = (!is_null($r_Row['FlowDate'])) ? 'Flusso: '. DateOutDB($r_Row['FlowDate']) : '';

            $str_Send = (! is_null($r_Row['SendDate'])) ? 'Invio: '. DateOutDB($r_Row['SendDate']) : '';

            $str_Result = "";
            if (! is_null($r_Row['ResultId'])) {
                $str_Result = (! is_null($r_Row['DeliveryDate'])) ? 'Notificato: '. DateOutDB($r_Row['DeliveryDate']) : $a_Result[$r_Row['ResultId']];

                $n_CountNotification++;

            }


            $str_Payment = '';
            $rs_Row = $rs->Select('FinePayment',"FineId=".$table_row['FineId']);
            if(mysqli_num_rows($rs_Row)>0){
                $r_Row = mysqli_fetch_array($rs_Row);
                $str_Payment = 'Pagato: '. DateOutDB($r_Row['PaymentDate'])." - € ".NumberDisplay($r_Row['Amount']);

                $n_CountPayment++;;
                $f_TotalPayment += $r_Row['Amount'];
            }


            $str_Dispute = '';
            $rs_Row = $rs->Select('V_FineDispute',"FineId=".$table_row['FineId']." ORDER BY GradeTypeId DESC");
            if(mysqli_num_rows($rs_Row)>0){
                $r_Row = mysqli_fetch_array($rs_Row);
                $str_Dispute = $a_GradeType[$r_Row['GradeTypeId']].' Grado - '.$r_Row['OfficeTitle'].' '. $r_Row['OfficeCity'].' Depositato in data '. DateOutDB($r_Row['DateFile']);
            }

            $rs_Row = $rs->Select('FineCommunication',"FineId=".$table_row['FineId']);
            $r_Row = mysqli_fetch_array($rs_Row);
            $str_Communication = (! is_null($r_Row['CommunicationDate'])) ? 'Comunicazione dati: '.DateOutDB($r_Row['CommunicationDate']) : '';





            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $table_row['ProtocolId'].' / '.$table_row['ProtocolYear'] .'</div>';
			$str_out.= '<div class="table_caption_H font_small col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>';
			$str_out.= '<div class="table_caption_H font_small col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>';
			$str_out.= '<div class="table_caption_H font_small col-sm-2">' . $table_row['VehiclePlate'] .'</div>';
			$str_out.= '<div class="table_caption_H font_small col-sm-6">' . '('.$table_row['TrespasserId']. ') '.$table_row['CompanyName'] .' '.$table_row['Surname'] .' '.$table_row['Name'] .'</div>';

			$str_out.= '<div class="clean_row HSpace4"></div>';

            $str_out.= '<div class="table_caption_H font_small col-sm-3">' . 'Ref: '.$table_row['Code'] .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $str_Article .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $str_PreviousId .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-3">' . $str_Communication .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $str_Dispute .'</div>';

            $str_out.= '<div class="clean_row HSpace4"></div>';

            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $str_Flow .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $str_Send .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $str_Result .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-3">' . $str_Locality .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $str_Payment .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-1">' . $str_Archive .'</div>';

            $str_out.= '<div class="clean_row HSpace16"></div>';

		}
        $str_out.= '
            <div class="table_label_H col-sm-12 center">Riepilogo generale</div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="BoxRowLabel col-sm-2">Totale verbali</div>
            <div class="BoxRowCaption col-sm-10">' . $n_CountFine .'</div>
            <div class="clean_row HSpace4"></div>
            
            <div class="BoxRowLabel col-sm-2">Verbali notificati</div>
            <div class="BoxRowCaption col-sm-10">' . $n_CountNotification .'</div>
            <div class="clean_row HSpace4"></div>
            
            <div class="BoxRowLabel col-sm-2">Verbali pagati</div>
            <div class="BoxRowCaption col-sm-10">' . $n_CountPayment .'</div>
            <div class="clean_row HSpace4"></div>
            
            <div class="BoxRowLabel col-sm-2">Totale pagamenti</div>
            <div class="BoxRowCaption col-sm-10">' . NumberDisplay($f_TotalPayment) .'</div>
            
        ';

	}
}

	$str_out.= '<div>
</div>';


	echo $str_out;
?>
<script type="text/javascript">

    $(document).ready(function () {

        $("#btn_src").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'prn_registry.php');
            $('#btn_src').hide();
            $('#btn_xls').hide();
            $('#btn_pdf').hide();
            $('#Progress').show();

            $('#f_Search').submit();

        });

        $("#btn_pdf").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'prn_registry_exe.php');
            $('#btn_src').hide();
            $('#btn_xls').hide();
            $('#btn_pdf').hide();
            $('#Progress').show();

            $('#f_Search').submit();
        });

        $("#btn_xls").on('click',function(e){
            e.preventDefault();
        });


    });
</script>
<?php
include(INC."/footer.php");
