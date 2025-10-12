<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$evento_id = $_GET['id'] ?? 0;
$evento_id = intval($evento_id);

// Buscar evento do usuário logado
$sql_evento = "SELECT * FROM TB_EVENTO WHERE EVE_ID='$evento_id' AND EVE_CRIADOR='$usuario_id' LIMIT 1";
$result_evento = mysqli_query($conexao, $sql_evento);
$evento = mysqli_fetch_assoc($result_evento);

if (!$evento) {
    echo "<h2 style='color:white;text-align:center;margin-top:50px'>Evento não encontrado ou você não tem permissão!</h2>";
    exit;
}

// ATUALIZAR EVENTO E INGRESSOS 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['atualizar'])) {
    $nome = $_POST['nome'];
    $local = $_POST['local'];
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];

    $dataHora = trim($data . ' ' . $hora);

    // Atualiza evento
    $sql_update = "
        UPDATE TB_EVENTO
        SET EVE_NOME='$nome',
            EVE_LOCAL='$local',
            EVE_DATA='$dataHora',
            EVE_TIPO='$tipo',
            EVE_DESCRICAO='$descricao'
        WHERE EVE_ID='$evento_id' AND EVE_CRIADOR='$usuario_id'";
    mysqli_query($conexao, $sql_update);

    // Atualiza ingressos existentes
    if (!empty($_POST['ingresso_id'])) {
        foreach ($_POST['ingresso_id'] as $i => $ing_id) {
            $tipo = $_POST['ingresso_tipo'][$i];
            $valor = $_POST['ingresso_valor'][$i];
            $qtd   = $_POST['ingresso_qtd'][$i];
            $benef = $_POST['ingresso_benef'][$i];

            $sql_up_ing = "
                UPDATE TB_INGRESSO
                SET ING_TIPO='$tipo',
                    ING_VALOR='$valor',
                    ING_QUANTIDADE='$qtd',
                    ING_BENEFICIOS='$benef'
                WHERE ING_ID='$ing_id' AND EVE_ID='$evento_id'";
            mysqli_query($conexao, $sql_up_ing);
        }
    }

    // Adiciona novos ingressos
    if (!empty($_POST['novo_tipo'])) {
        foreach ($_POST['novo_tipo'] as $j => $novo_tipo) {
            if (!empty($novo_tipo)) {
                $novo_valor = $_POST['novo_valor'][$j];
                $novo_qtd   = $_POST['novo_qtd'][$j];
                $novo_benef = $_POST['novo_benef'][$j];
                $sql_new_ing = "
                    INSERT INTO TB_INGRESSO (ING_TIPO, ING_VALOR, ING_QUANTIDADE, ING_BENEFICIOS, EVE_ID)
                    VALUES ('$novo_tipo','$novo_valor','$novo_qtd','$novo_benef','$evento_id')";
                mysqli_query($conexao, $sql_new_ing);
            }
        }
    }

    echo "<script>alert('Evento e ingressos atualizados com sucesso!'); window.location='meus_eventos.php';</script>";
}

// EXCLUIR EVENTO 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['excluir'])) {
    mysqli_query($conexao, "
        DELETE v FROM TB_VENDA_INGRESSO v
        INNER JOIN TB_INGRESSO i ON v.ING_ID = i.ING_ID
        WHERE i.EVE_ID = '$evento_id'
    ");
    mysqli_query($conexao, "
        DELETE c FROM TB_CARRINHO c
        INNER JOIN TB_INGRESSO i ON c.ING_ID = i.ING_ID
        WHERE i.EVE_ID = '$evento_id'
    ");
    mysqli_query($conexao, "DELETE FROM TB_INGRESSO WHERE EVE_ID = '$evento_id'");
    mysqli_query($conexao, "DELETE FROM TB_EVENTO WHERE EVE_ID='$evento_id' AND EVE_CRIADOR='$usuario_id'");
    echo "<script>alert('Evento e ingressos excluídos com sucesso!'); window.location='meus_eventos.php';</script>";
}

// LISTAR INGRESSOS
$sql_ingressos = "SELECT * FROM TB_INGRESSO WHERE EVE_ID='$evento_id'";
$result_ingressos = mysqli_query($conexao, $sql_ingressos);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Evento - Festify</title>
  <style>
    body{font-family:Arial,sans-serif;background:#0b0010;color:#fff;margin:0}
    .container{max-width:800px;margin:30px auto;background:#1c1c1c;padding:30px;border-radius:12px}
    h1{text-align:center;color:#ffcc00}
    form{display:flex;flex-direction:column;gap:10px}
    label{font-weight:bold}
    input,textarea{padding:10px;border-radius:6px;border:1px solid #555;background:#333;color:#fff;width:100%}
    input[type=submit],button{background:#8000c8;border:none;font-weight:bold;cursor:pointer;padding:10px;border-radius:6px;color:white}
    input[type=submit]:hover,button:hover{background:#a44dff}
    .danger{background:#cc0000 !important}
    .danger:hover{background:#ff0000 !important}
    .ingresso-box{background:#2a2a2a;padding:15px;border-radius:8px;margin-top:15px}
  </style>
</head>
<body>
<div class="container">
  <h1>Editar Evento</h1>
  <form method="POST">
    <label>Nome</label>
    <input type="text" name="nome" value="<?= htmlspecialchars($evento['EVE_NOME']); ?>" required>
    <label>Local</label>
    <input type="text" name="local" value="<?= htmlspecialchars($evento['EVE_LOCAL']); ?>" required>
    <label>Data</label>
    <input type="date" name="data" value="<?= date('Y-m-d', strtotime($evento['EVE_DATA'])); ?>" required>
    <label>Hora</label>
    <input type="time" name="hora" value="<?= date('H:i', strtotime($evento['EVE_DATA'])); ?>" required>
    <label>Tipo</label>
    <input type="text" name="tipo" value="<?= htmlspecialchars($evento['EVE_TIPO']); ?>" required>
    <label>Descrição</label>
    <textarea name="descricao"><?= htmlspecialchars($evento['EVE_DESCRICAO']); ?></textarea>

    <h2 style="color:#ffcc00;margin-top:20px;">Ingressos Existentes</h2>
    <?php
    if ($result_ingressos && mysqli_num_rows($result_ingressos) > 0) {
        while ($ing = mysqli_fetch_assoc($result_ingressos)) {
            echo "<div class='ingresso-box'>
                    <input type='hidden' name='ingresso_id[]' value='{$ing['ING_ID']}'>
                    <label>Tipo</label>
                    <input type='text' name='ingresso_tipo[]' value='".htmlspecialchars($ing['ING_TIPO'])."' required>
                    <label>Valor (R$)</label>
                    <input type='number' step='0.01' name='ingresso_valor[]' value='{$ing['ING_VALOR']}' required>
                    <label>Quantidade</label>
                    <input type='number' name='ingresso_qtd[]' value='{$ing['ING_QUANTIDADE']}' required>
                    <label>Benefícios</label>
                    <textarea name='ingresso_benef[]'>".htmlspecialchars($ing['ING_BENEFICIOS'])."</textarea>
                  </div>";
        }
    } else {
        echo "<p>Nenhum ingresso cadastrado.</p>";
    }
    ?>

    <h2 style="color:#ffcc00;margin-top:20px;">Adicionar Novos Ingressos</h2>
    <div id="novos-ingressos"></div>
    <button type="button" onclick="addIngresso()">+ Adicionar Ingresso</button>

    <input type="submit" name="atualizar" value="Salvar Alterações">
    <input type="submit" name="excluir" value="Excluir Evento" class="danger">
  </form>
</div>

<script>
function addIngresso(){
  const container = document.getElementById('novos-ingressos');
  const div = document.createElement('div');
  div.classList.add('ingresso-box');
  div.innerHTML = `
    <label>Tipo</label>
    <input type="text" name="novo_tipo[]" required>
    <label>Valor (R$)</label>
    <input type="number" step="0.01" name="novo_valor[]" required>
    <label>Quantidade</label>
    <input type="number" name="novo_qtd[]" required>
    <label>Benefícios</label>
    <textarea name="novo_benef[]"></textarea>
  `;
  container.appendChild(div);
}
</script>
</body>
</html>
