<?php
session_start();
include("config.php");

// 🔹 Verifica se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// 🔹 Busca nome do usuário
$sql_user = "SELECT CLI_NOME FROM TB_CLIENTE WHERE CLI_ID = '$usuario_id'";
$result_user = mysqli_query($conexao, $sql_user);
$usuario_nome = "";
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $row_user = mysqli_fetch_assoc($result_user);
    $usuario_nome = $row_user['CLI_NOME'];
}

// 🔹 Busca ingressos comprados
$sql = "SELECT V.VEI_ID, V.VEI_QUANTIDADE, V.VEI_VALOR, V.VEI_TIPO, V.VEI_MEIA_INTEIRA, V.VEI_DATA_VENDA, 
               I.ING_TIPO, E.EVE_NOME, E.EVE_CANTOR, E.EVE_LOCAL, E.EVE_DATA
        FROM TB_VENDA_INGRESSO V
        JOIN TB_INGRESSO I ON V.ING_ID = I.ING_ID
        JOIN TB_EVENTO E ON I.EVE_ID = E.EVE_ID
        WHERE V.CLI_ID = '$usuario_id'
        ORDER BY V.VEI_DATA_VENDA DESC";

$result = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meus Ingressos - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script> <!-- 🔹 Biblioteca de QRCode -->
  <style>
    body { font-family: Arial, sans-serif; background-color: #0d001f; color: #fff; margin: 0; padding: 0; }
    header { background-color: #0a0013; padding: 15px 40px; display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; }
    .logo { font-size: 22px; font-weight: bold; color: white; text-decoration: none; }
    nav { display: flex; gap: 20px; justify-self: center; }
    nav a { color: white; text-decoration: none; font-weight: bold; transition: color 0.3s; }
    nav a:hover { color: #ffb800; }
    .user-area { display: flex; align-items: center; gap: 15px; justify-self: end; }
    .btn-carrinho { padding: 8px 14px; background: #8000c8; border-radius: 6px; color: white; font-weight: bold; text-decoration: none; transition: background 0.3s; }
    .btn-carrinho:hover { background: #a44dff; }
    .user-menu { position: relative; }
    .user-name { font-weight: bold; color: #a76dff; cursor: pointer; }
    .dropdown { display: none; position: absolute; right: 0; background: #1c1c1c; border-radius: 6px; margin-top: 8px; padding: 10px; min-width: 160px; z-index: 100; }
    .dropdown a, .dropdown button { display: block; padding: 8px; color: white; text-decoration: none; background: none; border: none; text-align: left; width: 100%; cursor: pointer; }
    .dropdown a:hover, .dropdown button:hover { background: #2a2a2a; }

    .container { max-width: 900px; margin: 40px auto; padding: 20px; background: #1a1a1a; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.6); }
    h2 { color: #f0c000; text-align: center; margin-bottom: 20px; }

    .ingressos { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
    .ingresso { background: #2a2a2a; padding: 20px; border-radius: 10px; transition: transform 0.2s ease, background 0.3s ease; cursor: pointer; }
    .ingresso:hover { transform: scale(1.03); background: #333; }
    .ingresso h3 { margin: 0 0 10px 0; color: #a855f7; }
    .detalhes { font-size: 14px; line-height: 1.6; }

    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; z-index: 200; }
    .modal-content { background: #1a1a1a; padding: 25px; border-radius: 12px; max-width: 500px; width: 90%; color: white; text-align: left; position: relative; }
    .modal-content h3 { color: #f0c000; margin-bottom: 15px; }
    .close-btn { position: absolute; top: 10px; right: 15px; font-size: 20px; cursor: pointer; color: #aaa; }
    .close-btn:hover { color: #fff; }
    #qrcode { margin-top: 15px; text-align: center; }
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
        Olá, <?php echo $_SESSION['usuario_nome'] ?? ''; ?> ▼
      </span>
      <div class="dropdown" id="menuDropdown">
        <a href="meus_ingressos.php">Meus Ingressos</a>
        <a href="meus_eventos.php">Meus Eventos</a> <!-- 🔹 novo -->
        <form action="logout.php" method="POST">
          <button type="submit">Sair</button>
        </form>
      </div>
    </div>
  </div>
</header>


<div class="container">
    <h2>Meus Ingressos</h2>
    <div class="ingressos">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="ingresso" onclick="abrirModal('<?php echo htmlspecialchars($row['EVE_NOME']); ?>',
                                                         '<?php echo htmlspecialchars($row['EVE_CANTOR']); ?>',
                                                         '<?php echo htmlspecialchars($row['EVE_LOCAL']); ?>',
                                                         '<?php echo date("d/m/Y H:i", strtotime($row['EVE_DATA'])); ?>',
                                                         '<?php echo htmlspecialchars($row['VEI_TIPO']); ?>',
                                                         '<?php echo $row['VEI_MEIA_INTEIRA']; ?>',
                                                         '<?php echo $row['VEI_QUANTIDADE']; ?>',
                                                         '<?php echo number_format($row['VEI_VALOR'], 2, ',', '.'); ?>',
                                                         '<?php echo date("d/m/Y H:i", strtotime($row['VEI_DATA_VENDA'])); ?>')">
                    <h3><?php echo htmlspecialchars($row['EVE_NOME']); ?></h3>
                    <div class="detalhes">
                        <p><b>Cantor:</b> <?php echo htmlspecialchars($row['EVE_CANTOR']); ?></p>
                        <p><b>Data:</b> <?php echo date("d/m/Y", strtotime($row['EVE_DATA'])); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; margin-top:20px;">Você ainda não comprou nenhum ingresso 🎟️</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal" id="modalIngresso">
  <div class="modal-content">
    <span class="close-btn" onclick="fecharModal()">&times;</span>
    <h3 id="modalTitulo"></h3>
    <p><b>Cantor:</b> <span id="modalCantor"></span></p>
    <p><b>Local:</b> <span id="modalLocal"></span></p>
    <p><b>Data do Evento:</b> <span id="modalData"></span></p>
    <hr>
    <p><b>Ingresso:</b> <span id="modalTipo"></span> (<span id="modalMeia"></span>)</p>
    <p><b>Quantidade:</b> <span id="modalQtd"></span></p>
    <p><b>Valor Pago:</b> R$ <span id="modalValor"></span></p>
    <p><b>Data da Compra:</b> <span id="modalCompra"></span></p>
    <hr>
    <p><b>Token de Validação:</b> <span id="modalToken"></span></p>
    <div id="qrcode"></div>
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

// 🔹 Funções do Modal com Token + QRCode
function abrirModal(titulo, cantor, local, data, tipo, meia, qtd, valor, compra) {
  document.getElementById('modalTitulo').innerText = titulo;
  document.getElementById('modalCantor').innerText = cantor;
  document.getElementById('modalLocal').innerText = local;
  document.getElementById('modalData').innerText = data;
  document.getElementById('modalTipo').innerText = tipo;
  document.getElementById('modalMeia').innerText = meia;
  document.getElementById('modalQtd').innerText = qtd;
  document.getElementById('modalValor').innerText = valor;
  document.getElementById('modalCompra').innerText = compra;

  // Gera um token aleatório
  let token = 'TK-' + Math.random().toString(36).substr(2, 10).toUpperCase();
  document.getElementById('modalToken').innerText = token;

  // Gera o QRCode
  document.getElementById('qrcode').innerHTML = ""; // limpar anterior
  new QRCode(document.getElementById("qrcode"), {
      text: token,
      width: 150,
      height: 150
  });

  document.getElementById('modalIngresso').style.display = 'flex';
}

function fecharModal() {
  document.getElementById('modalIngresso').style.display = 'none';
}
</script>
</body>
</html>
