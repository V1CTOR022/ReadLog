<?php
$arquivoBancoDados = __DIR__ . '/database.sqlite';
$pdo = new PDO('sqlite:' . $arquivoBancoDados);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("PRAGMA foreign_keys = ON");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS livro (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_livro TEXT NOT NULL,
        autor TEXT NOT NULL,
        ano INTEGER,
        status TEXT DEFAULT 'quero_ler',
        paginas_total INTEGER DEFAULT 0,
        paginas_lidas INTEGER DEFAULT 0,
        avaliacao REAL DEFAULT 0,
        data_adicao DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

$columns = $pdo->query("PRAGMA table_info(livro)")->fetchAll(PDO::FETCH_ASSOC);
$columnNames = array_column($columns, 'name');

$newColumns = [
    'status'        => "TEXT DEFAULT 'quero_ler'",
    'paginas_total' => "INTEGER DEFAULT 0",
    'paginas_lidas' => "INTEGER DEFAULT 0",
    'avaliacao'     => "REAL DEFAULT 0",
    'data_adicao'   => "DATETIME DEFAULT CURRENT_TIMESTAMP",
];

foreach ($newColumns as $col => $definition) {
    if (!in_array($col, $columnNames)) {
        $pdo->exec("ALTER TABLE livro ADD COLUMN $col $definition");
    }
}
?>
