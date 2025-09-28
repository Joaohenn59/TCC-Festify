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

// Eventos (mostra futuros e até 1 dia após a data)
$sql_eventos = "SELECT * FROM TB_EVENTO 
                WHERE EVE_DATA >= (NOW() - INTERVAL 1 DAY)
                ORDER BY EVE_ID DESC";
$result_eventos = mysqli_query($conexao, $sql_eventos);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Home - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
body {
  font-family: Arial, sans-serif;
  background:#0b0010;
  color:white;
  margin:0;
}

/* ===== Header ===== */
header {
  background:#0a0013;
  padding:15px 40px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  position:relative;
  flex-wrap:wrap;
}

.logo {
  font-size:22px;
  font-weight:bold;
  color:white;
  text-decoration:none;
}

nav {
  position:absolute;
  left:50%;
  transform:translateX(-50%);
  display:flex;
  gap:20px;
}

nav a {
  color:white;
  text-decoration:none;
  font-weight:bold;
}

nav a:hover {
  color:#ffb800;
}

.user-area {
  display:flex;
  align-items:center;
  gap:15px;
}

.btn-carrinho {
  padding:8px 14px;
  background:#8000c8;
  border-radius:6px;
  color:white;
  font-weight:bold;
  text-decoration:none;
  transition:.3s;
}

.btn-carrinho:hover {
  background:#a44dff;
}

.user-menu {
  position:relative;
  display:inline-block;
}

.user-name {
  font-weight:bold;
  color:#a76dff;
  cursor:pointer;
}

.dropdown {
  display:none;
  position:absolute;
  right:0;
  background:#1c1c1c;
  border-radius:6px;
  margin-top:8px;
  padding:10px;
  min-width:160px;
  z-index:100;
}

.dropdown a,
.dropdown button {
  display:block;
  padding:8px;
  color:white;
  text-decoration:none;
  background:none;
  border:none;
  text-align:left;
  width:100%;
  cursor:pointer;
}

.dropdown a:hover,
.dropdown button:hover {
  background:#2a2a2a;
}

/* ===== Conteúdo ===== */
.container {
  padding:30px;
  text-align:center;
}

h2 {
  color:#ffcc00;
  margin-bottom:20px;
}

.eventos {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* responsivo */
  gap: 20px; /* espaço entre cards */
  margin-top: 30px;
}

.evento-card {
  background: #1c1c1c;
  padding: 20px;
  border-radius: 10px;
  color: white;
  text-decoration: none;
}

.evento-card:hover {
  background: #2a2a2a;
  transform: scale(1.02);
}

.evento-card h3 {
  color:#b44dff;
  margin-bottom:10px;
}

.evento-card p {
  margin:5px 0;
  border-top: 1px solid #444; /* linha divisória interna */
  padding-top: 5px;
}

/* ===== Responsividade ===== */
@media (max-width: 900px) {
  header {
    flex-direction:column;
    gap:10px;
    text-align:center;
  }

  nav {
    position:static;
    transform:none;
    justify-content:center;
    flex-wrap:wrap;
  }

  .user-area {
    justify-content:center;
    margin-top:10px;
  }

  .container {
    padding:15px;
  }
}

@media (max-width: 600px) {
  .logo {
    font-size:18px;
  }

  nav {
    flex-direction:column;
    gap:10px;
  }

  .btn-carrinho {
    width:100%;
    text-align:center;
  }

  .evento-card {
    padding:15px;
  }

  .evento-card h3 {
    font-size:16px;
  }
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
      <span class="user-name" onclick="toggleMenu()">Olá, <?php echo htmlspecialchars($usuario_nome); ?> ▼</span>
      <div class="dropdown" id="menuDropdown">
        <a href="meus_ingressos.php">Meus Ingressos</a>
        <form action="logout.php" method="POST"><button type="submit">Sair</button></form>
      </div>
    </div>
  </div>
</header>

<div class="container">
  <h2>Eventos Disponíveis</h2>
  <div class="eventos">
    <?php
    if ($result_eventos && mysqli_num_rows($result_eventos) > 0) {
        while ($evento = mysqli_fetch_assoc($result_eventos)) {
            $eve_id = $evento['EVE_ID'];

            // menor preço dos ingressos do evento
            $sql_preco = "SELECT MIN(ING_VALOR) AS preco_min FROM TB_INGRESSO WHERE EVE_ID = '$eve_id'";
            $res_preco = mysqli_query($conexao, $sql_preco);
            $preco_min = 0.00;
            if ($res_preco && mysqli_num_rows($res_preco) > 0) {
                $row_preco = mysqli_fetch_assoc($res_preco);
                $preco_min = floatval($row_preco['preco_min'] ?? 0);
            }

            echo "<a href='evento.php?id={$evento['EVE_ID']}' class='evento-card'>
                    <h3>".htmlspecialchars($evento['EVE_NOME'])."</h3>
                    <p><strong>Tipo:</strong> ".htmlspecialchars($evento['EVE_TIPO'])."</p>
                    <p><strong>Música:</strong> ".htmlspecialchars($evento['EVE_MUSICA'])."</p>
                    <p><strong>Local:</strong> ".htmlspecialchars($evento['EVE_LOCAL'])."</p>
                    <p><strong>Data:</strong> ".date('d/m/Y H:i', strtotime($evento['EVE_DATA']))."</p>
                    <p><strong>Preço:</strong> A partir de R$ ".number_format($preco_min, 2, ',', '.')."</p>
                  </a>";
        }
    } else {
        echo "<p>Nenhum evento disponível.</p>";
    }
    ?>
  </div>
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
