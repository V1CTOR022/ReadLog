<?php
require 'database.php';

$id = $_POST['id'] ?? '';
if (!ctype_digit((string)$id)) {
    header('Location: index.php');
    exit;
}

$status        = $_POST['status'] ?? 'quero_ler';
$paginas_lidas = (int)($_POST['paginas_lidas'] ?? 0);
$paginas_total = (int)($_POST['paginas_total'] ?? 0);
$avaliacao     = (float)($_POST['avaliacao'] ?? 0);

$validStatuses = ['lendo', 'lido', 'pausado', 'quero_ler'];
if (!in_array($status, $validStatuses)) $status = 'quero_ler';
if ($avaliacao < 0 || $avaliacao > 5) $avaliacao = 0;

$stmt = $pdo->prepare('UPDATE livro SET status=?, paginas_lidas=?, paginas_total=?, avaliacao=? WHERE id=?');
$stmt->execute([$status, $paginas_lidas, $paginas_total, $avaliacao, (int)$id]);

header('Location: index.php');
exit;
?>
