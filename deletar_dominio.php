<?php
require_once "functions.php";
if(isset($_POST['subject'])){
    foreach($_POST['subject'] as $selected)
    {
        $query = "DELETE FROM domains WHERE domain = '$selected'";
        $executar = mysqli_query ($connect, $query);
        header("location: index.php");

    }
}
?>