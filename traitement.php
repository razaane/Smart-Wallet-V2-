<?php
include "config.php";
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST["save_exp"])){
        $date=$_POST['date_exp'];
        $montant= $_POST['amount_exp'];
        $descreption =$_POST['descreption_exp'];
        $stmt=$pdo->prepare(" INSERT INTO expenses (la_date,montant,descreption) values(?,?,?)");
        $stmt->execute([$date,$montant,$descreption]);

    }
    if(isset($_POST["save_inc"])){
        $date=$_POST['date_inc'];
        $montant=$_POST['amount_inc'];
        $descreption=$_POST['descreption_inc'];
        $stmt=$pdo->prepare("INSERT INTO incomes (la_date,montant,descreption) values(?,?,?)");
        $stmt->execute([$date,$montant,$descreption]);
    }
    header("location:index.php");
    exit;
}



