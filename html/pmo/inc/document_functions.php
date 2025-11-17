<?php 
    $MAIN_CATEGORIES = array("OMOLOGAZIONI","COLLAUDI"/*,"OMOLOGAZIONI_SECONDO"*/,"DOCUMENTAZIONE");
    const UPLOAD_FOLDER = 'uploads/';
    include 'connection.php';
    
    if(isset($_POST['delete'])){
        $document_id = $_POST['document_id'];
        delete_documents($document_id,$conn);

    }
    //
    if(isset($_POST['delete_news'])){
        $news_id = $_POST['news_id'];
        delete_news($news_id,$conn);
    }
    //
    if(isset($_POST['delete_tipologia'])){
        $id = $_POST['tipologia_id'];
        delete_tipologia($id,$conn);
    }
    //
    if(isset($_POST['delete_sototipologia'])){
        $id = $_POST['sototipologia_id'];
        delete_sototipologia($id,$conn);
    }
    ///
    if(isset($_POST['delete_tipoatto'])){
        $id = $_POST['tipoatto_id'];
        delete_tipoatto($id,$conn);
    }
    //
    if(isset($_POST['delete_emitente'])){
        $id = $_POST['emitente_id'];
        delete_emitente($id,$conn);
    }
    //
    if(isset($_POST['delete_class'])){
        $id = $_POST['class_id'];
        delete_class($id,$conn);
    }

    if(isset($_POST['delete_category'])){
        $id = $_POST['category_id'];
        delete_category($id,$conn);
    }


    function checkLogin(){
       
     
        session_start();
       
        if(!isset($_SESSION['user'])){
           
            header('Location: ../login.php');
            
        }
        return true;
    }
    //delete
    function delete_documents($document_id,$conn){
        $select = "SELECT * FROM Documents WHERE ID= '$document_id'";
        if ($result=mysqli_query($conn,$select))
        {
        while ($obj=mysqli_fetch_object($result))
        {
        $filename = $obj->hashedNameDocument;
        }
       
        }
        $sql = "DELETE from Documents WHERE ID = '$document_id'";
        unlink(UPLOAD_FOLDER.$filename);
        if ($conn->query($sql) === TRUE) {
            echo "Record eliminato correttamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    function delete_news($news_id,$conn){
        $sql = "DELETE from News WHERE NewsId = '$news_id'";
        if ($conn->query($sql) === TRUE) {
            echo "Record eliminato correttamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    ///
    function delete_tipologia($id,$conn){
        $sql = "DELETE from Tipologia WHERE Id = '$id'";
        if ($conn->query($sql) === TRUE) {
            echo "Record eliminato correttamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    //
    function delete_sototipologia($id,$conn){
        $sql = "DELETE from sottoTipologia WHERE Id = '$id'";
        if ($conn->query($sql) === TRUE) {
            echo "Record eliminato correttamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    //
    function delete_tipoatto($id,$conn){
        $sql = "DELETE from tipoAtto WHERE Id = '$id'";
        if ($conn->query($sql) === TRUE) {
            echo "Record eliminato correttamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    function delete_emitente($id,$conn){
        $sql = "DELETE from enteEmittente WHERE Id = '$id'";
        if ($conn->query($sql) === TRUE) {
            echo "Record eliminato correttamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    function delete_class($id,$conn){
        $sql = "DELETE from clasificazioneAtto WHERE Id = '$id'";
        if ($conn->query($sql) === TRUE) {
            echo "Record eliminato correttamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    function delete_category($id,$conn){
        $sql = "DELETE from Categories WHERE Id = '$id'";
        if ($conn->query($sql) === TRUE) {
            echo "Record eliminato correttamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
?>