<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$FromDate= CheckValue('FromDate','s');
$ToDate= CheckValue('ToDate','s');
$ReclaimPayment = CheckValue('ReclaimPayment','n');


if($ReclaimPayment==1){
    $str_CheckReclaimPayment =" CHECKED ";
    $str_Where = " AND FP.FineId>=0";
} else {
    $str_CheckReclaimPayment = "";
    $str_Where = " AND FP.FineId>0";
}


$PaymentTypeId = CheckValue('PaymentTypeId','n');

if($FromDate == "" && $ToDate=="") {
    $str_FromToDate = '
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            Da
        </div>        
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_date" name="FromDate" type="text" style="width:9rem" value="'.date("01/01/".$_SESSION['year']).'">
        </div>
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            A
        </div>        
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_date" name="ToDate" type="text" style="width:9rem" value="'.date("31/12/".$_SESSION['year']).'">
        </div>
    ';
} else {
    $str_FromToDate = '
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            Da
        </div>        
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_date" name="FromDate" type="text" style="width:9rem" value="'.$FromDate.'">
        </div>
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            A
        </div>        
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_date" name="ToDate" type="text" style="width:9rem" value="'.$ToDate.'">
        </div>
    ';
}



$str_out .='
    <form id="f_Search" action="prn_trafat.php" method="post">
    <div class="row-fluid">
        <div class="col-sm-12" >
            <div class="col-sm-11" style="height:4.2rem; border-right:1px solid #E7E7E7;">
                <div class="col-sm-1 BoxRowLabel">
                    Accertamento:
                </div>
    
                '. $str_FromToDate .'
                <div class="col-sm-1 BoxRowLabel">
                    Metodo:
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '.CreateSelect("sarida.PaymentType","Disabled=0","Title","PaymentTypeId","Id","Title",$PaymentTypeId,false,15) .'
                </div>
                <div class="col-sm-2 BoxRowCaption">
                     <input type="checkbox" name="ReclaimPayment" value="1" '.$str_CheckReclaimPayment.'>Non associati
                </div>
                 <div class="col-sm-12 BoxRowLabel"></div>
            </div>
            <div class="col-sm-1 table_caption_H" style="height:4.2rem;">
                <img src="'.IMG.'/progress.gif" style="width:35px;display: none;position:absolute;left:20px;top:10px" id="Progress"/>
                <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
    
           
                    <button class="btn btn-primary" id="btn_trafat">
                       <i class="glyphicon glyphicon-filter" style="font-size:1.5rem"></i>
                    </button>
           
                </div>

            </div>    
        </div>
    </div>
    </form>
';


$str_out.= '    
    	<div class="row-fluid">
        	<div class="col-sm-12">
            	<div class="table_label_H col-sm-1">N importi</div>
				<div class="table_label_H col-sm-9">Ente</div>
				<div class="table_label_H col-sm-2">Totale</div>
				<div class="clean_row HSpace4"></div>';






if($FromDate == "" && $ToDate=="") {
    $str_out.=
        '<div class="table_caption_H col-sm-12">
			Scegliere un periodo
		</div>';
}else{
    $CityId = $_SESSION['cityid'];
    $d_DateFrom = DateInDB($FromDate);
    $d_DateTo = DateInDB($ToDate);

    if($PaymentTypeId>0){

        $str_Where .= ($PaymentTypeId==10) ? " AND (FP.PaymentTypeId = 1 OR FP.PaymentTypeId = 2) " : " AND FP.PaymentTypeId = " .$PaymentTypeId." ";
    }

    /*

     CityId
    FineId
    BankMgmt


    RefundStatus



    CityId

    NationalBankAccount   /    ForeignBankAccount


    */


    $rs_Payment = $rs->SelectQuery("
    SELECT
    Amount, FP.PaymentDate, C.ManagerName, PT.TrafatCode
    FROM FinePayment FP JOIN Customer C
    ON FP.CityId = C.CityId
    JOIN sarida.PaymentType PT ON FP.PaymentTypeId=PT.Id
    WHERE FP.BankMgmt = 1 AND FP.PaymentDate >='".$d_DateFrom."' AND  FP.PaymentDate<='".$d_DateTo."' AND FP.CityId='".$CityId."'".$str_Where." 
    ORDER BY C.ManagerName,FP.PaymentDate,PT.TRafatCode
    
");




    if (mysqli_num_rows($rs_Payment)==0){
        $str_out.='<div class="table_caption_H col-sm-12">
			Nessun record trovato
		</div>';
    }else{

        $str_file = "trafat_".$CityId."_".$d_DateFrom."_".$d_DateTo.".txt";
        $str_zipfile = "trafat_".$CityId."_".$d_DateFrom."_".$d_DateTo.".zip";



        $file = fopen(ROOT.'/doc/print/payment/'.$str_file,"w");

        $flt_Total = 0;
        $str_CustomerName = "";

        $n_RowCont =0;
        while($r_Payment = mysqli_fetch_array($rs_Payment)){
            $n_RowCont++;
            //DESCRIZIONE DEI CAMPI UTILIZZATI  OGNI CAMPO HA UNA LUNGHEZZA PREDEFINITA VISUALIZZATA A LATO E TRA PARENTESI UN VALORE PREDEFINITO:
            //DITTA 5 (00080)
            //VERSIONE PROGRAMMA 1 (1)
            //TIPO ARCHIVIO 1 (0)
            //RAGIONE SOCIALE CLIENTE 32
            //INDIRIZZO 30
            //CAP 5 (00000)
            //CITTA 25
            //PROVINCIA 2
            //CODICE FISCALE 16
            //PARTITA IVA 11 (00000000000)
            //PERSONA FISICA 1
            //POSIZIONE SPAZIO FRA COGNOME E NOME 2 (00)
            //CAUSALE 3 (042)
            //DESCRIZIONE CAUSALE 15 (RISCOSSIONE)
            //DESCRIZIONE AGGIUNTIVA 18 (NOME DEL COMUNE IN OGGETTO)
            //DATA DOCUMENTO 6 (GGMMAA)
            //NUMERO  7
            //NUMERO DOCUMENTO 5
            //SEZIONALE 2
            //ESTRATTO CONTO NUMERO PARTITA 6
            //ESTRATTO CONTO ANNO PARTITA 2
            //IMPONIBILE 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
            //ALIQUOTA 3
            //IVA11 1 (CODICE DI MEMORIZZAZIONE PER IVA11)
            //IMPOSTA 11 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 10 E SARA' SEGUITO DA UN + CHE OCCUPA L'11� BIT)
            //TOTALE FATTURA 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
            //CONTO DI RICAVO/COSTO 7
            //IMPORTO RICAVO/COSTO 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
            //CONTO 7
            //DARE AVERE 1 (D=DARE A=AVERE)
            //IMPORTO 12 (CAMPO SEGNATO, QUINDI AVRA' LUNGHEZZA 11 E SARA' SEGUITO DA UN + CHE OCCUPA IL 12� BIT)
            //DESCRIZIONE AGGIUNTIVA 18
            //ESTRATTO CONTO NUMERO PARTITA PAGAMENTO 6
            //ESTRATTO CONTO ANNO PARTITA PAGAMENTI 2

            //PRIMA PARTE DEL RECORD= DITTA+VERSIONE+TIPO ARCHIVIO+RAGIONE SOCIALE+INDIRIZZO+CAP+CITTA
            //+PROVINCIA+CODICE FISCALE+PARTITA IVA+PERSONA FISICA+DIVISIONE NOME COGNOME+CAUSALE+DESCRIZIONE CAUSALE
            //+CAUSALE AGGIUNTIVA (NOME DEL COMUNE DI RIFERIMENTO)
            //INOLTRE E' STATA AGGIUNTA LA DATA DEL VERSAMENTO
            //SECONDA PARTE DEL RECORD=N_DOC (NUMERO DOCUMENTO E SEZIONALE)+ESTRATTO CONTO NUMERO PARTITA+ANNO PARTITA+
            //DATI IVA SINGOLO (IMPONIBILE, ALIQUOTA,IVA11,IMPOSTA) RIPETUTI 8 VOLTE+TOTALE FATTURA+RICAVI(CONTO DI RICAVO E IMPORTI DI RICAVO)
            //RIPETUTO 8 VOLTE DOPODICHE' CI SONO I CODICI DEL CONTO DARE SEGUITI DA UNA D E DAL TOTALE DEI PAGAMENTI E DAL PAGAMENTO PARZIALE
            //(DESCRIZIONE AGGIUNTIVA, ESTRATTO CONTO NUMERO PARTITA PAGAMENTO,ANNO PARTITA PAGAMENTO)
            //INFINE CI SONO I CODICI DEL CONTO AVERE SEGUITI DA UNA A E DAL TOTALE DEI PAGAMENTI E DAL PAGAMENTO PARZIALE (DESCRIZIONE
            //AGGIUNTIVA, ESTRATTO CONTO NUMERO PARTITA PAGAMENTO,ANNO PARTITA PAGAMENTO)
            //INFINE C'E' UNA SEQUENZA DI 78 PAGAMENTO VUOTO (CONTO, DARE AVERE,IMPORTO)+PAGAMENTO PARZIALE



            if($str_CustomerName!=""){
                if(($str_CustomerName==$r_Payment["ManagerName"])){
                    $flt_Total += $r_Payment["Amount"];
                }
                else{
                    $str_out .= '
                        <div class="table_caption_H col-sm-1">
			                '.$n_RowCont.'
		                </div>
                        <div class="table_caption_H col-sm-9">
			                '.$str_CustomerName.'
		                </div>		                
                        <div class="table_caption_H col-sm-2">
			                '.NumberDisplay($flt_Total).'
		                </div>		                		              
		                ';

                    $str_CustomerName = $r_Payment["ManagerName"];
                    $flt_Total = $r_Payment["Amount"];
                }


            }else{
                $str_CustomerName = $r_Payment["ManagerName"];
                $flt_Total = $r_Payment["Amount"];
            }


            $str_Record = '';

            $str_Record .= '00080';   //Codice della ditta che deve essere lungo 5 bit

            $str_Record .= '1';    //Versione del programma che deve essere lungo 1 bit

            $str_Record .= '0';        //Tipo di archivio che deve essere lungo 1 bit

            for($i=0;$i<=61;$i++)
            {
                // 32 spazi vuoti per la ragione sociale
                // 30 spazi vuoti per l'indirizzo
                $str_Record .= ' ';
            }

            // Cap del contribuente
            $str_Record .= '00000';

            for($i=0;$i<=42;$i++)
            {
                // 25 spazi per la citta
                // 2 spazi per la provincia
                // 16 spazi per il codice fiscale
                $str_Record .= ' ';
            }
            //Partita Iva del contribuente
            $str_Record .= '00000000000';

            //Persona Fisica
            $str_Record .= ' ';
            //Posizione spazio fra Cognome e Nome
            $str_Record .= '00';

            //Causale
            $str_Record .= '042';

            //Descrizione della causale
            $str_Record .= 'RISCOSSIONE    ';



            //Fine prima parte record e aggiunta del nome del comune e della data del pagamento


            $ManagerName=substr($r_Payment["ManagerName"],0,18);

            $str_Record .= $ManagerName;

            for($i=0;$i<=(17-strlen($ManagerName));$i++) {
                $str_Record .= ' ';
            }

            $PaymentDate = $r_Payment['PaymentDate'];
            $str_data =substr($PaymentDate,8) . substr($PaymentDate,5,2) . substr($PaymentDate,2,2);


            $str_Record .= $str_data;

            //Inizio seconda parte del record
            //Scrivo 5 zeri per numero documento, 2 per il sezionale,6 per l'estratto conto numero della partita
            //2 per l'anno della partita
            for($i=0;$i<=14;$i++) {
                $str_Record .= '0';
            }


            //Ciclo di 8 volte dei campi imponibile (11),prima metto un + aliquota (3), iva11 (1),imposta (10) finisco con un +
            for($j=0;$j<=7;$j++) {
                for($i=0;$i<=10;$i++) {
                    $str_Record .= '0';
                }
                $str_Record .= '+';

                for($i=0;$i<=13;$i++) {
                    $str_Record .= '0';
                }
                $str_Record .= '+';
            }
            //Totale fattura (11) seguito da +
            for($i=0;$i<=10;$i++) {
                $str_Record .= '0';
            }
            $str_Record .= '+';

            //Ciclo di 8 volte  sul conto di ricavo (7) e l'importo di ricavo (11) seguito da un +
            for($j=0;$j<=7;$j++) {
                for($i=0;$i<=17;$i++) {
                    $str_Record .= '0';
                }
                $str_Record .= '+';
            }
            //Fine seconda parte del record.
            //Selezione del tipo di conto dare a seconda del tipo di pagamento selezionato


            $str_Record .= $r_Payment["TrafatCode"];

            //Scrittura del conto che occupa 7 bit seguito da D che sta per Dare
            $str_Record .= 'D';

            //Totale dei versamenti per data e comune seguito da un +


            $str_Amount = $r_Payment["Amount"];
            $str_Zero = '';
            for($i=0;$i<=(10-strlen($str_Amount));$i++) {
                $str_Zero .= '0';
            }
            $str_Record .= $str_Zero.$str_Amount;
            $str_Record .= '+';


            //Pagamento parziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
            //della partita del pagamento, 2 zeri per l'anno della partita
            for($i=0;$i<=17;$i++) {
                $str_Record .= ' ';
            }

            for($i=0;$i<=7;$i++) {
                $str_Record .= '0';
            }




            //Numero del conto avere lungo 7 bit seguito da A che sta per avere
            $str_Record .= "5205512A";



            //Totale dei pagamenti per comune e data che deve occupare 11 bit seguito da +
            $str_Amount = $r_Payment["Amount"];
            $str_Zero = '';
            for($i=0;$i<=(10-strlen($str_Amount));$i++) {
                $str_Zero .= '0';
            }



            $str_Record .= $str_Zero.$str_Amount;
            $str_Record .= '+';


            //Pagamento parziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
            //della partita del pagamento, 2 zeri per l'anno della partita
            for($i=0;$i<=17;$i++) {
                $str_Record .= ' ';
            }

            for($i=0;$i<=7;$i++) {
                $str_Record .= '0';
            }


            //Ciclo di 78 volte sul pagamento vuoto [costituito da 7 zeri per il conto, seguiti da uno spazio vuoto per D (dare)
            //o A (Avere), e da 11 zeri per l'importo e da un +] e sul pagamento parziale.
            for($j=0;$j<=77;$j++) {
                //Pagamento vuoto
                for($i=0;$i<=6;$i++) {
                    $str_Record .= '0';
                }
                $str_Record .= ' ';
                for($i=0;$i<=10;$i++) {
                    $str_Record .= '0';
                }
                $str_Record .= '+';

                //Pagamento prziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
                //della partita del pagamento, 2 zeri per l'anno della partita
                for($i=0;$i<=17;$i++) {
                    $str_Record .= ' ';
                }
                for($i=0;$i<=7;$i++) {
                    $str_Record .= '0';
                }
            }

            fwrite($file,$str_Record);
        }


        fclose($file);

        $zip = new ZipArchive();
        if ($zip->open(ROOT.'/doc/print/payment/'.$str_zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile(ROOT.'/doc/print/payment/'.$str_file,$str_file);
            $zip->close();
            $_SESSION['Documentation'] = $MainPath.'/doc/print/payment/'.$str_zipfile;
        }



        $str_out .= '
                        <div class="table_caption_H col-sm-1">
			                '.$n_RowCont.'
		                </div>
                        <div class="table_caption_H col-sm-9">
			                '.$str_CustomerName.'
		                </div>		                
                        <div class="table_caption_H col-sm-2">
			                '.NumberDisplay($flt_Total).'
		                </div>		                		              
		                ';
    }

}


echo $str_out;



?>

    <script type="text/javascript">

        $(document).ready(function () {
            $("#btn_trafat").on('click',function(e){
                e.preventDefault();
                $('#btn_trafat').hide();
                $('#Progress').show();

                $('#f_Search').submit();
            });


        });
    </script>
<?php
include(INC."/footer.php");