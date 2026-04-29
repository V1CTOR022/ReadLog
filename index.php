<?php
require 'database.php';

$livros = $pdo->query('SELECT * FROM livro ORDER BY data_adicao DESC')->fetchAll(PDO::FETCH_ASSOC);

$totalLivros  = count($livros);
$livrosLendo  = count(array_filter($livros, fn($l) => $l['status'] === 'lendo'));
$livrosLidos  = count(array_filter($livros, fn($l) => $l['status'] === 'lido'));

$avaliacoesValidas = array_values(array_filter($livros, fn($l) => ($l['avaliacao'] ?? 0) > 0));
$mediaAvaliacao    = count($avaliacoesValidas) > 0
    ? round(array_sum(array_column($avaliacoesValidas, 'avaliacao')) / count($avaliacoesValidas), 1)
    : 0;

$leituraAtual = null;
foreach ($livros as $l) {
    if ($l['status'] === 'lendo') { $leituraAtual = $l; break; }
}

$atividadeRecente = array_slice($livros, 0, 3);

$bookColors = ['#8B6D3F','#5B7FA6','#2C3E50','#6B4226','#3D6B4F','#8B4513','#4A6741','#7B5EA7'];

function bookColor(int $id, array $colors): string {
    return $colors[$id % count($colors)];
}

function safeSubstr(string $str, int $max): string {
    return strlen($str) > $max ? substr($str, 0, $max) . '…' : $str;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Log</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:          #F7F3EE;
            --white:       #FFFFFF;
            --dark:        #1A1A1A;
            --text:        #3D3D3D;
            --muted:       #7A7A7A;
            --accent:      #8C5E2A;
            --accent-light:#C4956A;
            --accent-bg:   #F5E6D0;
            --green:       #22C55E;
            --green-bg:    #DCFCE7;
            --border:      #E8E3DC;
            --shadow:      0 1px 3px rgba(0,0,0,0.07), 0 4px 16px rgba(0,0,0,0.04);
            --radius:      12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        nav {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            gap: 40px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 17px;
            font-weight: 800;
            color: var(--dark);
            text-decoration: none;
            flex-shrink: 0;
            letter-spacing: -0.3px;
        }
        .logo-icon {
            width: 32px; height: 32px;
            background: var(--dark);
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .nav-links {
            display: flex;
            gap: 28px;
            list-style: none;
            flex: 1;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            transition: color .15s;
        }
        .nav-links a.active {
            color: var(--dark);
            font-weight: 600;
            border-bottom: 2px solid var(--dark);
            padding-bottom: 1px;
        }
        .nav-links a:hover { color: var(--dark); }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 14px;
            min-width: 220px;
        }
        .search-bar input {
            border: none; background: transparent; outline: none;
            font-size: 13px; color: var(--text); width: 100%;
            font-family: 'Inter', sans-serif;
        }
        .search-bar input::placeholder { color: var(--muted); }
        .icon-btn {
            width: 34px; height: 34px;
            border: 1px solid var(--border); border-radius: 8px;
            background: var(--white);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 17px; color: var(--muted);
        }
        .avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 13px; font-weight: 700; cursor: pointer;
        }

        /* ── HERO ── */
        .hero {
            max-width: 1280px;
            margin: 0 auto;
            padding: 56px 32px 48px;
            display: flex;
            align-items: center;
            gap: 56px;
        }
        .hero-text { flex: 1; }
        .hero-text h1 {
            font-size: 46px;
            font-weight: 800;
            color: var(--dark);
            line-height: 1.15;
            letter-spacing: -1px;
        }
        .hero-text h1 .accent { color: var(--accent); }
        .hero-text p {
            font-size: 16px;
            color: var(--muted);
            margin-top: 16px;
            margin-bottom: 32px;
            line-height: 1.65;
            max-width: 400px;
        }
        .hero-buttons { display: flex; gap: 12px; }

        .btn-primary {
            background: var(--dark); color: white;
            border: none; padding: 11px 22px;
            border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
            font-family: 'Inter', sans-serif; text-decoration: none;
            transition: background .15s;
        }
        .btn-primary:hover { background: #2d2d2d; }

        .btn-secondary {
            background: transparent; color: var(--dark);
            border: 1.5px solid var(--dark);
            padding: 11px 22px; border-radius: 8px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
            font-family: 'Inter', sans-serif; text-decoration: none;
            transition: background .15s;
        }
        .btn-secondary:hover { background: rgba(0,0,0,0.05); }

        /* ── BOOKSHELF ── */
        .hero-visual { flex: 1; display: flex; justify-content: flex-end; }
        .bookshelf-scene {
            position: relative;
            width: 460px; height: 230px;
        }
        .shelf {
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 14px;
            background: linear-gradient(135deg, #C4956A, #A67C52);
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .books-row {
            position: absolute; bottom: 14px; left: 16px;
            display: flex; align-items: flex-end; gap: 5px;
        }
        .book-spine {
            border-radius: 3px 0 0 3px;
            box-shadow: 2px 0 8px rgba(0,0,0,0.2), inset -2px 0 4px rgba(0,0,0,0.1);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .book-spine .spine-title {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-size: 9px; font-weight: 700;
            color: rgba(255,255,255,0.85);
            padding: 4px 0; line-height: 1.2; text-align: center;
        }
        .plant {
            position: absolute; right: 16px; bottom: 14px;
            font-size: 44px; line-height: 1;
        }

        /* ── STATS ── */
        .stats-section {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px 40px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }
        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--shadow);
            display: flex; gap: 14px; align-items: flex-start;
        }
        .stat-icon {
            width: 46px; height: 46px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 21px; flex-shrink: 0;
        }
        .stat-icon.books   { background: var(--accent-bg); }
        .stat-icon.reading { background: #FEF3C7; }
        .stat-icon.done    { background: var(--green-bg); }
        .stat-icon.rating  { background: #FEF9C3; }

        .stat-number {
            font-size: 30px; font-weight: 800;
            color: var(--dark); line-height: 1;
        }
        .stat-label { font-size: 12px; color: var(--muted); margin-top: 4px; }
        .stat-change { font-size: 11px; font-weight: 600; margin-top: 8px; }
        .stat-change.positive { color: var(--green); }
        .stat-change.warning  { color: #F59E0B; }

        .stars-mini { display: flex; gap: 1px; margin-top: 7px; }
        .stars-mini .star       { color: #FCD34D; font-size: 13px; }
        .stars-mini .star.empty { color: #D1D5DB; }

        /* ── DASHBOARD ── */
        .dashboard {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--shadow);
        }
        .card-header {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 18px;
        }
        .card-title  { font-size: 17px; font-weight: 700; color: var(--dark); }
        .card-link   { font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 500; }
        .card-link:hover { text-decoration: underline; }

        /* current reading */
        .current-reading { display: flex; gap: 18px; }
        .book-cover {
            width: 88px; height: 126px; border-radius: 4px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 4px 4px 14px rgba(0,0,0,0.22);
            font-size: 10px; font-weight: 700;
            color: rgba(255,255,255,0.9); text-align: center; padding: 8px; line-height: 1.3;
        }
        .book-info { flex: 1; }
        .book-info h3 { font-size: 17px; font-weight: 700; color: var(--dark); margin-bottom: 3px; line-height: 1.3; }
        .book-info .author { font-size: 13px; color: var(--muted); margin-bottom: 18px; }

        .progress-label {
            display: flex; justify-content: space-between;
            font-size: 12px; color: var(--muted); margin-bottom: 7px;
        }
        .progress-bar {
            height: 6px; background: var(--border);
            border-radius: 999px; overflow: hidden; margin-bottom: 18px;
        }
        .progress-fill {
            height: 100%; background: var(--accent);
            border-radius: 999px; transition: width .3s;
        }
        .book-actions { display: flex; align-items: center; gap: 10px; }

        .no-current {
            text-align: center; padding: 28px;
            color: var(--muted); font-size: 14px; line-height: 1.6;
        }
        .no-current .icon { font-size: 34px; margin-bottom: 10px; }

        /* activity */
        .activity-list { display: flex; flex-direction: column; gap: 14px; }
        .activity-item { display: flex; align-items: center; gap: 11px; }
        .activity-cover {
            width: 42px; height: 56px; border-radius: 3px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 8px; font-weight: 700;
            color: rgba(255,255,255,0.9); text-align: center; padding: 4px;
        }
        .activity-info .action { font-size: 13px; color: var(--text); line-height: 1.4; }
        .activity-info .action strong { font-weight: 600; color: var(--dark); }
        .activity-info .time { font-size: 11px; color: var(--muted); margin-top: 2px; }

        /* ── MY SHELF ── */
        .shelf-section {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px 64px;
        }
        .section-header {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 22px;
        }
        .section-title { font-size: 21px; font-weight: 700; color: var(--dark); }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(195px, 1fr));
            gap: 18px;
        }
        .book-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform .2s, box-shadow .2s;
        }
        .book-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.11); }
        .book-card-cover {
            width: 100%; height: 154px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            color: rgba(255,255,255,0.9); text-align: center; padding: 14px; line-height: 1.4;
        }
        .book-card-body { padding: 13px 13px 8px; }
        .book-card-title { font-size: 13px; font-weight: 600; color: var(--dark); margin-bottom: 3px; line-height: 1.3; }
        .book-card-author { font-size: 11px; color: var(--muted); margin-bottom: 9px; }

        .badge {
            display: inline-flex; align-items: center;
            font-size: 10px; font-weight: 600;
            padding: 2px 7px; border-radius: 999px;
        }
        .badge-lendo  { background: #DBEAFE; color: #1D4ED8; }
        .badge-lido   { background: var(--green-bg); color: #16A34A; }
        .badge-quero  { background: #F3F4F6; color: #6B7280; }
        .badge-pausado{ background: #FEF3C7; color: #D97706; }

        .book-card-footer {
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 0 13px 12px;
        }
        .book-card-stars .star       { color: #FCD34D; font-size: 11px; }
        .book-card-stars .star.empty { color: #D1D5DB; }

        .delete-btn {
            background: none; border: none;
            color: #EF4444; cursor: pointer; font-size: 16px;
            padding: 2px; opacity: 0.55; transition: opacity .15s;
        }
        .delete-btn:hover { opacity: 1; }

        /* ── EMPTY STATE ── */
        .empty-state { text-align: center; padding: 56px 32px; color: var(--muted); }
        .empty-state .empty-icon { font-size: 44px; margin-bottom: 14px; }
        .empty-state h3 { font-size: 17px; font-weight: 600; color: var(--text); margin-bottom: 7px; }
        .empty-state p  { font-size: 14px; }

        /* ── MODALS ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.38);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none; align-items: center; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--white);
            border-radius: 16px; padding: 30px;
            width: 100%; max-width: 460px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18);
        }
        .modal-title { font-size: 19px; font-weight: 700; color: var(--dark); margin-bottom: 22px; }
        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block; font-size: 12px; font-weight: 600;
            color: var(--text); margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
            width: 100%; border: 1.5px solid var(--border);
            border-radius: 8px; padding: 9px 13px;
            font-size: 13px; font-family: 'Inter', sans-serif;
            color: var(--dark); background: var(--white);
            outline: none; transition: border-color .15s;
        }
        .form-group input:focus,
        .form-group select:focus { border-color: var(--accent); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .modal-footer {
            display: flex; gap: 10px;
            margin-top: 22px; justify-content: flex-end;
        }
        .btn-cancel {
            background: transparent; border: 1.5px solid var(--border);
            color: var(--text); padding: 9px 18px;
            border-radius: 8px; font-size: 13px; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif;
        }
        .btn-submit {
            background: var(--dark); color: white; border: none;
            padding: 9px 22px; border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif;
        }
        .btn-submit:hover { background: #2d2d2d; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-inner">
        <a href="index.php" class="logo">
            <div class="logo-icon">📚</div>
            READ LOG
        </a>
        <ul class="nav-links">
            <li><a href="#inicio" class="active">Início</a></li>
            <li><a href="#minha-estante">Minha Estante</a></li>
            <li><a href="#" onclick="openModal(); return false;">Adicionar</a></li>
        </ul>
        <div class="nav-right">
            <div class="search-bar">
                <span style="color:var(--muted);font-size:14px;">🔍</span>
                <input type="text" placeholder="Buscar livros..." id="searchInput" oninput="filterBooks()">
            </div>
            <div class="icon-btn">🔔</div>
            <div class="avatar">RL</div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section id="inicio">
    <div class="hero">
        <div class="hero-text">
            <h1>Registre. Acompanhe.<br><span class="accent">Lembre-se de cada história.</span></h1>
            <p>O Read Log é o seu espaço para armazenar livros, acompanhar suas leituras e descobrir novas histórias.</p>
            <div class="hero-buttons">
                <button class="btn-primary" onclick="openModal()">+ Adicionar Livro</button>
                <a href="#minha-estante" class="btn-secondary">📚 Ver Minha Estante</a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="bookshelf-scene">
                <div class="books-row">
                    <?php
                    $spineColors  = ['#C4956A','#8BA3B8','#2C3E50','#B5825A','#4A6741'];
                    $spineHeights = [186, 162, 194, 174, 168];
                    $spineWidths  = [52,  44,  48,  50,  46];
                    $shelfBooks   = array_slice($livros, 0, 5);
                    if (count($shelfBooks) > 0):
                        foreach ($shelfBooks as $i => $sb):
                            $sc = $spineColors[$i % 5];
                            $sh = $spineHeights[$i % 5];
                            $sw = $spineWidths[$i % 5];
                    ?>
                    <div class="book-spine" style="background:<?= $sc ?>;height:<?= $sh ?>px;width:<?= $sw ?>px;">
                        <span class="spine-title"><?= htmlspecialchars(safeSubstr($sb['nome_livro'], 18)) ?></span>
                    </div>
                    <?php endforeach;
                    else:
                        foreach ($spineColors as $i => $sc): ?>
                    <div class="book-spine" style="background:<?= $sc ?>;height:<?= $spineHeights[$i] ?>px;width:<?= $spineWidths[$i] ?>px;"></div>
                    <?php endforeach; endif; ?>
                </div>
                <div class="shelf"></div>
                <div class="plant">🪴</div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<div class="stats-section">
    <div class="stat-card">
        <div class="stat-icon books">📖</div>
        <div>
            <div class="stat-number"><?= $totalLivros ?></div>
            <div class="stat-label">Livros na estante</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon reading">🔖</div>
        <div>
            <div class="stat-number"><?= $livrosLendo ?></div>
            <div class="stat-label">Leituras em andamento</div>
            <?php if ($livrosLendo > 0): ?>
            <div class="stat-change warning"><?= $livrosLendo ?> ativa<?= $livrosLendo > 1 ? 's' : '' ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon done">✅</div>
        <div>
            <div class="stat-number"><?= $livrosLidos ?></div>
            <div class="stat-label">Livros lidos</div>
            <?php if ($livrosLidos > 0): ?>
            <div class="stat-change positive">+<?= $livrosLidos ?> concluídos</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rating">⭐</div>
        <div>
            <div class="stat-number"><?= $mediaAvaliacao > 0 ? $mediaAvaliacao : '—' ?></div>
            <div class="stat-label">Avaliação média</div>
            <?php if ($mediaAvaliacao > 0): ?>
            <div class="stars-mini">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star <?= $i <= round($mediaAvaliacao) ? '' : 'empty' ?>">
                    <?= $i <= round($mediaAvaliacao) ? '★' : '☆' ?>
                </span>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="dashboard">


    <div class="card">
        <div class="card-header">
            <span class="card-title">Leitura Atual</span>
        </div>
        <?php if ($leituraAtual): ?>
        <?php
            $pg    = (int)$leituraAtual['paginas_total'];
            $pl    = (int)$leituraAtual['paginas_lidas'];
            $pct   = ($pg > 0) ? min(100, (int)round($pl / $pg * 100)) : 0;
            $cover = bookColor($leituraAtual['id'], $bookColors);
        ?>
        <div class="current-reading">
            <div class="book-cover" style="background:<?= $cover ?>;">
                <?= htmlspecialchars(safeSubstr($leituraAtual['nome_livro'], 28)) ?>
            </div>
            <div class="book-info">
                <h3><?= htmlspecialchars($leituraAtual['nome_livro']) ?></h3>
                <div class="author"><?= htmlspecialchars($leituraAtual['autor']) ?></div>
                <?php if ($pg > 0): ?>
                <div class="progress-label">
                    <span><?= $pct ?>% concluído</span>
                    <span><?= $pl ?> / <?= $pg ?> páginas</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%;"></div></div>
                <?php else: ?>
                <div style="margin-bottom:18px;"></div>
                <?php endif; ?>
                <div class="book-actions">
                    <button class="btn-primary"
                        onclick="openUpdateModal(<?= $leituraAtual['id'] ?>,'<?= addslashes(htmlspecialchars($leituraAtual['nome_livro'])) ?>',<?= $pl ?>,<?= $pg ?>,'lendo')">
                        Continuar Lendo
                    </button>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="no-current">
            <div class="icon">📖</div>
            <p>Nenhum livro em andamento.<br>Adicione um e marque como "Lendo"!</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Atividade Recente</span>
            <a href="#minha-estante" class="card-link">Ver todas</a>
        </div>
        <?php if (count($atividadeRecente) > 0): ?>
        <div class="activity-list">
            <?php foreach ($atividadeRecente as $a):
                $aColor = bookColor($a['id'], $bookColors);
                $action = match($a['status'] ?? 'quero_ler') {
                    'lido'      => 'Você concluiu',
                    'lendo'     => 'Você está lendo',
                    'pausado'   => 'Você pausou',
                    default     => 'Você adicionou',
                };
                $compl  = match($a['status'] ?? 'quero_ler') {
                    'lido'  => '',
                    'lendo' => 'à sua leitura',
                    default => 'à sua estante',
                };
                $when = 'Recentemente';
                if (!empty($a['data_adicao'])) {
                    $diff = (new DateTime())->diff(new DateTime($a['data_adicao']));
                    if ($diff->days === 0)     $when = 'Hoje';
                    elseif ($diff->days === 1) $when = 'Ontem';
                    else                       $when = $diff->days . ' dias atrás';
                }
            ?>
            <div class="activity-item">
                <div class="activity-cover" style="background:<?= $aColor ?>;">
                    <?= htmlspecialchars(safeSubstr($a['nome_livro'], 14)) ?>
                </div>
                <div class="activity-info">
                    <div class="action">
                        <?= $action ?> <strong><?= htmlspecialchars($a['nome_livro']) ?></strong><?= $compl ? ' ' . $compl : '' ?>
                    </div>
                    <div class="time"><?= $when ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-current">
            <div class="icon">🕐</div>
            <p>Nenhuma atividade ainda.<br>Adicione seu primeiro livro!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MINHA ESTANTE -->
<section id="minha-estante">
    <div class="shelf-section">
        <div class="section-header">
            <span class="section-title">Minha Estante</span>
            <button class="btn-primary" onclick="openModal()">+ Adicionar Livro</button>
        </div>

        <?php if (count($livros) > 0): ?>
        <div class="books-grid" id="booksGrid">
            <?php foreach ($livros as $livro):
                $color      = bookColor($livro['id'], $bookColors);
                $statusKey  = $livro['status'] ?? 'quero_ler';
                $statusLabel = match($statusKey) {
                    'lendo'     => 'Lendo',
                    'lido'      => 'Lido',
                    'pausado'   => 'Pausado',
                    default     => 'Quero Ler',
                };
                $badgeClass = match($statusKey) {
                    'lendo'   => 'badge-lendo',
                    'lido'    => 'badge-lido',
                    'pausado' => 'badge-pausado',
                    default   => 'badge-quero',
                };
                $rating = (float)($livro['avaliacao'] ?? 0);
            ?>
            <div class="book-card"
                 data-title="<?= strtolower(htmlspecialchars($livro['nome_livro'])) ?>"
                 data-author="<?= strtolower(htmlspecialchars($livro['autor'])) ?>">
                <div class="book-card-cover"
                     style="background:<?= $color ?>;cursor:pointer;"
                     onclick="openUpdateModal(<?= $livro['id'] ?>,'<?= addslashes(htmlspecialchars($livro['nome_livro'])) ?>',<?= (int)$livro['paginas_lidas'] ?>,<?= (int)$livro['paginas_total'] ?>,'<?= $statusKey ?>',<?= $rating ?>)">
                    <?= htmlspecialchars(safeSubstr($livro['nome_livro'], 38)) ?>
                </div>
                <div class="book-card-body">
                    <div class="book-card-title"><?= htmlspecialchars($livro['nome_livro']) ?></div>
                    <div class="book-card-author">
                        <?= htmlspecialchars($livro['autor']) ?>
                        <?= $livro['ano'] ? ' · ' . $livro['ano'] : '' ?>
                    </div>
                    <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                </div>
                <div class="book-card-footer">
                    <div class="book-card-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?= $i <= $rating ? '' : 'empty' ?>"><?= $i <= $rating ? '★' : '☆' ?></span>
                        <?php endfor; ?>
                    </div>
                    <form id="del_<?= $livro['id'] ?>" action="delete_book.php" method="post" style="display:inline">
                        <input type="hidden" name="id" value="<?= $livro['id'] ?>">
                        <button type="button" class="delete-btn"
                                onclick="confirmDelete(<?= $livro['id'] ?>)" title="Excluir">🗑</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>Sua estante está vazia</h3>
            <p>Adicione seu primeiro livro para começar!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-title">Adicionar Livro</div>
        <form action="add_book.php" method="post">
            <div class="form-group">
                <label>Nome do Livro *</label>
                <input type="text" name="nome_livro" placeholder="Ex: O Alquimista" required autofocus>
            </div>
            <div class="form-group">
                <label>Autor *</label>
                <input type="text" name="autor" placeholder="Ex: Paulo Coelho" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ano de Publicação</label>
                    <input type="number" name="ano" placeholder="Ex: 1988" min="1000" max="2099">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="quero_ler">Quero Ler</option>
                        <option value="lendo">Lendo</option>
                        <option value="lido">Lido</option>
                        <option value="pausado">Pausado</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Total de Páginas</label>
                    <input type="number" name="paginas_total" placeholder="Ex: 320" min="1">
                </div>
                <div class="form-group">
                    <label>Avaliação (0–5)</label>
                    <input type="number" name="avaliacao" placeholder="Ex: 4.5" min="0" max="5" step="0.5">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-submit">Salvar Livro</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="updateModal">
    <div class="modal">
        <div class="modal-title">Atualizar Livro</div>
        <form action="update_book.php" method="post">
            <input type="hidden" name="id" id="updateId">
            <div class="form-group">
                <div id="updateBookTitle" style="font-size:15px;font-weight:700;color:var(--dark);margin-bottom:14px;"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Páginas Lidas</label>
                    <input type="number" name="paginas_lidas" id="updatePaginasLidas" min="0">
                </div>
                <div class="form-group">
                    <label>Total de Páginas</label>
                    <input type="number" name="paginas_total" id="updatePaginasTotal" min="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="updateStatus">
                        <option value="lendo">Lendo</option>
                        <option value="lido">Lido</option>
                        <option value="pausado">Pausado</option>
                        <option value="quero_ler">Quero Ler</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Avaliação (0–5)</label>
                    <input type="number" name="avaliacao" id="updateAvaliacao" min="0" max="5" step="0.5" placeholder="Ex: 4.5">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeUpdateModal()">Cancelar</button>
                <button type="submit" class="btn-submit">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('addModal').classList.add('open');
}
function closeModal() {
    document.getElementById('addModal').classList.remove('open');
}

function openUpdateModal(id, title, paginasLidas, paginasTotal, status, avaliacao) {
    document.getElementById('updateId').value          = id;
    document.getElementById('updateBookTitle').textContent = title;
    document.getElementById('updatePaginasLidas').value   = paginasLidas || '';
    document.getElementById('updatePaginasTotal').value   = paginasTotal || '';
    document.getElementById('updateAvaliacao').value       = avaliacao   || '';
    const sel = document.getElementById('updateStatus');
    for (let opt of sel.options) opt.selected = (opt.value === status);
    document.getElementById('updateModal').classList.add('open');
}
function closeUpdateModal() {
    document.getElementById('updateModal').classList.remove('open');
}

document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.getElementById('updateModal').addEventListener('click', function(e) {
    if (e.target === this) closeUpdateModal();
});

function confirmDelete(id) {
    if (confirm('Quer mesmo excluir este livro?')) {
        document.getElementById('del_' + id).submit();
    }
}

function filterBooks() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.book-card').forEach(card => {
        const match = card.dataset.title.includes(q) || card.dataset.author.includes(q);
        card.style.display = match ? '' : 'none';
    });
}
</script>
</body>
</html>
