<?php
require_once('config.php');

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM incomes WHERE id = ?");
$stmt->execute([$id]);

$rs = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rs) {
    die("Revenu introuvable !");
}
?>



