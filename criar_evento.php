<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Nome do usuário
$sql_user = "SELECT CLI_NOME FROM TB_CLIENTE WHERE CLI_ID = '$usuario_id'";
$result_user = mysqli_query($conexao, $sql_user);
$usuario_nome = "";
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $row_user = mysqli_fetch_assoc($result_user);
    $usuario_nome = $row_user['CLI_NOME'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cantor = $_POST['cantor'];
    $local = $_POST['local'];
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $genero = $_POST['genero'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];

    $sql_evento = "INSERT INTO TB_EVENTO (EVE_NOME, EVE_CANTOR, EVE_LOCAL, EVE_DATA, EVE_MUSICA, EVE_TIPO, EVE_DESCRICAO) 
                   VALUES ('$nome','$cantor','$local','$data $hora','$genero','$tipo','$descricao')";

    if (mysqli_query($conexao, $sql_evento)) {
        $evento_id = mysqli_insert_id($conexao);

        if (!empty($_POST['ingresso_tipo'])) {
            foreach ($_POST['ingresso_tipo'] as $i => $tipo_ingresso) {
                $valor = $_POST['ingresso_valor'][$i];
                $quantidade = $_POST['ingresso_quantidade'][$i];
                $beneficios = $_POST['ingresso_beneficios'][$i];

                $sql_ingresso = "INSERT INTO TB_INGRESSO 
                                 (ING_TIPO, ING_VALOR, ING_BENEFICIOS, ING_QUANTIDADE_TOTAL, ING_QUANTIDADE_RESTANTE, EVE_ID) 
                                 VALUES 
                                 ('$tipo_ingresso','$valor','$beneficios','$quantidade','$quantidade','$evento_id')";
                mysqli_query($conexao, $sql_ingresso);
            }
        }

        echo "<script>alert('Evento criado com sucesso!'); window.location='Home.php';</script>";
    } else {
        echo "Erro: " . mysqli_error($conexao);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Criar Evento - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
    body {font-family: Arial, sans-serif; background:#0b0010; color:white; margin:0}
    header {background:#0a0013; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; position:relative}
    .logo {font-size:22px; font-weight:bold; color:white; text-decoration:none}
    nav {position:absolute; left:50%; transform:translateX(-50%); display:flex; gap:20px}
    nav a {color:white; text-decoration:none; font-weight:bold}
    nav a:hover {color:#ffb800}
    .user-area {display:flex; align-items:center; gap:15px}
    .btn-carrinho {padding:8px 14px; background:#8000c8; border-radius:6px; color:white; font-weight:bold; text-decoration:none; transition:.3s}
    .btn-carrinho:hover {background:#a44dff}
    .user-menu {position:relative; display:inline-block}
    .user-name {font-weight:bold; color:#a76dff; cursor:pointer}
    .dropdown {display:none; position:absolute; right:0; background:#1c1c1c; border-radius:6px; margin-top:8px; padding:10px; min-width:160px; z-index:100}
    .dropdown a,.dropdown button {display:block;padding:8px;color:white;text-decoration:none;background:none;border:none;text-align:left;width:100%;cursor:pointer}
    .dropdown a:hover,.dropdown button:hover {background:#2a2a2a}
    
    .container {max-width:700px; margin:30px auto; background:#1c1c1c; padding:30px; border-radius:12px}
    h1 {text-align:center; color:#ffcc00}
    form {display:flex; flex-direction:column; gap:15px}
    label {font-weight:bold; text-align:left}
    input, textarea, select {
      padding:10px;
      border:1px solid #555;
      border-radius:6px;
      font-size:14px;
      width:100%;
      background:#333;
      color:white;
    }
    input:focus, textarea:focus, select:focus {
      outline:none;
      border:1px solid #ffcc00;
      background:#444;
    }
    input[type="submit"] {
      background:#8000c8;
      color:white;
      font-weight:bold;
      cursor:pointer;
      border:none;
      transition:0.3s;
    }
    input[type="submit"]:hover {background:#a44dff}
    .ingresso-box {
      background:#2a2a2a;
      padding:15px;
      border-radius:8px;
      margin-top:10px;
    }
    .add-btn {
      background:#ffcc00;
      color:black;
      font-weight:bold;
      cursor:pointer;
      padding:10px;
      border:none;
      border-radius:6px;
      transition:0.3s;
    }
    .add-btn:hover {background:#ffdb4d}

    @media (max-width:768px){
      .container {width:90%; padding:20px}
      header {flex-direction:column; gap:10px}
      nav {position:static; transform:none}
    }
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
      <span class="user-name" onclick="toggleMenu()">Olá, <?php echo $usuario_nome; ?> ▼</span>
      <div class="dropdown" id="menuDropdown">
        <a href="meus_ingressos.php">Meus Ingressos</a>
        <form action="logout.php" method="POST"><button type="submit">Sair</button></form>
      </div>
    </div>
  </div>
</header>

<div class="container">
  <h1>Criar Evento</h1>
  <form method="POST">
    <label>Nome do Evento</label>
    <input type="text" name="nome" required>

    <label>Cantor</label>
    <input type="text" name="cantor" required>

    <label>Local</label>
    <input type="text" name="local" required>

    <label>Data</label>
    <input type="date" name="data" required>

    <label>Hora</label>
    <input type="time" name="hora" required>

    <label>Gênero</label>
    <input type="text" name="genero" required>

    <label>Tipo</label>
    <input type="text" name="tipo" required>

    <label>Descrição</label>
    <textarea name="descricao" rows="3"></textarea>

    <h2>Ingressos</h2>
    <div id="ingressos">
      <div class="ingresso-box">
        <label>Tipo de Ingresso</label>
        <input type="text" name="ingresso_tipo[]" placeholder="Ex: VIP, Pista" required>

        <label>Valor (R$)</label>
        <input type="number" step="0.01" name="ingresso_valor[]" required>

        <label>Quantidade Total</label>
        <input type="number" name="ingresso_quantidade[]" min="1" required>

        <label>Benefícios</label>
        <textarea name="ingresso_beneficios[]" rows="2"></textarea>
      </div>
    </div>

    <button type="button" class="add-btn" onclick="adicionarIngresso()">+ Adicionar Ingresso</button>
    <input type="submit" value="Criar Evento">
  </form>
</div>

<script>
function adicionarIngresso() {
  const container = document.getElementById('ingressos');
  const div = document.createElement('div');
  div.classList.add('ingresso-box');
  div.innerHTML = `
    <label>Tipo de Ingresso</label>
    <input type="text" name="ingresso_tipo[]" placeholder="Ex: VIP, Pista" required>
    <label>Valor (R$)</label>
    <input type="number" step="0.01" name="ingresso_valor[]" required>
    <label>Quantidade Total</label>
    <input type="number" name="ingresso_quantidade[]" min="1" required>
    <label>Benefícios</label>
    <textarea name="ingresso_beneficios[]" rows="2"></textarea>
  `;
  container.appendChild(div);
}

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
