<?php

/**
 * Class cls_dispute
 */
class cls_dispute
{
    public $a_dispute;
    public $a_info;

    public function setDispute(array $a_dispute,$checkType=0){
        $this->a_dispute = $a_dispute;
        $this->resetInfo();
        $this->setInfo($checkType);
    }

    public function resetInfo(){
        $this->a_info = array("msg"=>null, "check"=>true, "days"=>0);
    }

    public function setInfo($checkType=0){
        switch ($this->a_dispute['DisputeStatusId']){
            case 1:
                if($checkType==0){
                    $this->a_info['msg'] = "Ricorso in attesa o rinviato";
                    $this->a_info['check'] = false;
                    $this->a_info['responseCode'] = 1;
                }
                else{
                    if($this->a_dispute['FineSuspension']==0){
                        if(DateOutDB($this->a_dispute['SuspensiveDate'])!=null){
                            $this->a_info['msg'] = "Sospensiva ricorso n. ".$this->a_dispute['SuspensiveNumber']." del ".DateOutDB($this->a_dispute['SuspensiveDate']);
                            $this->a_info['check'] = false;
                            $this->a_info['responseCode'] = 2;
                        }
                        else{
                            $this->a_info['msg'] = "Sospensiva del ricorso assente";
                            $this->a_info['check'] = true;
                            $this->a_info['responseCode'] = 3;
                        }
                    }
                    else if($this->a_dispute['FineSuspension']==1){
                        $this->a_info['msg'] = "Ricorso in attesa o rinviato";
                        $this->a_info['check'] = false;
                        $this->a_info['responseCode'] = 4;
                    }
                }

                break;
            case 2:
                $this->a_info['msg'] = "Ricorso respinto/inammissibile";
                $this->a_info['check'] = true;
                $this->a_info['days'] = DateDiff("D", $this->a_dispute['DateFile'], $this->a_dispute['DateMerit'])+1;
                $this->a_info['responseCode'] = 5;
                break;
            case 3:
                $this->a_info['msg'] = "Ricorso accolto";
                $this->a_info['check'] = false;
                $this->a_info['responseCode'] = 6;
                break;
        }
    }

}