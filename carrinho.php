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

// Remover item do carrinho
if (isset($_GET['remover'])) {
    $car_id = intval($_GET['remover']);
    $sql_del = "DELETE FROM TB_CARRINHO WHERE CAR_ID = '$car_id' AND CLI_ID = '$usuario_id'";
    mysqli_query($conexao, $sql_del);
    header("Location: carrinho.php");
    exit;
}

// Buscar itens do carrinho
$sql_carrinho = "SELECT c.CAR_ID, c.CAR_QUANTIDADE, c.CAR_VALOR, c.CAR_MEIA_INTEIRA, 
                        i.ING_TIPO, i.ING_VALOR, e.EVE_NOME 
                 FROM TB_CARRINHO c
                 JOIN TB_INGRESSO i ON c.ING_ID = i.ING_ID
                 JOIN TB_EVENTO e ON i.EVE_ID = e.EVE_ID
                 WHERE c.CLI_ID = '$usuario_id'";
$result_carrinho = mysqli_query($conexao, $sql_carrinho);

$total = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Meu Carrinho - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
body {
  font-family: Arial, sans-serif;
  background:#0b0010;
  color:white;
  margin:0;
}

header {
  background:#0a0013;
  padding:15px 20px;
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

.container {
  max-width:900px;
  margin:30px auto;
  background:#1c1c1c;
  padding:30px;
  border-radius:12px;
}

h1 {
  color:#ffcc00;
  margin-bottom:20px;
  text-align:center;
}

/* TABELA padrão (desktop) */
table {
  width:100%;
  border-collapse:collapse;
  margin-top:20px;
}

th, td {
  padding:12px;
  text-align:left;
  border-bottom:1px solid #444;
}

th {
  background:#2a2a2a;
}

.btn-remover {
  color:#ff4d4d;
  text-decoration:none;
  font-weight:bold;
}

.btn-remover:hover {
  text-decoration:underline;
}

.btn-pagar {
  background:#ffcc00;
  padding:12px 20px;
  border:none;
  border-radius:6px;
  font-weight:bold;
  cursor:pointer;
  margin-top:20px;
  display:block;
  width:100%;
}

.btn-pagar:hover {
  background:#ffdb4d;
}

/* 📱 Mobile: tabela vira cards */
@media (max-width: 768px) {
  nav {
    position:static;
    transform:none;
    margin:10px 0;
    gap:15px;
  }

  .user-area {
    width:100%;
    justify-content:space-between;
  }

  table, thead, tbody, th, td, tr {
    display:block;
  }

  thead {
    display:none;
  }

  tr {
    background:#2a2a2a;
    margin-bottom:15px;
    border-radius:8px;
    padding:12px;
  }

  td {
    border:none;
    display:flex;
    justify-content:space-between;
    padding:8px 5px;
  }

  td::before {
    content: attr(data-label);
    font-weight:bold;
    color:#ffcc00;
  }

  .btn-pagar {
    font-size:16px;
    padding:14px;
  }
}

@media (max-width: 480px) {
  h1 {
    font-size:20px;
  }

  .logo {
    font-size:18px;
  }

  .btn-carrinho {
    padding:6px 10px;
    font-size:13px;
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
  <h1>Meu Carrinho</h1>

  <?php if ($result_carrinho && mysqli_num_rows($result_carrinho) > 0): ?>
    <table>
      <tr>
        <th>Evento</th>
        <th>Ingresso</th>
        <th>Tipo</th>
        <th>Quantidade</th>
        <th>Preço Unitário</th>
        <th>Total</th>
        <th>Ação</th>
      </tr>
      <?php while ($c = mysqli_fetch_assoc($result_carrinho)): 
          $preco = ($c['CAR_MEIA_INTEIRA'] === 'meia') ? $c['ING_VALOR'] / 2 : $c['ING_VALOR'];
          $subtotal = $preco * $c['CAR_QUANTIDADE'];
          $total += $subtotal;
      ?>
      <tr>
        <td><?php echo htmlspecialchars($c['EVE_NOME']); ?></td>
        <td><?php echo htmlspecialchars($c['ING_TIPO']); ?></td>
        <td><?php echo ucfirst($c['CAR_MEIA_INTEIRA']); ?></td>
        <td><?php echo $c['CAR_QUANTIDADE']; ?></td>
        <td>R$ <?php echo number_format($preco, 2, ',', '.'); ?></td>
        <td>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
        <td><a href="carrinho.php?remover=<?php echo $c['CAR_ID']; ?>" class="btn-remover">Remover</a></td>
      </tr>
      <?php endwhile; ?>
    </table>

    <h2>Total: R$ <?php echo number_format($total, 2, ',', '.'); ?></h2>
    <form action="pagamento.php" method="POST">
      <input type="hidden" name="valor_total" value="<?php echo $total; ?>">
      <button type="submit" class="btn-pagar">Finalizar Pagamento</button>
    </form>

  <?php else: ?>
    <p>Seu carrinho está vazio.</p>
  <?php endif; ?>
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
