<?php 
$host = "localhost";
$user = "root";
$pass = "";
$db = "smart_wallet";

try{
$pdo = new PDO("mysql:host=$host;dbname=$db",$user,$pass);
}catch(PDOException $erreur){
    die("Erreur de connexion: " . $erreur->getMessage());
}
$result_incomes = $pdo->query("SELECT * FROM incomes");
$result_expenses = $pdo->query("SELECT * FROM expenses");


