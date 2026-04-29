<?php
require 'database.php';

$nome_livro = trim($_POST['nome_livro'] ?? '');
$autor      = trim($_POST['autor'] ?? '');
$ano        = trim($_POST['ano'] ?? '');
$status     = $_POST['status'] ?? 'quero_ler';
$paginas_total = (int)($_POST['paginas_total'] ?? 0);
$avaliacao  = (float)($_POST['avaliacao'] ?? 0);

if ($nome_livro === '' || $autor === '') {
    header('Location: index.php');
    exit;
}

$validStatuses = ['lendo', 'lido', 'pausado', 'quero_ler'];
if (!in_array($status, $validStatuses)) $status = 'quero_ler';
if ($avaliacao < 0 || $avaliacao > 5) $avaliacao = 0;

$stmt = $pdo->prepare('INSERT INTO livro (nome_livro, autor, ano, status, paginas_total, avaliacao) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $nome_livro,
    $autor,
    $ano === '' ? null : (int)$ano,
    $status,
    $paginas_total ?: null,
    $avaliacao ?: null,
]);

header('Location: index.php');
exit;
?>
