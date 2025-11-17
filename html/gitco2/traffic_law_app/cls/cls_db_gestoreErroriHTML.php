<?php
require_once(CLS.'/cls_db_gestoreErrori.php');

class cls_db_gestoreErroriHTML implements cls_db_gestoreErrori{
    public function esci(){
        die;
    }
    
    public function ErrorAlert(String $msgType, String $msgText){
        // $msgType success(verde), info(azzurro), warning(giallo), danger(rosso)
        echo "<div class='alert alert-".$msgType."'>".$msgText."</div>";
    }
}