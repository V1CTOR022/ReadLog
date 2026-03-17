<?php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    
    if (!empty($id) && is_numeric($id)) {
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("UPDATE tarefas SET concluida = 1 WHERE id = ?");
            $stmt->execute([$id]);
            
            header('Location: index.php');
            exit();
            
        } catch (PDOException $e) {
            header('Location: index.php?error=1');
            exit();
        }
    } else {
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>