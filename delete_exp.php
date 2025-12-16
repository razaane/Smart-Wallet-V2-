<?php
require_once('config.php');

if(isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->exec("SET @count = 0");
    $pdo->exec("UPDATE expenses SET id = (@count:=@count+1) ORDER BY id ASC");
    $pdo->exec("ALTER TABLE expenses AUTO_INCREMENT = 1");

    header("Location: affich_exp.php");
    exit;
} else {
    die("ID manquant !");
}
?>