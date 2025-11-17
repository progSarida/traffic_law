<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(CLS."/cls_flow.php");
include(INC . "/function.php");
include(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');




$str_Where = "CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year']." AND StatusTypeId NOT IN(33,34,36)";


$rs_Fine = $rs->SelectQuery("SELECT COUNT(*) TOT, Max(ProtocolId) ProtocolId FROM Fine WHERE " . $str_Where );
$r_Fine = mysqli_fetch_array($rs_Fine);

$rs_Payment = $rs->SelectQuery("SELECT COUNT(*) TOT FROM V_FinePayment WHERE " . $str_Where );
$r_Payment = mysqli_fetch_array($rs_Payment);


$str_FineController = '';
if($_SESSION['controllerid']>0){
    $rs_FineController = $rs->SelectQuery("SELECT COUNT(*) TOT FROM Fine WHERE ControllerId=".$_SESSION['controllerid']. " AND ".$str_Where);
    $r_FineController = mysqli_fetch_array($rs_FineController);
    $str_FineController = '
        
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12">
        <div class="table_label_H col-sm-3">TOT VERBALI ACCERTATI UTENTE</div>
        <div class="table_caption_H col-sm-9" style="height:3rem;font-size: 1.5rem;line-height: 3rem;">'. $r_FineController['TOT'] .'</div>
    </div>
    ';
}

$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Informazioni generali:
                <ul style="list-style-position: inside;">
                	<li>Gli eventi dei processi automatici sono riportati nel menu in alto a destra (<i class="fa fa-bell"></i>). I contatori rappresentano il numero di notifiche non ancora segnate come lette, divise per tipologia.</li>
                </ul>
            	</li>
            </ul>
        </div>
    </div>

    <div class="clean_row HSpace4"></div>

    <div class="col-sm-12">
        <div style="width:100%;height:300px;background: url(\'img/index/'.$_SESSION['cityid'].'/index.jpg\') no-repeat; background-size: 100%;">
            
        </div>
    </div>

    <div class="col-sm-12">
        <div class="table_label_H col-sm-3">VIOLAZIONI TOTALI INSERITE</div>
        <div class="table_caption_H col-sm-9" style="height:3rem;font-size: 1.5rem;line-height: 3rem;">'. $r_Fine['TOT'] .'</div>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12">
        <div class="table_label_H col-sm-3">ULTIMO CRON ELABORATO</div>
        <div class="table_caption_H col-sm-9" style="height:3rem;font-size: 1.5rem;line-height: 3rem;">'. $r_Fine['ProtocolId'] .'</div>
            
    </div>
            
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12">
        <div class="table_label_H col-sm-3">TOT VERBALI PAGATI</div>
        <div class="table_caption_H col-sm-9" style="height:3rem;font-size: 1.5rem;line-height: 3rem;">'. $r_Payment['TOT'] .'</div>
            
    </div>
    '. $str_FineController. '
</div>
';



if($_SESSION['userlevel']>=3){
    
    $a_StatusTypeId = array();
    
    $a_StatusTypeId[1] = "Senza trasgressore";
    $a_StatusTypeId[2] = "Noleggi non completi";
    $a_StatusTypeId[5] = "In attesa dati trasgressore";
    $a_StatusTypeId[10]= "Completati";
    
    $a_StatusTypeId[12]= "PEC create";
    
    $a_StatusTypeId[15]= "Da spedire";
    $a_StatusTypeId[20]= "In attesa di data di notifica";
    $a_StatusTypeId[23]= "Notifiche esito negativo";
    $a_StatusTypeId[25]= "Notificati";
    $a_StatusTypeId[27]= "Non pagati";
    $a_StatusTypeId[28]= "Pagati parziali";
    
    $a_StatusTypeId[30]= "Notificati e pagati";
    $a_StatusTypeId[32]= "Chiuso da Ufficio";   // Aggiunto da Paolo il 7/7/20
    $a_StatusTypeId[33]= "Rinotificati";
    $a_StatusTypeId[35]= "Archiviati comune";
    $a_StatusTypeId[36]= "Noleggio";
    $a_StatusTypeId[37]= "Archiviati ufficio";
    
    $a_StatusTypeId[40]= "Coattiva";
    
    $a_StatusTypeId[90]= "Annullati comune";
    $a_StatusTypeId[91]= "Annullati ufficio";
    
    
    $rs_Fine = $rs->SelectQuery("SELECT StatusTypeId, COUNT(StatusTypeId) TOT FROM Fine WHERE " . $str_Where ." AND (StatusTypeId<=10 OR StatusTypeId>=90) AND StatusTypeId!=0 GROUP BY StatusTypeId ORDER BY StatusTypeId");
    $str_StatusTypeId = '';
    $n_Tot = 0;
    $n_Height = 0;
    $n_RegularPostalFine = 0;
    
    
    while($r_Fine = mysqli_fetch_array($rs_Fine)){
        
        $n_TMP_Tot = $r_Fine['TOT'];
        if ($r_Fine['StatusTypeId']>=7 && $r_Fine['StatusTypeId']<=9) $n_RegularPostalFine+=$n_TMP_Tot;
        else{
            if($r_Fine['StatusTypeId']==10) $n_TMP_Tot+=$n_RegularPostalFine;
            $str_StatusTypeId .= '
            <div class="col-sm-4 BoxRowLabel">
                '. $a_StatusTypeId[$r_Fine['StatusTypeId']] .'
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '. $n_TMP_Tot .'
            </div>
            <div class="clean_row HSpace4"></div>
            ';
            $n_Tot += $r_Fine['TOT'];
            $n_Height += 2.26;
        }
        
        
    }
    
    if($n_Height==0) $n_Height = 2.26;
    $str_out .= '
        <div class="clean_row HSpace4"></div>
    	<div class="row-fluid">
        	<div class="col-sm-12">
        	    <div class="table_label_H col-sm-12">ANALISI VERBALI</div>
        	    <div class="clean_row HSpace4"></div>
                <div class="col-sm-3 BoxRowLabel" style="text-align:center; line-height: '. $n_Height .'rem; font-size:2rem;height: '. $n_Height .'rem">
                    Preinserimenti in elaborazione
                </div>
                <div class="col-sm-3 BoxRowCaption" style="text-align:center; line-height: '. $n_Height .'rem; font-size:2rem; height: '. $n_Height .'rem">
                    '. $n_Tot .'
                </div>
                        
                <div class="col-sm-6">
                    <div class="col-sm-12">
                    '. $str_StatusTypeId .'
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
        	';
    
    $rs_Fine = $rs->SelectQuery("SELECT StatusTypeId, COUNT(StatusTypeId) TOT FROM Fine WHERE " . $str_Where ." AND (StatusTypeId>=15 AND StatusTypeId<90) AND ProtocolId>0 GROUP BY StatusTypeId ORDER BY StatusTypeId");
    
    $str_StatusTypeId = '';
    $n_Tot = 0;
    $n_Height = 0;
    while($r_Fine = mysqli_fetch_array($rs_Fine)){
        $str_StatusTypeId .= '
        <div class="col-sm-4 BoxRowLabel">
            '. $a_StatusTypeId[$r_Fine['StatusTypeId']] .'
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '. $r_Fine['TOT'] .'
        </div>
        <div class="clean_row HSpace4"></div>
        ';
        $n_Tot += $r_Fine['TOT'];
        $n_Height += 2.26;
        
    }
    if($n_Height==0) $n_Height = 2.26;
    
    //PEC da creare
    $str_Where = " CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND ControllerId IS NOT NULL";
    $str_Where .= " AND ((StatusTypeId=14) OR (StatusTypeId=10))";
    $str_Where .= " AND Id IN (SELECT Id FROM V_ViolationAll WHERE (PEC != '' AND PEC IS NOT NULL) GROUP BY Id)";
    $rs_FinePEC = $rs->SelectQuery("SELECT COUNT(Id) TOT FROM V_ViolationAll WHERE " . $str_Where);
    $n_PEC = mysqli_fetch_array($rs_FinePEC)['TOT'];
    
    //PEC da firmare
    $rs_FinePEC = $rs->SelectQuery("
        SELECT COUNT(FH.Id) TOT from FineHistory FH
        LEFT JOIN Fine F ON F.Id=FH.FineId
        WHERE F.CityId = '".$_SESSION['cityid']."'
        AND F.ProtocolYear=".$_SESSION['year']."
        AND FH.NotificationTypeId = 15
        AND FH.Documentation NOT LIKE '%_signed%'");
    $n_PECToSign = mysqli_fetch_assoc($rs_FinePEC)['TOT'];
    
    //Notifiche da creare
    $str_Where = " VV.CityId='".$_SESSION['cityid']."' AND VV.ProtocolYear=".$_SESSION['year']." AND FH.NotificationTypeId=15";
    $str_Where .= " AND ((VV.FineChiefControllerId={$_SESSION['controllerid']} AND VV.StatusTypeId=12 AND VV.FineTypeId IN(3,4)) OR (VV.StatusTypeId=12))";
    $str_Where .= " AND length(COALESCE(VV.PEC,''))>0";
    
    $rs_FinePEC = $rs->SelectQuery("
        SELECT COUNT(VV.Id) TOT
        FROM V_ViolationAll VV
        JOIN FineHistory FH ON VV.Id=FH.FineId AND VV.TrespasserId=FH.TrespasserId
        WHERE $str_Where");
    $n_PECNToCreate = mysqli_fetch_assoc($rs_FinePEC)['TOT'];
    
    //Notifiche da firmare
    $rs_FinePEC = $rs->SelectQuery("
        SELECT COUNT(IFNULL(FD2.Id, 1)) TOT from FineDocumentation FD
        LEFT JOIN FineDocumentation FD2 on FD.FineId=FD2.FineId AND FD.Documentation=REPLACE(FD2.Documentation, '_signed', '') AND FD2.DocumentationTypeId =14
        LEFT JOIN Fine F ON F.Id=FD.FineId
        WHERE FD2.Id IS NULL
        AND F.CityId = '{$_SESSION['cityid']}'
        AND F.ProtocolYear = {$_SESSION['year']}
        AND FD.DocumentationTypeId = 13
        AND ((F.FineChiefControllerId={$_SESSION['controllerid']} AND F.StatusTypeId=20 AND F.FineTypeId IN(3,4)) or F.StatusTypeId=20)");
    $n_PECNToSign = mysqli_fetch_array($rs_FinePEC)['TOT'];
    
    //PEC da creare a partire da preinserimenti
    $str_Where = " CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND ProtocolId = 0 AND ControllerId IS NOT NULL";
    $str_Where .= " AND StatusTypeId=10 AND FineTypeId = 1";
    $str_Where .= " AND Id IN (SELECT Id FROM V_ViolationAll WHERE (PEC != '' AND PEC IS NOT NULL) GROUP BY Id)";
    $rs_FinePEC = $rs->SelectQuery("SELECT COUNT(Id) TOT FROM V_ViolationAll WHERE " . $str_Where);
    $n_PECpre = mysqli_fetch_array($rs_FinePEC)['TOT'];
    
    $str_out .= '
                <div class="col-sm-3 BoxRowLabel" style="text-align:center; line-height: '. $n_Height .'rem; font-size:2rem;height: '. $n_Height .'rem">
                    Verbali creati
                </div>
                <div class="col-sm-3 BoxRowCaption" style="text-align:center; line-height: '. $n_Height .'rem; font-size:2rem; height: '. $n_Height .'rem">
                    '. $n_Tot .'
                </div>
                        
                <div class="col-sm-6">
                    <div class="col-sm-12">
                    '. $str_StatusTypeId .'
                    </div>
                </div>
                        
                <div class="clean_row HSpace4"></div>
                        
        	    <div class="table_label_H col-sm-12">ANALISI VERBALI PEC</div>
                        
        	    <div class="clean_row HSpace4"></div>
                        
                <div class="col-sm-3 BoxRowLabel">
                    Verbali PEC da creare
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '. $n_PEC .'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Notifiche PEC da creare
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '. $n_PECNToCreate .'
                </div>
                        
                <div class="clean_row HSpace4"></div>
                        
                <div class="col-sm-3 BoxRowLabel">
                    Verbali PEC da firmare
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '. $n_PECToSign .'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Notifiche PEC da firmare
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '. $n_PECNToSign .'
                </div>

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-3 BoxRowLabel">
                    Preinserimenti PEC da creare
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '.$n_PECpre.'
                </div>
                <div class="col-sm-6 BoxRowLabel">
                </div>
            </div>
        </div>
    </div>
</div>
';
    
    $cls_flow = new cls_flow($_SESSION['cityid']);
    $a_flows = $cls_flow->getFlowsNumber();
    
    $str_out.= '
        <div class="clean_row HSpace4"></div>
        <div class="table_label_H col-sm-12" style="height:3rem;font-size: 1.6rem;line-height: 3rem;"><b>ANALISI FLUSSI</b></div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="mioTab">
                <li tab_position="1" class="tab_button active" id="tab_AG"><a href="#AG" data-toggle="tab">AG</a></li>
                <li tab_position="2" class="tab_button" id="tab_PEC"><a href="#PEC" data-toggle="tab">PEC</a></li>
                <li tab_position="3" class="tab_button" id="tab_AR"><a href="#AR" data-toggle="tab">AR</a></li>
                <li tab_position="4" class="tab_button" id="tab_AvvisoBonario"><a href="#AvvisoBonario" data-toggle="tab">Avviso Bonario</a></li>
                <li tab_position="5" class="tab_button" id="tab_LetteraOrdinaria"><a href="#LetteraOrdinaria" data-toggle="tab">Lettera Ordinaria</a></li>
           </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane active" id="AG">'.$cls_flow->htmlFlowNumber($a_flows,1).'</div>
            <div class="tab-pane" id="PEC">'.$cls_flow->htmlFlowNumber($a_flows,7).'</div>
            <div class="tab-pane" id="AR">'.$cls_flow->htmlFlowNumber($a_flows,2).'</div>
            <div class="tab-pane" id="AvvisoBonario">'.$cls_flow->htmlFlowNumber($a_flows,3).'</div>
            <div class="tab-pane" id="LetteraOrdinaria">'.$cls_flow->htmlFlowNumber($a_flows,4).'</div>
        </div>';
    
}


echo $str_out;

include(INC . "/footer.php");
