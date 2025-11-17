
<?php
    
    include("controller_login.php");
    include("document_functions.php");
    checkLogin();
    if(isset($_POST['submit'])){

        $tipologia = $_POST['tipologia'];
        $soto_tipologia = $_POST['soto_tipologia'];
        if($tipologia == ""){
            $answer = '<p style="color:red;font-size:18px;">Please Tipologia can not be empty!</p>';
        }else{
            $descrizione = htmlentities($_POST['descrizione'], ENT_QUOTES, "UTF-8");
        $classificazione = $_POST['classificazione'];
        $tipo_atto = $_POST['tipo_atto'];
        $ente_emitente = $_POST['ente_emitente'];
        $date = $_POST['date'];
        $number = htmlentities($_POST['number'], ENT_QUOTES, "UTF-8");
        $description = htmlentities($_POST['description'], ENT_QUOTES, "UTF-8");

        $sql = "INSERT INTO `News` VALUES 
        (NULL, '$tipologia', '$soto_tipologia', '$descrizione', '$classificazione', '$tipo_atto', '$ente_emitente', '$date', '$number', '$description');";
        if($conn->query($sql) === TRUE){
            $answer =  "News added succesfully!";

        }else {
            $answer = $conn->error;

        }
        }
        
        
    
    }

    ?>
    <?php include('header.php');?>
    <div class="container-fluid">
    <button type="button" class="btn btn-info btn-sm pull-right" data-toggle="modal" data-target="#myModal">Add News</button><br><hr>
        <p style="font-size:18px;color:green;"><?php echo $answer;?></p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">News Id</th>
                    <th scope="col">Tipologia</th>
                    <th scope="col">Soto Tipologia</th>
                    <th scope="col">Tipologia Descrizione</th>
                    <th scope="col">Classificazione Atto</th>
                    <th scope="col">Tipo Atto</th>
                    <th scope="col">EnteEmitente</th>
                    <th scope="col">Date</th>
                    <th scope="col">Number</th>
                    <th scope="col" >Description</th>
                    <th scope="col">Edit</th>
                    <th scope="col">Delete</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $sql = "SELECT * FROM News JOIN Tipologia on News.Tipologia_Id = Tipologia.Id JOIN sottoTipologia on News.Sotto_Tipologia_Id = sottoTipologia.Id JOIN tipoAtto on News.TippoAtto_Id = tipoAtto.Id JOIN enteEmittente on News.EnteEmitente_Id = enteEmittente.Id JOIN clasificazioneAtto ON News.ClassificazioneAtto_Id = clasificazioneAtto.Id";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    // output data of each row
                    while($row = $result->fetch_assoc()) {
                        ?>
                        <tr id="remove<?php echo $row['NewsId'];?>">
                            <td><?php echo $row['NewsId'];?></td>
                            <td><?php echo $row['Description_Tipologia'];?></td>
                            <td><?php echo $row['Description_SottoTipologia'];?></td>
                            <td><?php echo $row['Tipologia_Descrizione'];?></td>
                            <td><?php echo $row['Description_Clasificazione'];?></td>
                            <td><?php echo $row['Description_TipoAtto'];?></td>
                            <td><?php echo $row['Description_EnteEmittente'];?></td>
                            <td><?php echo $row['News_Date'];?></td>
                            <td><?php echo $row['News_Number'];?></td>
                            <td width="400px;"><?php echo mb_strimwidth($row['Description_News'], 0, 100, "...");;?></td>
                            <td><a href="edit_news.php?news_id=<?php echo $row['NewsId']; ?>"><button class=" btn btn-success">Edit</button></a></td>
                            <td><button class="btn btn-danger btn-sm delete_news" id="<?php  echo $row['NewsId'];?>" value="delete_news"> Delete</button>
                        </tr>
                    </tbody>
                    
                        <?php
                    }
                    echo '</table>';
                }
            ?>

        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add News!</h4>
                    </div>
                <div class="modal-body modal-lg">
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Tipologia</label>
                            <div class="col-sm-8">
                            <div class="answer"></div>
                            <select id="tipologia" class="custom-select form-control" name="tipologia">
                            <option value="">Choose...</option>
                            <?php 
                                    $sql = "SELECT * from Tipologia";
                                    $result = $conn->query($sql);
                                    while($row = $result->fetch_assoc()) {
                                        ?>
                                            <option value="<?php echo $row['Id'];?>"><?php echo $row['Description_Tipologia'];?></option>
                                        <?php
                                
                                    }
                                ?>
                            </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Sotto Tipologia</label>
                            <div class="col-sm-8">
                            <select id="sototipologia" class="custom-select form-control" name="soto_tipologia">
                            </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Tipologia Descrizione</label>
                            <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" name="descrizione" required>
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Clasificazione Atto</label>
                            <div class="col-sm-8">
                            <select class="custom-select form-control" name="classificazione">
                            <?php 
                                    $sql = "SELECT * from clasificazioneAtto";
                                    $result = $conn->query($sql);
                                    while($row = $result->fetch_assoc()) {
                                        ?>
                                            <option value="<?php echo $row['Id'];?>"><?php echo $row['Description_Clasificazione'];?></option>
                                        <?php
                                
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
                                    $sql = "SELECT * from tipoAtto";
                                    $result = $conn->query($sql);
                                    while($row = $result->fetch_assoc()) {
                                        ?>
                                            <option value="<?php echo $row['Id'];?>"><?php echo $row['Description_TipoAtto'];?></option>
                                        <?php
                                
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
                                    $sql = "SELECT * from enteEmittente";
                                    $result = $conn->query($sql);
                                    while($row = $result->fetch_assoc()) {
                                        ?>
                                            <option value="<?php echo $row['Id'];?>"><?php echo $row['Description_EnteEmittente'];?></option>
                                        <?php
                                
                                    }
                                ?>
                            </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Date</label>
                            <div class="col-sm-8">
                            <input type="date"  class="form-control form-control-sm" name="date" required>
                                                     
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Number</label>
                            <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" name="number" required>
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Description</i></label>
                            <div class="col-sm-8">
                            <textarea class="form-control" rows="10" name="description" required></textarea>
                            </div>
                        </div>
                    
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-primary" name="submit" value="submit" id="submit">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                </form>
            </div>
        </div>
    </div>    
   <script>
        $(document).ready(function(){
            
            $('.delete_news').on('click',function () {
                if (!confirm("Do you want to delete")){
                return false;
                }
                var button = $('.delete_news').val();
                var news_id = $(this).attr('id');
                var myData = {"delete_news":button,"news_id":news_id};
                $.ajax({
                    url: 'document_functions.php',
                    type: 'POST',
                    data: myData,
                    success: function(data){
                        $('#remove'+news_id).fadeOut();
                    },

                });
            });
                
        });
   </script>
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
