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

// Delete Income
if (isset($_GET['delete_inc'])) {
    $id = $_GET['delete_inc'];
    
    // Verify ownership before delete
    $stmt = $pdo->prepare("DELETE FROM incomes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    header('Location: affich_inc.php?success=income_deleted');
    exit();
}

// ============ EXPENSE OPERATIONS ============

// Add Expense
if (isset($_POST['save_exp'])) {
    $date = $_POST['date_exp'];
    $amount = $_POST['amount_exp'];
    $description = $_POST['descreption_exp'];
    
    $stmt = $pdo->prepare("INSERT INTO expenses (user_id, montant, descreption, la_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $amount, $description, $date]);
    
    header('Location: index.php?success=expense_added');
    exit();
}

// Update Expense
if (isset($_POST['update_exp'])) {
    $id = $_POST['id'];
    $date = $_POST['date_exp'];
    $amount = $_POST['amount_exp'];
    $description = $_POST['descreption_exp'];
    
    // Verify ownership before update
    $stmt = $pdo->prepare("UPDATE expenses SET montant = ?, descreption = ?, la_date = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$amount, $description, $date, $id, $userId]);
    
    header('Location: affich_exp.php?success=expense_updated');
    exit();
}

// Delete Expense
if (isset($_GET['delete_exp'])) {
    $id = $_GET['delete_exp'];
    
    // Verify ownership before delete
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    header('Location: affich_exp.php?success=expense_deleted');
    exit();
}

// ============ FETCH USER DATA ============

// Get user's incomes
$stmt = $pdo->prepare("SELECT * FROM incomes WHERE user_id = ? ORDER BY la_date DESC");
$stmt->execute([$userId]);
$result_incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's expenses
$stmt = $pdo->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY la_date DESC");
$stmt->execute([$userId]);
$result_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent transactions (combined)
$stmt = $pdo->prepare("
    SELECT 'income' as type, id, montant, descreption, la_date 
    FROM incomes 
    WHERE user_id = ? 
    UNION ALL 
    SELECT 'expense' as type, id, montant, descreption, la_date 
    FROM expenses 
    WHERE user_id = ? 
    ORDER BY la_date DESC 
    LIMIT 10
");
$stmt->execute([$userId, $userId]);
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

