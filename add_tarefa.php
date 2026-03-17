<?php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao']);
    $detalhes = trim($_POST['detalhes']);
    $data_vencimento = $_POST['data_vencimento'];
    
    if (!empty($descricao) && !empty($data_vencimento)) {
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("INSERT INTO tarefas (descricao, detalhes, data_vencimento) VALUES (?, ?, ?)");
            $stmt->execute([$descricao, $detalhes, $data_vencimento]);
            
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