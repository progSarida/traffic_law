<?php
    $conn = mysqli_connect("localhost","pmo","jIUh_*_U36|>KFB","pmo");
    if (!$conn) {
        echo "Error: Unable to connect to Database." .mysqli_connect_errno().": ".mysqli_connect_error();
        exit;
    }
?>
