<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Nome do usuário logado
$sql_user = "SELECT CLI_NOME FROM TB_CLIENTE WHERE CLI_ID = '$usuario_id'";
$result_user = mysqli_query($conexao, $sql_user);
$usuario_nome = "";
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $row_user = mysqli_fetch_assoc($result_user);
    $usuario_nome = $row_user['CLI_NOME'];
}

// Verifica se o ID foi passado corretamente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<h2 style='color:white; text-align:center; margin-top:50px;'>Evento não encontrado!</h2>";
    exit;
}
$evento_id = intval($_GET['id']);

// ADICIONAR AO CARRINHO 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_carrinho'])) {
    $ingresso_id  = intval($_POST['ingresso_id']);
    $meia_inteira = $_POST['meia_inteira'] ?? 'inteira';
    $quantidade   = intval($_POST['quantidade']);
    if ($quantidade < 1) $quantidade = 1;

    // Buscar valor do ingresso
    $sql_ing = "SELECT ING_VALOR FROM TB_INGRESSO WHERE ING_ID = '$ingresso_id'";
    $res_ing = mysqli_query($conexao, $sql_ing);
    if ($res_ing && mysqli_num_rows($res_ing) > 0) {
        $row_ing = mysqli_fetch_assoc($res_ing);
        $valor_unit = (float)$row_ing['ING_VALOR'];
        if ($meia_inteira === "meia") {
            $valor_unit = $valor_unit / 2;
        }
        $valor_total = $valor_unit * $quantidade;

        $sql_add = "INSERT INTO TB_CARRINHO (CLI_ID, ING_ID, CAR_MEIA_INTEIRA, CAR_QUANTIDADE, CAR_VALOR, CAR_DATA_ADICIONADO)
                    VALUES ('$usuario_id', '$ingresso_id', '$meia_inteira', '$quantidade', '$valor_total', NOW())";

        if (mysqli_query($conexao, $sql_add)) {
            echo "<script>alert('Ingresso adicionado ao carrinho!'); window.location='evento.php?id=$evento_id';</script>";
            exit;
        } else {
            echo "<script>alert('Erro ao adicionar ingresso: ".mysqli_error($conexao)."');</script>";
        }
    }
}

// EVENTO 
$sql_evento = "SELECT * FROM TB_EVENTO WHERE EVE_ID = '$evento_id' LIMIT 1";
$result_evento = mysqli_query($conexao, $sql_evento);
$evento = mysqli_fetch_assoc($result_evento);

if (!$evento) {
    echo "<h2 style='color:white; text-align:center; margin-top:50px;'>Evento não encontrado!</h2>";
    exit;
}

// Oculta evento expirado (mais de 1 dia após a data)
if (strtotime($evento['EVE_DATA']) < strtotime('-1 day')) {
    echo "<h2 style='color:white; text-align:center; margin-top:50px;'>Evento expirado!</h2>";
    exit;
}

// Puxa ingressos do evento
$sql_ingressos = "SELECT * FROM TB_INGRESSO WHERE EVE_ID = '$evento_id'";
$result_ingressos = mysqli_query($conexao, $sql_ingressos);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($evento['EVE_NOME']); ?> - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
    body { font-family: Arial, sans-serif; background:#0b0010; color:white; margin:0; }
    header { background:#0a0013; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .logo { font-size:22px; font-weight:bold; color:white; text-decoration:none; }
    nav { flex-grow:1; display:flex; justify-content:center; gap:30px; }
    nav a { color:white; text-decoration:none; font-weight:bold; }
    nav a:hover { color:#ffb800; }
    .user-area { display:flex; align-items:center; gap:15px; }
    .btn-carrinho { padding:8px 14px; background:#8000c8; border-radius:6px; color:white; font-weight:bold; text-decoration:none; transition:.3s; }
    .btn-carrinho:hover { background:#a44dff; }
    .user-menu { position:relative; }
    .user-name { font-weight:bold; color:#a76dff; cursor:pointer; }
    .dropdown { display:none; position:absolute; right:0; background:#1c1c1c; border-radius:6px; margin-top:8px; padding:10px; min-width:160px; z-index:100; }
    .dropdown a,.dropdown button { display:block; padding:8px; color:white; text-decoration:none; background:none; border:none; text-align:left; width:100%; cursor:pointer; }
    .dropdown a:hover,.dropdown button:hover { background:#2a2a2a; }

    .container { max-width:900px; margin:30px auto; background:#1c1c1c; padding:30px; border-radius:12px; }
    h1 { color:#ffcc00; margin-bottom:15px; text-align:center; }
    h2 { margin-top:30px; color:#ffcc00; }
    .ingresso-card { background:#2a2a2a; padding:20px; border-radius:8px; margin-bottom:15px; }
    .ingresso-card h3 { color:#b44dff; }
    .btn { background:#ffcc00; border:none; padding:10px 15px; cursor:pointer; border-radius:6px; font-weight:bold; }
    .btn:hover { background:#ffdb4d; }
  </style>
</head>
<body>
<header>
  <a href="Home.php" class="logo">Festify</a>
  <nav>
    <a href="Home.php">Home</a>
    <a href="criar_evento.php">Criar Evento</a>
  </nav>
  <div class="user-area">
    <a href="carrinho.php" class="btn-carrinho">🛒 Carrinho</a>
    <div class="user-menu">
      <span class="user-name" onclick="toggleMenu()">Olá, <?php echo htmlspecialchars($usuario_nome); ?> ▼</span>
      <div class="dropdown" id="menuDropdown">
        <a href="meus_ingressos.php">Meus Ingressos</a>
        <a href="meus_eventos.php">Meus Eventos</a>
        <form action="logout.php" method="POST"><button type="submit">Sair</button></form>
      </div>
    </div>
  </div>
</header>

<div class="container">
  <h1><?php echo htmlspecialchars($evento['EVE_NOME']); ?></h1>
  <p><strong>Cantor:</strong> <?php echo htmlspecialchars($evento['EVE_CANTOR']); ?></p>
  <p><strong>Local:</strong> <?php echo htmlspecialchars($evento['EVE_LOCAL']); ?></p>
  <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($evento['EVE_DATA'])); ?></p>
  <p><strong>Tipo:</strong> <?php echo htmlspecialchars($evento['EVE_TIPO']); ?></p>

  <h2>Sobre o Evento</h2>
  <p><?php echo nl2br(htmlspecialchars($evento['EVE_DESCRICAO'])); ?></p>

  <h2>Ingressos Disponíveis</h2>
  <?php
  if ($result_ingressos && mysqli_num_rows($result_ingressos) > 0) {
      while ($ing = mysqli_fetch_assoc($result_ingressos)) {
          $estoque = intval($ing['ING_QUANTIDADE']);
          if ($estoque <= 0) {
              echo "<div class='ingresso-card'>
                      <h3>".htmlspecialchars($ing['ING_TIPO'])."</h3>
                      <p><strong>Preço:</strong> R$ ".number_format($ing['ING_VALOR'],2,',','.')."</p>
                      <p><strong>Estoque:</strong> Esgotado</p>
                    </div>";
          } else {
              echo "<div class='ingresso-card'>
                      <h3>".htmlspecialchars($ing['ING_TIPO'])." - R$ ".number_format($ing['ING_VALOR'],2,',','.')."</h3>
                      <p><strong>Benefícios:</strong> ".htmlspecialchars($ing['ING_BENEFICIOS'])."</p>
                      <p><strong>Disponíveis:</strong> $estoque</p>
                      <form method='POST' action=''>
                          <label>Meia ou Inteira: </label>
                          <select name='meia_inteira'>
                              <option value='inteira'>Inteira</option>
                              <option value='meia'>Meia</option>
                          </select>
                          <label> Quantidade: </label>
                          <input type='number' name='quantidade' value='1' min='1' max='$estoque'>
                          <input type='hidden' name='ingresso_id' value='{$ing['ING_ID']}'>
                          <button type='submit' name='add_carrinho' class='btn'>Adicionar ao Carrinho</button>
                      </form>
                    </div>";
          }
      }
  } else {
      echo "<p>Nenhum ingresso disponível.</p>";
  }
  ?>
</div>

<script>
function toggleMenu() {
  const menu = document.getElementById('menuDropdown');
  menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}
window.onclick = function(e) {
  if (!e.target.matches('.user-name')) {
    const d = document.getElementById('menuDropdown');
    if (d && d.style.display === 'block') d.style.display = 'none';
  }
}
</script>
</body>
</html>
