<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


/*
1 gennaio
6 gennaio
25 aprile
1 maggio
2 giugno
15 agosto
1 novembre
8 dicembre
25 e 26 dicembre


echo date("d M Y", easter_date(2019));

DIE;


*/
$CurrentDate = date("Y-m-d");
$CurrentYear = date("Y");






    $CityId                 = 'U480';
    $Rigid                  = 0;

    $RangeDayMin            = 60;
    $RangeDayMax            = 300;
    $f_AmountLimit          = 1.00;
    $n_PaymentDayAccepted   = 2;


    $WaitDay = 30;


    $rs_Customer = $rs->Select('Customer', "CityId='" .$CityId . "'");
    $r_Customer = mysqli_fetch_array($rs_Customer);

    $LumpSum = $r_Customer['LumpSum'];


    $rs_FineProcedure = $rs->Select('V_PaymentProcedure',"Id IN (SELECT FineId FROM FineHistory  WHERE NotificationTypeId=6 AND NotificationDate<='2018-08-31') AND CityId='".$CityId."' AND ProtocolYear=2018");

echo $CityId;

    if(mysqli_num_rows($rs_FineProcedure)>0){



        $ProcessingDate= date("Y-m-d");
        $ProcessingStartTime= date("H:i:s");


        $n_CountFine                = 0;

        $n_CountFineClosed          = 0;
        $n_CountFineNotPayed        = 0;
        $n_CountFinePartialPayed    = 0;
        $n_CountFineDispute         = 0;
        $n_CountFineOutDate         = 0;

        $str_FineOutDate            = '';
        $str_FinePartialPayed       = '';

        $str_Content                = '';


        echo " ". mysqli_num_rows($rs_FineProcedure)." <br />";

        while($r_FineProcedure = mysqli_fetch_array($rs_FineProcedure)){

            $n_CountFine++;



            $b_FineDispute = false;
            $n_DisputeDay = 0;

            $NotificationDate = $r_FineProcedure['NotificationDate'];


            $rs_FineDispute = $rs->Select('V_FineDispute',"FineId=".$r_FineProcedure['Id']);
            if(mysqli_num_rows($rs_FineDispute)>0){

                $r_FineDispute = mysqli_fetch_array($rs_FineDispute);
                //todo FineSuspension
                if($r_FineDispute['DisputeStatusId']==2){

                    $n_DisputeDay = DateDiff("D", $r_FineDispute['DateFile'], $r_FineDispute['DateMerit'])+1;

                }else{
                    $b_FineDispute = true;
                    $n_CountFineDispute++;
                }

            }


            if(! $b_FineDispute){

                $AdditionalFee = $r_FineProcedure['AdditionalFee'.$LumpSum];
                $ReducedFee = $Fee = $r_FineProcedure['Fee'] + $AdditionalFee - $f_AmountLimit;
                $MaxFee = $r_FineProcedure['MaxFee'] + $AdditionalFee;


                if ($r_FineProcedure['ReducedPayment']) {
                    $ReducedFee = ($r_FineProcedure['Fee'] * FINE_PARTIAL) + $AdditionalFee - $f_AmountLimit;

                }



                $n_Day = DateDiff("D", $NotificationDate, $CurrentDate)+1;


                $n_CalcDay = $n_Day - $RangeDayMin;
                $RangeDayMax += $n_DisputeDay;



                if($n_CalcDay>$WaitDay && $n_CalcDay<=$RangeDayMax){

                    $rs_FinePayment = $rs->SelectQuery("SELECT Min(PaymentDate) PaymentDate, SUM(Amount) Amount FROM FinePayment WHERE FineId=". $r_FineProcedure['Id'] ." GROUP BY Amount");
                    if(mysqli_num_rows($rs_FinePayment)>0){
                        $r_FinePayment = mysqli_fetch_array($rs_FinePayment);

                        $n_Day = DateDiff("D", $NotificationDate, $r_FinePayment['PaymentDate'])-$n_PaymentDayAccepted;

                        if($n_Day<=5){
                            if($r_FinePayment['Amount']>=$ReducedFee){
                                echo $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. " PAGAMENTO RIDOTTO OK<br />";
                                $n_CountFineClosed++;

                            } else {
                                echo $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. " PAGAMENTO RIDOTTO NO ".$r_FinePayment['Amount'].">=".$ReducedFee."<br />";
                                $n_CountFinePartialPayed++;

                                $str_FinePartialPayed .= $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. "<br>";

                            }
                        } else{
                            if($r_FinePayment['Amount']>=$Fee){
                                echo $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. " PAGAMENTO NORMALE OK<br />";
                                $n_CountFineClosed++;

                            } else {
                                echo $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. " PAGAMENTO NORMALE NO GG:".$n_Day." ".$r_FinePayment['Amount'].">=".$Fee."<br />";
                                $n_CountFinePartialPayed++;

                                $str_FinePartialPayed .= $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. "<br>";
                            }
                        }

                    } else {
                        echo $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. " NON PAGATO<br />";
                        $n_CountFineNotPayed++;







                    }


                } else{

                    if($n_CalcDay>$RangeDayMax){
                        $n_CountFineOutDate++;

                        $str_FineOutDate .= $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. " ";
                    }

                }

                $PaymentProcedure = 0;

            }else{
                if($Rigid){
                    // todo Rigid = 1
                }else{

                    $PaymentProcedure = 0;

                }
            }



        }
        $ProcessingEndTime= date("H:i:s");



        if($n_CountFine>0){


                $str_Content = ": sono stati elaborati n. ".$n_CountFine." verbali";


                if($n_CountFineClosed>0){
                $str_Content .= "<br />VERBALI CHIUSI PER PAGAMENTO CORRETTO: ".$n_CountFineClosed." <br />";
                }
                if($n_CountFineNotPayed>0){
                $str_Content .= "<br />VERBALI NON PAGATI: ".$n_CountFineNotPayed." <br />";
                }
                if($n_CountFinePartialPayed>0){
                $str_Content .= "<br />VERBALI PAGATI PARZIALMENTE: ".$n_CountFinePartialPayed.$str_FinePartialPayed." <br />";
                }
                if($n_CountFineOutDate>0){
                    $str_Content .= "<br />VERBALI OLTRE TEMPO MASSIMO NON ELABORATI: ".$str_FineOutDate." <br />";
                }
                if($n_CountFineDispute>0){
                    $str_Content .= "<br />VERBALI IMPUGNATI IN ATTESA: ".$n_CountFineDispute." <br />";
                }

                echo $str_Content;



        }


    }





DIE;
header("location: frm_reclaim_payment.php?PaymentTypeId=".$PaymentTypeId);