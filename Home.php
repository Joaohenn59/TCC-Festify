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
    $_SESSION['usuario_nome'] = $usuario_nome; // garante que sempre tem o nome
}

// ================= FILTROS =================
$where = [];
$data = $_GET['data'] ?? '';

if (!empty($data)) {
    // Filtrar apenas eventos no mesmo dia (independente da hora)
    $data_inicio = $data . " 00:00:00";
    $data_fim = $data . " 23:59:59";
    $where[] = "e.EVE_DATA BETWEEN '$data_inicio' AND '$data_fim'";
}

// condição WHERE
$where_sql = count($where) > 0 ? " AND " . implode(" AND ", $where) : "";

// ================= EVENTOS =================
$data_atual = date('Y-m-d H:i:s'); // pega a data e hora atuais

$sql_eventos = "
    SELECT e.*, 
           MIN(i.ING_VALOR) AS preco_min
    FROM TB_EVENTO e
    LEFT JOIN TB_INGRESSO i ON e.EVE_ID = i.EVE_ID
    WHERE e.EVE_DATA >= '$data_atual' $where_sql
    GROUP BY e.EVE_ID
    ORDER BY e.EVE_ID DESC
";
$result_eventos = mysqli_query($conexao, $sql_eventos);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Festify - Home</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      flex-wrap:wrap;
    }

    .logo { font-size:22px; font-weight:bold; color:white; text-decoration:none; }

    nav {
      flex-grow:1;
      display:flex;
      justify-content:center;
      gap:30px;
    }

    nav a { color:white; text-decoration:none; font-weight:bold; }
    nav a:hover { color:#ffb800; }

    .user-area { display:flex; align-items:center; gap:15px; }
    .btn-carrinho {
      padding:8px 14px;
      background:#8000c8;
      border-radius:6px;
      color:white;
      font-weight:bold;
      text-decoration:none;
      transition:.3s;
    }
    .btn-carrinho:hover { background:#a44dff; }

    .user-menu { position:relative; }
    .user-name { font-weight:bold; color:#a76dff; cursor:pointer; }
    .dropdown {
      display:none; position:absolute; right:0; background:#1c1c1c;
      border-radius:6px; margin-top:8px; padding:10px; min-width:160px; z-index:100;
    }
    .dropdown a,.dropdown button {
      display:block; padding:8px; color:white; text-decoration:none;
      background:none; border:none; text-align:left; width:100%; cursor:pointer;
    }
    .dropdown a:hover,.dropdown button:hover { background:#2a2a2a; }

    /* ===== Conteúdo ===== */
    .container { padding:30px; max-width:1200px; margin:auto; }

    h2 { color:#ffcc00; }

    /* ===== Filtros ===== */
    .filtros {
      background:#1c1c1c;
      padding:20px;
      border-radius:10px;
      margin-bottom:30px;
      display:flex;
      flex-wrap:wrap;
      gap:15px;
      align-items:end;
      justify-content:center;
    }
    .filtros label { display:block; font-weight:bold; margin-bottom:5px; }
    .filtros input {
      padding:8px;
      border:none;
      border-radius:6px;
      background:#333;
      color:white;
    }
    .filtros button {
      background:#ffcc00;
      border:none;
      padding:10px 15px;
      cursor:pointer;
      border-radius:6px;
      font-weight:bold;
    }
    .filtros button:hover { background:#ffdb4d; }

    /* ===== Cards ===== */
    .eventos {
      display:grid;
      grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
      gap:20px;
    }
    .evento-card {
      display:block;
      background:#1c1c1c;
      padding:20px;
      border-radius:10px;
      color:white;
      text-decoration:none;
      transition:.3s;
      border:1px solid #333;
    }
    .evento-card:hover { background:#2a2a2a; transform:scale(1.02); }
    .evento-card h3 { color:#b44dff; margin-bottom:10px; }
    .evento-card p { margin:5px 0; border-bottom:1px solid #333; padding-bottom:5px; }

    @media (max-width: 768px) {
      .filtros { flex-direction:column; align-items:stretch; }
      nav { flex-direction:column; gap:10px; }
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
      <span class="user-name" onclick="toggleMenu()">
        Olá, <?= htmlspecialchars($usuario_nome) ?> ▼
      </span>
      <div class="dropdown" id="menuDropdown">
        <a href="meus_ingressos.php">Meus Ingressos</a>
        <a href="meus_eventos.php">Meus Eventos</a>
        <form action="logout.php" method="POST">
          <button type="submit">Sair</button>
        </form>
      </div>
    </div>
  </div>
</header>


<div class="container">
  <h2>Eventos Disponíveis</h2>

  <!-- FILTRO POR DATA -->
  <form method="GET" class="filtros">
    <div>
      <label for="data">Data do Evento</label>
      <input type="date" id="data" name="data" value="<?= htmlspecialchars($data) ?>">
    </div>
    <div>
      <button type="submit">Filtrar</button>
      <a href="Home.php" style="background:#555; color:white; padding:10px 15px; border-radius:6px; text-decoration:none; font-weight:bold;">Limpar filtros</a>
    </div>
  </form>

  <!-- LISTAGEM -->
  <div class="eventos">
    <?php
    if ($result_eventos && mysqli_num_rows($result_eventos) > 0) {
        while ($evento = mysqli_fetch_assoc($result_eventos)) {
            $preco = $evento['preco_min'] ? "A partir de R$ " . number_format($evento['preco_min'], 2, ',', '.') : "Grátis";
            echo "<a href='evento.php?id={$evento['EVE_ID']}' class='evento-card'>
                    <h3>{$evento['EVE_NOME']}</h3>
                    <p><strong>Tipo:</strong> {$evento['EVE_TIPO']}</p>
                    <p><strong>Música:</strong> {$evento['EVE_MUSICA']}</p>
                    <p><strong>Local:</strong> {$evento['EVE_LOCAL']}</p>
                    <p><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($evento['EVE_DATA'])) . "</p>
                    <p><strong>Preço:</strong> $preco</p>
                  </a>";
        }
    } else {
        if (!empty($data)) {
            echo "<p>Nenhum evento encontrado nessa data.</p>";
        } else {
            echo "<p>Nenhum evento disponível.</p>";
        }
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
