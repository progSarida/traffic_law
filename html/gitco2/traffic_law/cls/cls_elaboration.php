<?php


class cls_elaboration
{
    public $type;
    public $a_params;

    public $a_days;
    public $a_missingData;
    public $a_daysLimitation;

    public $a_elaborate;

    public function __construct($type)
    {
        $this->type = $type;
    }

    private function setParams($params){
        $this->a_params = $params;

        $this->a_days = array(
            "covidDays" => 0,
            "passedDays" => 0,
            "minDays" => 0,
            "maxDays" => 0,
            "msg" => ""
        );

        $this->a_missingData = array(
            "check" => null,
            "msg" => null
        );

        $this->a_daysLimitation = array(
            "check" => null,
            "msg" => null,
            "terms" => null
        );

        $this->a_elaborate = array(
            "start" => true,
            "msg" => ""
        );

        if(isset($this->a_params['DisputeCheck'])){
            $this->a_elaborate['start'] = $this->a_params['DisputeCheck'];
        }
    }

    public function checkMissingData($params){
        $this->setParams($params);
        switch($this->type){
            case "126Bis":
                $this->check126BisMissingData();
                break;
        }
    }

    public function check126BisMissingData(){
        if($this->a_params['CommunicationDate']=="" || $this->a_params['CommunicationDate']==null){
            $this->a_missingData['check'] = true;
            $this->a_missingData['msg'] = "Comunicazione assente";
        }
        else{
            $this->a_missingData['check'] = false;
            $this->a_missingData['msg'] = "Comunicazione dati corretta";

            if($this->a_params['CommunicationDelay']==1){
                $delayDays = DateDiff("D", $this->a_params['NotificationDate'], $this->a_params['CommunicationDate']) + 1;
                if($delayDays>$this->a_params['CommunicationDays']){
                    $this->a_missingData['check'] = true;
                    $this->a_missingData['msg'] = "Comunicazione presentata in ritardo";
                }
            }

            if($this->a_params['IncompletedCommunication']==1 && $this->a_missingData['check']===false){
                if($this->a_params['IncompletedCommunicationFlag']==1){
                    $this->a_missingData['check'] = true;
                    $this->a_missingData['msg'] = "Comunicazione incompleta";
                }
            }

        }

        if($this->a_elaborate['start'])
            $this->a_elaborate['start'] = $this->a_missingData['check'];

    }

    public function checkDaysLimitation(){
        switch($this->type){
            case "126Bis":
                $this->check126BisDays();
                break;
        }
    }

    public function check126BisDays(){
        $this->set126BisDays();
        if($this->a_days['passedDays']>$this->a_days['minDays'] && $this->a_days['passedDays']<=$this->a_days['maxDays']){
            $this->a_daysLimitation['check'] = true;
            $this->a_daysLimitation['terms'] = 0;
            $this->a_daysLimitation['msg'] = "Nei termini";
        }
        else if($this->a_days['passedDays']>$this->a_days['maxDays']){
            $this->a_daysLimitation['check'] = false;
            $this->a_daysLimitation['terms'] = 1;
            $this->a_daysLimitation['msg'] = "Oltre i termini";
        }
        else if($this->a_days['passedDays']<=$this->a_days['minDays']){
            $this->a_daysLimitation['check'] = false;
            $this->a_daysLimitation['terms'] = -1;
            $this->a_daysLimitation['msg'] = "In anticipo sui termini";
        }

        if($this->a_elaborate['start'])
            $this->a_elaborate['start'] = $this->a_daysLimitation['check'];
    }

    public function set126BisDays(){

        $this->covidPeriod();

        $this->a_days['passedDays'] = DateDiff("D", $this->a_params['NotificationDate'], $this->a_params['CurrentDate']) + 1;
        $this->a_days['minDays'] = $this->a_params['WaitDay'] + $this->a_params['RangeDayMin'];
        $this->a_days['maxDays'] = $this->a_params['RangeDayMin'] + $this->a_params['RangeDayMax'] + $this->a_params['DisputeDays'] + $this->a_days['covidDays'];

        $this->a_days['msg'] = "Trascorsi ".$this->a_days['passedDays']." LIMITE ".$this->a_days['maxDays'];
        if($this->a_params['DisputeDays']>0 || $this->a_days['covidDays'])
            $this->a_days['msg'].= " ( ".($this->a_params['RangeDayMin'] + $this->a_params['RangeDayMax']);
        if($this->a_params['DisputeDays'] >0)
            $this->a_days['msg'].= " + ricorso ".$this->a_params['DisputeDays'] ;
        if($this->a_days['covidDays']>0)
            $this->a_days['msg'].= " + covid ".$this->a_days['covidDays'];
        if($this->a_params['DisputeDays']>0 || $this->a_days['covidDays'])
            $this->a_days['msg'].= " )";
    }

    public function covidPeriod(){

        $covidStartDate = "2020-02-23";
        $covidStopDate = "2020-04-15";
        $limitDate = date('Y-m-d', strtotime($this->a_params['NotificationDate']. ' + '.($this->a_params['RangeDayMax'] + $this->a_params['DisputeDays']).' days'));
        if($this->a_params['NotificationDate']<=$covidStopDate && $limitDate>=$covidStartDate){
            if($this->a_params['NotificationDate']>=$covidStartDate)
                $startDate = $this->a_params['NotificationDate'];
            else
                $startDate = $covidStartDate;
            if($limitDate<=$covidStopDate)
                $endDate = $limitDate;
            else
                $endDate = $covidStopDate;
            $this->a_days['covidDays'] = (int)DateDiff("D", $startDate, $endDate) + 1;
        }
    }

    public function getMsg(){
        switch($this->type){
            case "126Bis":
                $this->get126BisMsg();
                break;
        }
    }

    public function get126BisMsg(){
        if($this->a_elaborate['start']===true)
            $this->a_elaborate['msg'] = "POSITIVO: ";
        else
            $this->a_elaborate['msg'] = "NEGATIVO: ";

        if($this->a_missingData['check'])
            $this->a_elaborate['msg'].= $this->a_missingData['msg'];
        else
            $this->a_elaborate['msg'].= "Comunicazione dati corretta";

        if($this->a_daysLimitation['msg']!=null)
            $this->a_elaborate['msg'].= " - ".$this->a_daysLimitation['msg']."\n".$this->a_days['msg'];

        if($this->a_params['DisputeCheck']===true || $this->a_params['DisputeCheck']===false)
            $this->a_elaborate['msg'].= "\n".$this->a_params['DisputeMsg'];

    }
}