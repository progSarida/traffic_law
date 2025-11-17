<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$str_Communication = "";

$Search_FineId = $_POST['Search_FineId'];


$str_Sql="
SELECT  

FC.FineId, FC.CommunicationDate,
TT.Description TrespasserTypeDescription, 
T.CompanyName, T.Surname, T.Name,
T.LicenseNumber, T.LicenseDate, T.LicenseCategory, T.LicenseOffice 	

FROM FineCommunication FC JOIN TrespasserType TT ON FC.TrespasserTypeId = TT.Id
JOIN Trespasser T ON FC.TrespasserId = T.Id

WHERE FC.FineId=".$Search_FineId." ORDER BY TrespasserTypeId
";





$str_Communication ='

    	<div class="row-fluid">
        	<div class="col-sm-12">';


    $rs_Communication = $rs->SelectQuery($str_Sql);
    While($r_Communication = mysqli_fetch_array($rs_Communication)){
        $str_Communication .='

  	            <div class="col-sm-12" >
                    <div class="col-sm-3 BoxRowLabel">
                        Tipo trasgressore
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                       '.$r_Communication['TrespasserTypeDescription'].'
                    </div>
                    <div class="clean_row HSpace4"></div>  	            
                    <div class="col-sm-2 BoxRowLabel">
                        Nominativo
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . $r_Communication['CompanyName'].' '.$r_Communication['Surname'].' '.$r_Communication['Name'] . '
                    </div> 	            
                    <div class="col-sm-3 BoxRowLabel">
                        Data comunicazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . DateOutDB($r_Communication['CommunicationDate']) . '
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Patente n
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                       ' . $r_Communication['LicenseNumber'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        categoria
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       ' . $r_Communication['LicenseCategory'].'
                    </div>
                    <div class="clean_row HSpace4"></div>  	   
                    <div class="col-sm-3 BoxRowLabel">
                        Ente rilascio
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                       ' . $r_Communication['LicenseOffice'].'
                    </div>                           
                    <div class="col-sm-3 BoxRowLabel">
                        Data rilascio
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                       ' . DateOutDB($r_Communication['LicenseDate']).'
                    </div>   
                    <div class="clean_row HSpace4"></div>
                </div>
                ';
    }


    $str_Communication .='    
            </div>
        </div>            

';



echo json_encode(
	array(
		"Communication" => $str_Communication,

		)
);