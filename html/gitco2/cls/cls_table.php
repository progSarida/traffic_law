<?php
class  CLS_TABLE{

    public $aField;
    public $idField;
    public $filter = null;
    public $order = null;
    public $str_out;
    public $page;
    private $table;
    public $str_CurrentPage;

    public $aButton = true;
    public $vButton = true;
    public $dButton = true;
    public $uButton = true;

    public function __construct($table){
        $this->table = $table;
    }

    function Create_Page(){

        $aLan = unserialize(IMG_LANGUAGE);

        $rs = new CLS_DB();

        $this->str_out = '
            <div class="container-fluid">
              <div class="row-fluid">
                <div class="col-sm-12">
                ';
                

        foreach ($this->aField as $F):
            //if(!$F['hidden']) $this->str_out .= '<div class="table_label_H p_w'.$F['width'].'">' . $F['label'].'</div>';
            if(!$F['hidden'] && $F['width']!="0") $this->str_out .= '<div class="table_label_H col-sm-'.$F['width'].' ">' . $F['label'].'</div>';

        endforeach;
        $this->str_out .='<div class="table_add_button col-sm-1 right">';
        if($this->aButton) $this->str_out .='<span class="glyphicon glyphicon-plus-sign add_button" style="height:2.8rem;margin-right:0.3rem; line-height:2.6rem;"></span>';
        $this->str_out .='</div>
        <div class="clean_row HSpace4"></div>';

        $strJSon = "\"aField\":" . json_encode($this->aField);
        $pagelimit = $this->page * PAGE_NUMBER;
        
        $table_rows = $rs->Select($this->table, $this->filter, $this->order, $pagelimit . ',' . PAGE_NUMBER);
        $RowNumber = mysqli_num_rows($table_rows);
        

        if ($RowNumber == 0) {
            $this->str_out .= 'Nessun record presente';
        } else {
            while ($table_row = mysqli_fetch_array($table_rows)) {

                foreach ($this->aField as $F) :
                    $field = (empty($table_row[$F['field']]) || strlen(trim($table_row[$F['field']]))==0) ? "&nbsp;" : $table_row[$F['field']];
                    if (!$F['hidden'] && $F['width']!="0"){

                        $this->str_out .= '<div class="table_caption_H col-sm-'.$F['width'].'">' . $field . '</div>';

                        //else $strSpan = '<span><img src="' .IMG.'/'.$aLan[$field] . '" style="width:16px" /></span>';
                    }

                endforeach;

                $this->str_out .= '<div class="table_caption_button col-sm-1">';
                if ($this->vButton) $this->str_out .= '<a hhref=""><span class="glyphicon glyphicon-eye-open"><input type="hidden" id="' . $this->idField . '" value="' . $table_row[$this->idField] . '" /> </span></a>&nbsp;';
                if ($this->uButton) $this->str_out .= '<a hhref=""><span class="glyphicon glyphicon-pencil"><input type="hidden" id="' . $this->idField . '" value="' . $table_row[$this->idField] . '" /> </span></a>&nbsp;';
                if ($this->dButton) $this->str_out .= '<a hhref=""><span class="glyphicon glyphicon-remove-sign"><input type="hidden" id="' . $this->idField . '" value="' . $table_row[$this->idField] . '" /> </span></a>';
                $this->str_out .=
                    '</div>
                        <div class="clean_row HSpace4"></div>';
            }

            $table_users_number = $rs->Select($this->table, $this->filter, $this->order);
            $UserNumberTotal = mysqli_num_rows($table_users_number);

            $this->str_out .= CreatePagination(PAGE_NUMBER, $UserNumberTotal, $this->page, $this->LinkPage,"");
            $this->str_out .= '<div>
    </div>';
        }



        $this->str_out .= ' <div class="overlay" id="overlay" style="display:none;"></div>';




//**********************************
//Add button
//**********************************

        if($this->aButton) $this->str_out .= '<div id="BoxInsert" style="margin-top: 40%; height: 23rem"></div>';


//**********************************
//View button
//**********************************

        if($this->vButton) $this->str_out .= '<div id="BoxView"></div>';



//**********************************
//Update button
//**********************************

        if($this->uButton) $this->str_out .= '<div id="BoxUpdate" class="margin-top:40%"></div>';



//**********************************
//Delete button
//**********************************

        if($this->dButton) $this->str_out .= '<div id="BoxDelete"></div>';


        $this->str_out .= '
        <script type="text/javascript">
       $(document).ready(function () {

        $("#overlay").click(function(){
            $(this).fadeOut("fast");';
        if($this->aButton) $this->str_out .= '$("#BoxInsert").hide();';
        if($this->uButton) $this->str_out .= '$("#BoxUpdate").hide();';
        if($this->vButton) $this->str_out .= '$("#BoxView").hide();';
        if($this->dButton) $this->str_out .= '$("#BoxDelete").hide();';

        $this->str_out .= '});';

        if($this->aButton){
            $this->str_out .= '
        $(".add_button").click(function(){
            $("#overlay").fadeIn("fast");
            $("#BoxInsert").fadeIn("slow");

            var post_data = {"action":"ins",'.$strJSon.',"table":"'.$this->table.'"};

            $.post("ajax/table.php", post_data, function(data) {
                $("#BoxInsert").html(data);

            }).fail(function(err) {
                alert("NON DA CHIAMATA :"+err.statusText);
            });

        });';
        }


        if($this->vButton){
            $this->str_out .= '
        $(".glyphicon-eye-open").click(function(){
            $("#overlay").fadeIn("fast");
            $("#BoxView").fadeIn("slow");

            var Id = $(this).find("#Id").val();
            var post_data = {"action":"viw","Id":Id,'.$strJSon.',"table":"'.$this->table.'"};

            $.post("ajax/table.php", post_data, function(data) {
                $("#BoxView").html(data);

            }).fail(function(err) {
                alert("NON DA CHIAMATA :"+err.statusText);
            });

        });';
        }
        if($this->uButton){
            $this->str_out .= '
        $(".glyphicon-pencil").click(function(){
            $("#overlay").fadeIn("fast");
            $("#BoxUpdate").fadeIn("slow");

            var Id = $(this).find("#Id").val();

            var post_data = {"action":"upd","Id":Id,'.$strJSon.',"table":"'.$this->table.'"};

            $.post("ajax/table.php", post_data, function(data) {
                $("#BoxUpdate").html(data);

            }).fail(function(err) {
                alert("NON DA CHIAMATA :"+err.statusText);
            });

        });';
        }
        if($this->dButton){
            $this->str_out .= '

        $(".glyphicon-remove-sign").click(function(){
            $("#overlay").fadeIn("fast");
            $("#BoxDelete").fadeIn("slow");

            var Id = $(this).find("#Id").val();
            var post_data = {"action":"del","Id":Id,'.$strJSon.',"table":"'.$this->table.'"};

            $.post("ajax/table.php", post_data, function(data) {
                $("#BoxDelete").html(data);

            }).fail(function(err) {
                alert("NON DA CHIAMATA :"+err.statusText);
            });

        });';
        }
        $this->str_out .= '
        });
</script>';

    }
}