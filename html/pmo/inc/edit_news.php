<?php include('header.php');?>
<?php
$id =  $_GET['news_id'];
include("controller_login.php");
include("document_functions.php");
checkLogin();
if(isset($_POST['submit'])){
    // $id =  $_GET['news_id'];
    $tipologia = $_POST['tipologia'];
    $soto_tipologia = $_POST['soto_tipologia'];
    $descrizione = htmlentities($_POST['descrizione'], ENT_QUOTES, "UTF-8");
    $classificazione = $_POST['classificazione'];
    $tipo_atto = $_POST['tipo_atto'];
    $ente_emitente = $_POST['ente_emitente'];
    $date = $_POST['date'];
    $number = htmlentities($_POST['number'], ENT_QUOTES, "UTF-8");
    $description = htmlentities($_POST['description'], ENT_QUOTES, "UTF-8");
    $query = "UPDATE News set Tipologia_Id = '$tipologia', Sotto_Tipologia_Id = '$soto_tipologia',
    Tipologia_Descrizione='$descrizione',
    ClassificazioneAtto_Id = '$classificazione',TippoAtto_Id = '$tipo_atto',
    EnteEmitente_Id = '$ente_emitente',News_Date = '$date',News_Number = '$number',
    Description_News= '$description' WHERE NewsId = '$id'";
 
    if($conn->query($query) === TRUE){
        
        header('Location: news_administration.php');  
    }else{
        echo "Problem: ".$conn->error;
    }
}

$result = $conn->query("SELECT Tipologia_Id FROM News WHERE NewsId = $id");
$get_tipologia_id = $result->fetch_assoc();
$tipo_id = $get_tipologia_id['Tipologia_Id'];
?>
    <div class="container">
        
        <h2>Edit News</h2>
        <p class ="" style="font-size:18px;color:green;"><?php  echo $answer;?></p>
        <hr>
        
        <?php 
            $news_id =  $_GET['news_id'];
            $sql = "SELECT * FROM News JOIN Tipologia on News.Tipologia_Id = Tipologia.Id JOIN sottoTipologia on News.Sotto_Tipologia_Id = sottoTipologia.Id JOIN tipoAtto on News.TippoAtto_Id = tipoAtto.Id JOIN enteEmittente on News.EnteEmitente_Id = enteEmittente.Id 
            JOIN clasificazioneAtto ON News.ClassificazioneAtto_Id = clasificazioneAtto.Id WHERE NewsId = '$news_id'";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()) {
                ?>
                
                <form method="post" action="" id="load">
                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Tipologia</label>
                        <div class="col-sm-8">
                        <div class="answer"></div>
                        <select class="custom-select form-control" name="tipologia" id="tipologia">
                            <?php 
                                $sql_t = "SELECT * from Tipologia";
                                $result_t = $conn->query($sql_t);
                                while($row_t = $result_t->fetch_assoc()) {
                                    if($row['Description_Tipologia']==$row_t['Description_Tipologia']){
                                        ?>
                                        <option value="<?php echo $row['Tipologia_Id'];?>" selected><?php echo $row['Description_Tipologia']; ?></option>
                                        <?php
                                    }else{
                                        ?>
                                        <option value="<?php echo $row_t['Id'];?>"><?php echo $row_t['Description_Tipologia'];?></option>

                                        <?php
                                    }
                            
                                }
                            ?>
                        </select>
                        </div>
                    </div>
                    <div class="form-group row">
                    
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Sotto Tipologia</label>
                        <div class="col-sm-8">
                        <select class="custom-select form-control" name="soto_tipologia" id="sototipologia">
                        <?php 
                                $sql_s = "SELECT * from sottoTipologia WHERE tipologia_id = $tipo_id";
                                $result_s = $conn->query($sql_s);
                                while($row_s = $result_s->fetch_assoc()) {
                                    if($row['Sotto_Tipologia_Id'] == $row_s['Id']){
                                        ?>
                                        <option value="<?php echo $row['Sotto_Tipologia_Id'];?>" selected><?php echo $row['Description_SottoTipologia'];?></option>
                                        <?php
                                    }else{
                                    ?>
                                        <option value="<?php echo $row_s['Id'];?>"><?php echo $row_s['Description_SottoTipologia'];?></option>
                                    <?php
                                    }
                                   
                                }
                            ?>
                            
                        </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Tipologia Descrizione</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" name="descrizione" value="<?php echo $row['Tipologia_Descrizione']; ?>" required>
                        </div>
                    
                    </div>
                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Clasificazione Atto</label>
                        <div class="col-sm-8">
                        <select class="custom-select form-control" name="classificazione">
                        <?php 
                                $sql_c = "SELECT * from clasificazioneAtto";
                                $result_c = $conn->query($sql_c);
                                while($row_c = $result_c->fetch_assoc()) {
                                    if($row['Description_Clasificazione'] == $row_c['Description_Clasificazione']){
                                        ?>
                                        <option value="<?php echo $row['ClassificazioneAtto_Id'];?>" selected><?php echo $row['Description_Clasificazione']; ?></option>

                                        <?php
                                    }else{
                                        ?>
                                        <option value="<?php echo $row_c['Id'];?>"><?php echo $row_c['Description_Clasificazione']; ?></option>
                                        <?php
                                    }
                            
                                }
                            ?>
                        </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Tipo Atto</label>
                        <div class="col-sm-8">
                        <select class="custom-select form-control" name="tipo_atto">
                        <?php 
                                $sql_t = "SELECT * from tipoAtto";
                                $result_t = $conn->query($sql_t);
                                while($row_t = $result_t->fetch_assoc()) {
                                    if($row['TippoAtto_Id'] == $row_t['Id']){
                                        ?>
                                        <option value="<?php echo $row['TippoAtto_Id'];?>" selected><?php echo $row['Description_TipoAtto']; ?></option>

                                        <?php
                                    }else{
                                        ?>
                                        <option value="<?php echo $row_t['Id'];?>"><?php echo $row_['Description_TipoAtto']; ?></option>
                                        <?php
                                    }
                            
                                }
                            ?>
                        </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Ente Emittente</label>
                        <div class="col-sm-8">
                        <select class="custom-select form-control" name="ente_emitente">
                        <?php 
                                $sql_e = "SELECT * from enteEmittente";
                                $result_e = $conn->query($sql_e);
                                while($row_e = $result_e->fetch_assoc()) {
                                    if($row['EnteEmittente_Id'] == $row_e['Id']){
                                        ?>
                                        <option value="<?php echo $row['EnteEmittente_Id'];?>" selected><?php echo $row['Description_EnteEmittente'];?></option>
                                        <?php
                                    }else{
                                        ?>
                                        <option value="<?php echo $row_e['Id'];?>"><?php echo $row['Description_EnteEmittente'];?></option>
                                        <?php
                                    }
                                }
                            ?>
                        </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Date</label>
                        <div class="col-sm-8">
                        <input type="date"  class="form-control form-control-sm" name="date" value="<?php echo $row['News_Date'];?>">
                                                
                        </div>
                    
                    </div>
                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Number</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" name="number" value="<?php echo $row['News_Number'];?>" required>
                        </div>
                    
                    </div>
                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Description</label>
                        <div class="col-sm-8">
                        <textarea class="form-control" rows="10" name="description" required><?php echo $row['Description_News'];?></textarea>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-success" name="submit" value="Edit News" id="submit">

                </form>
                <?php
            }
            
        ?>
             
    </div>   
    <script type="text/javascript">
        $(document).ready(function(){

            $('#tipologia').on('click',function(){

                var tipologia_id = $('#tipologia').val();
                
                $.ajax({
                    type: 'POST',
                    url: 'get_sototipologia.php',
                    data: {"tipologia_id": tipologia_id},
                    success: function (data) {
                        $('#sototipologia').empty();
                        var answer = JSON.parse(data).result
                        if(answer.length == 0){
                            $('#submit').hide();
                            $('.answer').html('<p>Please select other because this not have values!</p>');
                        }else{
                            $('#submit').show();
                            $('.answer').html('');
                            for(var i=0; i<answer.length; i++){
                                $('#sototipologia').append('<option value = " '+answer[i].Id +' " class="form-control" required>'+answer[i].Description_SottoTipologia+'</option>');
                            }
                        }
                        
                        

                    },
                    error: function () {
                    console.log('error occured');
                    }
                }); 
                
            });
        });


</script>
</body>
</html>
