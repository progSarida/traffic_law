
<?php
    
    include("controller_login.php");
    include("document_functions.php");
    checkLogin();
    if(isset($_POST['add_tipoatto'])){

        $t_description = htmlentities($_POST['t_description'], ENT_QUOTES, "UTF-8");
        

        $sql = "INSERT INTO `tipoAtto` VALUES (NULL, '$t_description');";
        if($conn->query($sql) === TRUE){
            $answer =  "Description added succesfully!";

        }else {
            $answer = $conn->error;

        }
        
    
        }else{
        $answer = "";
    }
    
    ?>
    <?php include('header.php');?>
    <div class="container">
       
        <div class="col-lg-10">
        <h5 style="color:green;"><?php echo $answer;?></h5>
                <table class="table table-striped">
                <caption><h3>Tipo Atto</h3></caption>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Delete</th>
                    </tr>
                    <tbody id="categories_data">
                <?php
                $query = "SELECT * from tipoAtto";
                $rs= mysqli_query($conn, $query);
                if(mysqli_num_rows($rs) != 0){
                while($row = mysqli_fetch_array($rs)){
                    ?>
                    <tr id="remove<?php echo $row['Id'];?>">
                        <td><?php echo $row['Id'];?></td>
                        <td data-name="name_tipoatto" class="name" data-type="text" data-pk="<?php echo $row['Id'];?>" style="color:blue;"><?php echo $row['Description_TipoAtto'];?></td>
                        <td><button class="btn btn-danger btn-sm delete_tipoatto" id="<?php  echo $row['Id'];?>" value="delete_tipoatto"> Delete</button>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
                </table>
                <?php
                }else{
            
                    echo '<tr><td colspan="4">0 Results!</td></tr>';
                    echo '</tbody></table>';
                }
                ?>
                
        </div>
        <div class="col-lg-2">
        <br>
        <button type="button" class="btn btn-info btn-sm pull-right form-control" data-toggle="modal" data-target="#myModal">Add New</button><br>

        </div>
        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add New</h4>
                    </div>
                    <div class="modal-body">
                        <h4>Description!</h4>
                        <form method="post" action="tipoatto.php">
                            <input type="text" class="form-control" name="t_description" required><br>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" value="Add New" name="add_tipoatto" class="btn btn-primary">
                        </form>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>
    </div>    
   <script>
        $(document).ready(function(){
            
            $('.delete_tipoatto').on('click',function () {
                if (!confirm("Do you want to delete")){
                return false;
                }
                var button = $('.delete_tipoatto').val();
                var tipoatto_id = $(this).attr('id');
                var myData = {"delete_tipoatto":button,"tipoatto_id":tipoatto_id};
                $.ajax({
                    url: 'document_functions.php',
                    type: 'POST',
                    data: myData,
                    success: function(data){
                        $('#remove'+tipoatto_id).fadeOut();
                    },

                });
            });
            $('#categories_data').editable({
                container: 'body',
                selector: 'td.name',
                url: "editnewscategories.php",
                title: 'Description',
                type: "POST",
                validate: function(value){
                    if($.trim(value) == ''){
                        return 'This field is required';
                    }
                }
            });   
        });
   </script>
</body>
</html>
