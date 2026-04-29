<?php
require 'database.php';

$id = $_POST['id'] ?? '';

if (!ctype_digit((string)$id)) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM livro WHERE id = ?');
$stmt->execute([$id]);

header('Location: index.php');
exit;
?>