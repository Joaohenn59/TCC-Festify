<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// 🔹 Nome do usuário logado
$sql_user = "SELECT CLI_NOME FROM TB_CLIENTE WHERE CLI_ID = '$usuario_id'";
$result_user = mysqli_query($conexao, $sql_user);
$usuario_nome = "";
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $row_user = mysqli_fetch_assoc($result_user);
    $usuario_nome = $row_user['CLI_NOME'];
}

// 🔹 Remover item do carrinho (não mexe mais no estoque!)
if (isset($_GET['remover'])) {
    $car_id = intval($_GET['remover']);
    $sql_remover = "DELETE FROM TB_CARRINHO WHERE CAR_ID = '$car_id' AND CLI_ID = '$usuario_id'";
    mysqli_query($conexao, $sql_remover);

    echo "<script>alert('Item removido do carrinho!'); window.location='carrinho.php';</script>";
    exit;
}

// 🔹 Buscar itens do carrinho
$sql = "SELECT c.*, i.ING_TIPO, i.ING_VALOR, e.EVE_NOME
        FROM TB_CARRINHO c
        JOIN TB_INGRESSO i ON c.ING_ID = i.ING_ID
        JOIN TB_EVENTO e ON i.EVE_ID = e.EVE_ID
        WHERE c.CLI_ID = '$usuario_id'";
$result = mysqli_query($conexao, $sql);

// 🔹 Calcular valor total
$valor_total = 0;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $valor_total += $row['CAR_VALOR'];
    }
    mysqli_data_seek($result, 0);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Carrinho - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
    body { font-family: Arial, sans-serif; background:#0b0010; color:white; margin:0; padding:0; }

    /* 🔹 Header */
    header { background:#0a0013; padding:15px 40px; display:grid; grid-template-columns:1fr auto 1fr; align-items:center; }
    .logo { font-size:22px; font-weight:bold; color:white; text-decoration:none; justify-self:start; }
    nav { display:flex; gap:20px; justify-self:center; }
    nav a { color:white; text-decoration:none; font-weight:bold; transition:.3s; }
    nav a:hover { color:#ffb800; }

    .user-area { display:flex; align-items:center; gap:15px; justify-self:end; }
    .btn-carrinho { padding:8px 14px; background:#8000c8; border-radius:6px; color:white; font-weight:bold; text-decoration:none; transition:.3s; }
    .btn-carrinho:hover { background:#a44dff; }
    .user-menu { position:relative; }
    .user-name { font-weight:bold; color:#a76dff; cursor:pointer; }
    .dropdown { display:none; position:absolute; right:0; background:#1c1c1c; border-radius:6px; margin-top:8px; padding:10px; min-width:160px; z-index:100; }
    .dropdown a, .dropdown button { display:block; padding:8px; color:white; text-decoration:none; background:none; border:none; text-align:left; width:100%; cursor:pointer; }
    .dropdown a:hover, .dropdown button:hover { background:#2a2a2a; }

    /* 🔹 Container */
    .container { max-width:900px; margin:30px auto; background:#1c1c1c; padding:20px; border-radius:12px; }
    h1 { color:#ffcc00; text-align:center; }

    table { width:100%; border-collapse:collapse; margin-top:20px; }
    th, td { padding:12px; border-bottom:1px solid #333; text-align:center; }
    th { background:#2a2a2a; color:#ffcc00; }
    tr:hover { background:#2a2a2a; }

    .btn { padding:8px 14px; border-radius:6px; color:white; text-decoration:none; font-weight:bold; transition:.3s; }
    .btn-remover { background:#c80000; }
    .btn-remover:hover { background:#ff3333; }
    .btn-pagamento { display:block; text-align:center; background:#8000c8; margin-top:20px; padding:12px; }
    .btn-pagamento:hover { background:#a44dff; }
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
      <span class="user-name" onclick="toggleMenu()">Olá, <?= htmlspecialchars($usuario_nome) ?> ▼</span>
      <div class="dropdown" id="menuDropdown">
        <a href="meus_ingressos.php">Meus Ingressos</a>
        <form action="logout.php" method="POST"><button type="submit">Sair</button></form>
      </div>
    </div>
  </div>
</header>

<div class="container">
  <h1>Meu Carrinho</h1>

  <?php if ($result && mysqli_num_rows($result) > 0): ?>
    <table>
      <tr>
        <th>Evento</th>
        <th>Ingresso</th>
        <th>Meia/Inteira</th>
        <th>Quantidade</th>
        <th>Valor</th>
        <th>Ações</th>
      </tr>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= htmlspecialchars($row['EVE_NOME']) ?></td>
          <td><?= htmlspecialchars($row['ING_TIPO']) ?></td>
          <td><?= htmlspecialchars($row['CAR_MEIA_INTEIRA']) ?></td>
          <td><?= $row['CAR_QUANTIDADE'] ?></td>
          <td>R$ <?= number_format($row['CAR_VALOR'], 2, ',', '.') ?></td>
          <td>
            <a href="carrinho.php?remover=<?= $row['CAR_ID'] ?>" class="btn btn-remover">Remover</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>

    <h2 style="text-align:right; margin-top:20px;">
      Total: R$ <?= number_format($valor_total, 2, ',', '.') ?>
    </h2>

    <form method="POST" action="pagamento.php">
      <input type="hidden" name="valor_total" value="<?= $valor_total ?>">
      <button type="submit" class="btn btn-pagamento">Finalizar Pagamento</button>
    </form>

  <?php else: ?>
    <p style="text-align:center; margin-top:20px;">Seu carrinho está vazio 🛒</p>
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
