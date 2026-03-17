<?php
require_once 'database.php';
$db = new Database();
$pdo = $db->getConnection();

$pendentes = $pdo->query("SELECT * FROM tarefas WHERE concluida = 0 ORDER BY data_vencimento")->fetchAll();
$concluidas = $pdo->query("SELECT * FROM tarefas WHERE concluida = 1 ORDER BY data_vencimento")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cronograma</title>
    <style>
        .caixa-tarefa {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
     body {
            font-family: Arial; 
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
             background: #e9e7e7ff;
        }
     h1 {
            background: #b700ffff;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
      
     .pendente {
       background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 10px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
     .concluida {            
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 10px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;}
        
     form {
            margin: 10px 0; 
        }
        
     input, button, textarea {
            padding: 5px; 
            margin: 2px;
        }
        
     textarea {
            width: 100%;
            height: 60px;
        }
  
     h3{           
            background: #af4c4cff;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;}
            
     h4{
            font-size: 20px;
            background: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
     button {
            background-color: #4CAF50; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        
     button:hover {
            background-color: #45a049; 
        }
        
     .descricao-tarefa {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Cronograma</h1>

   <div class="caixa-tarefa">
        <h2>Adicionar Nova Tarefa</h2>
        <form action="add_tarefa.php" method="POST" id="formTarefa">
            <div class="form-group">
                <label for="descricao">Nome da Tarefa:</label>
                <input type="text" id="descricao" name="descricao" required 
                           placeholder="Digite o nome da tarefa...">
            </div>
            <div class="form-group">
                    <label for="detalhes">Descrição da Tarefa:</label>
                    <textarea id="detalhes" name="detalhes" placeholder="Digite a descrição da tarefa..."></textarea>
            </div>
            <div class="form-group">
                    <label for="data_vencimento">Data de Vencimento:</label>
                    <input type="date" id="data_vencimento" name="data_vencimento" required>
            </div>
            <button type="submit"> Adicionar Tarefa</button>
        </form>
    </div>

    <h3>Pendentes (<?= count($pendentes) ?>)</h3>
        <?php foreach ($pendentes as $t): ?>
           <div class="pendente">
    <h4><?= htmlspecialchars($t['descricao']) ?></h4>
        <?php if (!empty($t['detalhes'])): ?>
            <div class="descricao-tarefa">
        <?= htmlspecialchars($t['detalhes']) ?>
            </div>
            <?php endif; ?>
            <small>Data: <?= date('d/m/y', strtotime($t['data_vencimento'])) ?></small>
            
            <form action="update_tarefa.php" method="POST" style="display:inline">
            <input type="hidden" name="id" value="<?= $t['id'] ?>">
            <button>✔ Concluir</button>
        </form>
            
        <form action="delete_tarefa.php" method="POST" style="display:inline">
            <input type="hidden" name="id" value="<?= $t['id'] ?>">
            <button>❌ Excluir</button>
        </form>
        </div>
    <?php endforeach; ?>

    <h4>Concluídas (<?= count($concluidas) ?>)</h4>
    <?php foreach ($concluidas as $t): ?>
        <div class="concluida">
    <h4><?= htmlspecialchars($t['descricao']) ?></h4>
        <small>Data: <?= date('d/m/y', strtotime($t['data_vencimento'])) ?></small>
            
        <form action="delete_tarefa.php" method="POST" style="display:inline">
            <input type="hidden" name="id" value="<?= $t['id'] ?>">
            <button>❌ Excluir</button>
        </form>
        </div>
    <?php endforeach; ?>

    <script>
        document.getElementById('data_vencimento').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>