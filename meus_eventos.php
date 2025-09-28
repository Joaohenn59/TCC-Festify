<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar nome do usuário
$sql_user = "SELECT CLI_NOME FROM TB_CLIENTE WHERE CLI_ID = '$usuario_id' LIMIT 1";
$result_user = mysqli_query($conexao, $sql_user);
$usuario_nome = "Usuário";
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $usuario_nome = mysqli_fetch_assoc($result_user)['CLI_NOME'];
}

// Buscar eventos criados pelo usuário que ainda não começaram
$sql_eventos = "
    SELECT *
    FROM TB_EVENTO
    WHERE EVE_CRIADOR = '$usuario_id'
      AND EVE_DATA >= NOW()
    ORDER BY EVE_DATA ASC
";
$result_eventos = mysqli_query($conexao, $sql_eventos);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Meus Eventos - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
    body{font-family:Arial,sans-serif;background:#0b0010;color:#fff;margin:0}
    header{background:#0a0013;padding:15px 40px;display:flex;justify-content:space-between;align-items:center;position:relative}
    .logo{font-size:22px;font-weight:bold;color:#fff;text-decoration:none}
    nav{position:absolute;left:50%;transform:translateX(-50%);display:flex;gap:20px}
    nav a{color:#fff;text-decoration:none;font-weight:bold}
    nav a:hover{color:#ffb800}
    .user-area{display:flex;align-items:center;gap:15px}
    .btn-carrinho{padding:8px 14px;background:#8000c8;border-radius:6px;color:#fff;font-weight:bold;text-decoration:none}
    .btn-carrinho:hover{background:#a44dff}
    .user-menu{position:relative}
    .user-name{font-weight:bold;color:#a76dff;cursor:pointer}
    .dropdown{display:none;position:absolute;right:0;background:#1c1c1c;border-radius:6px;margin-top:8px;padding:10px;min-width:160px;z-index:100}
    .dropdown a,.dropdown button{display:block;padding:8px;color:#fff;text-decoration:none;background:none;border:none;text-align:left;width:100%;cursor:pointer}
    .dropdown a:hover,.dropdown button:hover{background:#2a2a2a}
    .container{max-width:900px;margin:30px auto;background:#1c1c1c;padding:30px;border-radius:12px}
    h1{color:#ffcc00;text-align:center}
    .evento-card{background:#2a2a2a;padding:15px;margin:15px 0;border-radius:8px}
    .evento-card h3{color:#b44dff;margin:0 0 10px 0}
    .evento-card a{display:inline-block;margin-top:10px;padding:8px 12px;background:#ffcc00;color:#000;text-decoration:none;font-weight:bold;border-radius:6px}
    .evento-card a:hover{background:#ffdb4d}
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
        <a href="meus_eventos.php">Meus Eventos</a>
        <a href="meus_ingressos.php">Meus Ingressos</a>
        <form action="logout.php" method="POST"><button type="submit">Sair</button></form>
      </div>
    </div>
  </div>
</header>

<div class="container">
  <h1>Meus Eventos</h1>
  <?php
  if ($result_eventos && mysqli_num_rows($result_eventos) > 0) {
      while ($evento = mysqli_fetch_assoc($result_eventos)) {
          echo "<div class='evento-card'>
                  <h3>".htmlspecialchars($evento['EVE_NOME'])."</h3>
                  <p><strong>Data:</strong> ".date('d/m/Y H:i', strtotime($evento['EVE_DATA']))."</p>
                  <p><strong>Local:</strong> ".htmlspecialchars($evento['EVE_LOCAL'])."</p>
                  <a href='editar_evento.php?id={$evento['EVE_ID']}'>Editar Evento</a>
                </div>";
      }
  } else {
      echo "<p style='text-align:center'>Você ainda não criou nenhum evento futuro.</p>";
  }
  ?>
</div>

<script>
function toggleMenu(){
  const d=document.getElementById('menuDropdown');
  d.style.display=(d.style.display==='block')?'none':'block';
}
window.onclick=function(e){
  if(!e.target.matches('.user-name')){
    const d=document.getElementById('menuDropdown');
    if(d&&d.style.display==='block')d.style.display='none';
  }
}
</script>
</body>
</html>
