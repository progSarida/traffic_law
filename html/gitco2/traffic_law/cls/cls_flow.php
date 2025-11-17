<?php

class cls_flow{

    private $db;
    private $a_type = array(
        "N"=>"- NAZIONALE",
        "F"=>"- ESTERO"
    );



    public $a_status;
    public $a_deadlines;
    public $a_printTypes;
    public $CityId;

    public function __construct($cityId=null){
        $this->db = new CLS_DB();
        $this->a_printTypes = $this->setPrintTypes();
        $this->a_status = $this->setStatus();
        $this->a_deadlines = $this->setDeadlines();
        $this->CityId = $cityId;
    }

    private function setStatus(){
        $queryStatus = "SELECT * FROM FlowStatus";
        $a_queryStatus = $this->db->getResults($this->db->ExecuteQuery($queryStatus));
        for($i=0;$i<count($a_queryStatus);$i++){
            $a_status[$a_queryStatus[$i]['Id']] = $a_queryStatus[$i];
        }
        return $a_status;
    }

    private function setDeadlines(){
        $queryDeadlines = "SELECT * FROM FlowDeadlines";
        $a_deadlines = array();
        $a_queryDeadlines = $this->db->getResults($this->db->ExecuteQuery($queryDeadlines));
        for($i=0;$i<count($a_queryDeadlines);$i++){
            $a_queryDeadlines[$i]['Label_N'] = str_replace("[Days]",$a_queryDeadlines[$i]['Days_N']." ",$a_queryDeadlines[$i]['Label']);
            $a_queryDeadlines[$i]['Label_F'] = str_replace("[Days]",$a_queryDeadlines[$i]['Days_F']." ",$a_queryDeadlines[$i]['Label']);
            $a_deadlines[$a_queryDeadlines[$i]['PrintTypeId']][] = $a_queryDeadlines[$i];
        }

        return $a_deadlines;

    }
    
    public function getDeadlinesDays($printTypeId, $nationality){
        $a_return = array();
        $str_daysNationality = $nationality == 'N' ? 'Days_N' : 'Days_F';
        foreach($this->a_deadlines[$printTypeId] as $a_deadline){
            switch($a_deadline['StartStatusId']){
                case 1:     $a_return['UPLOAD'] = $a_deadline[$str_daysNationality];          break;
                case 2:     $a_return['PROCESS'] = $a_deadline[$str_daysNationality];          break;
                case 3:     $a_return['PAYMENT'] = $a_deadline[$str_daysNationality];          break;
                case 4:     $a_return['SHIPMENT'] = $a_deadline[$str_daysNationality];          break;
            }
        }
        return $a_return;
    }

    private function setPrintTypes(){
        $a_temp = $this->db->getResults($this->db->ExecuteQuery("SELECT * FROM Print_Type WHERE Disabled=0 AND Id!=6"));
        foreach ($a_temp as $a_print){
            $a_printTypes[$a_print['Id']] = $a_print;
        }

        return $a_printTypes;
    }

    public function getFlowsNumber(){
        foreach($this->a_type as $type=>$typeLabel){
            foreach ($this->a_printTypes as $printTypeId=>$a_printType){
                foreach($this->a_deadlines[$printTypeId] as $a_deadline) {
                    $a_flow[$type][$printTypeId][] = $this->getFlowNumberArray($type, $a_deadline, $printTypeId);
                }

            }
        }
        return $a_flow;
    }


    /**
     * @param $type "N" for National or "F" for Foreign
     * @param $a_deadline
     * @return null
     */
    private function getFlowNumberArray($type, $a_deadline, $printType = null){
        $query = "";
        if($type=="N")
            $query.= "SELECT F.CityId, C.ManagerCity, COUNT(F.Id) TOT FROM `Flow` F JOIN `Customer` C ON F.CityId= C.CityId WHERE F.FileName LIKE '%_ita_%'";
        else if($type=="F")
            $query.= "SELECT F.CityId, C.ManagerCity, COUNT(F.Id) TOT FROM `Flow` F JOIN `Customer` C ON F.CityId= C.CityId WHERE F.FileName NOT LIKE '%_ita_%'";
        else
            return null;

        if($this->CityId!=null)
            $query.= " AND F.CityId='".$this->CityId."'";

        if($printType>0)
            $query.= " AND F.PrintTypeId=".$printType;

        $query.= " AND F.".$this->a_status[(int)$a_deadline['StartStatusId']]['FlowField']." is not null";
        $query.= " AND F.".$this->a_status[(int)$a_deadline['EndStatusId']]['FlowField']." is null";
        $query.= " AND DATEDIFF(CURDATE(),F.".$this->a_status[(int)$a_deadline['StartStatusId']]['FlowField'].")>".$a_deadline['Days_'.$type];
        $query.= " GROUP BY F.CityId, F.PrintTypeId, C.ManagerCity";



        return $this->db->getResults($this->db->ExecuteQuery($query));
    }

    function htmlFlowNumber($a_flow, $sel_printType = null){
        $str = '';
        foreach($a_flow as $type=>$a_flowPrint){

//            $str .= '<div class="clean_row HSpace4"></div><div class="table_label_H col-sm-12" style="height:3rem;font-size: 1.6rem;line-height: 3rem;"><b>'.$this->a_type[$type].'</b></div>
//                <div class="clean_row HSpace4"></div>';

            foreach($a_flowPrint as $printTypeId=>$a_flowDeadlines){

                if($sel_printType>0 && $printTypeId!=$sel_printType)
                    continue;

                $checkPrintTypeTitle = 0;
                foreach($a_flowDeadlines as $deadlineId=>$a_flowDetails){

                    if($checkPrintTypeTitle==0 && count($a_flowDetails)>0){
                        $str.='<div class="table_label_H col-sm-12" style="height:3rem;font-size: 1.4rem;line-height: 3rem;">';
                        $str.= '<b>'.strtoupper($this->a_printTypes[$printTypeId]['Name']).' '.$this->a_type[$type].'</b></div><div class="clean_row HSpace16"></div>';
                        $checkPrintTypeTitle=1;
                    }

                    if(count($a_flowDetails)>0) {
                        if($this->CityId==null)
                            $str .= '<div class="clean_row HSpace4"></div>';
                        $str .= '<div class="col-sm-2 BoxRowLabel" style="height:3rem;font-size: 1.4rem;line-height: 3rem;">' . $this->a_deadlines[$printTypeId][$deadlineId]['Label_'.$type] . '</div>';
                        for ($i = 0; $i < count($a_flowDetails); $i++) {
                            if ($i % 5 == 0 && $i != 0 && $this->CityId==null){
                                $str .= '
                                    <div class="clean_row HSpace4"></div>
                                    <div class="col-sm-2"></div>
                                ';
                            }
                            $col = 1;
                            if($this->CityId==null)
                                $col=2;

                            $str.= '<div class="col-sm-'.$col.' table_caption_H" style="height:3rem;font-size: 1.4rem;line-height: 3rem;"><span style="color: red;">';
                            if($this->CityId==null)
                                $str.= '<span class="detail_flow" id_city="'.$a_flowDetails[$i]['CityId'].'" id_print_type="'.$printTypeId.'" id_type="'. $type .'" start_status="'. $this->a_deadlines[$printTypeId][$deadlineId]['StartStatusId'] .'" end_status="'. $this->a_deadlines[$printTypeId][$deadlineId]['EndStatusId'] .'" day_n="'. $this->a_deadlines[$printTypeId][$deadlineId]['Days_'.$type] .'" >'. $a_flowDetails[$i]['CityId']." ".$a_flowDetails[$i]['ManagerCity']." ";
                            $str.= $a_flowDetails[$i]['TOT'] .'</span></span></div>';


                        }
                        if($this->CityId==null)
                            $str.='<div class="clean_row HSpace4"></div>';
                    }


                }
                $str.='<div class="clean_row HSpace4"></div>';
            }

        }

        return $str;
    }

}