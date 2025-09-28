<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Busca ingressos comprados pelo usuário
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
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
   body {
    font-family: Arial, sans-serif;
    background-color: #0d001f;
    color: #fff;
    margin: 0;
    padding: 0;
}

/* 🔹 Header */
header {
    background-color: #0a0014;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap; /* permite quebrar em telas pequenas */
}
header h1 {
    margin: 0;
    color: white;
    font-size: 22px;
}
nav {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}
nav a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s;
}
nav a:hover {
    color: #f0c000;
}

/* 🔹 Container principal */
.container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #1a1a1a;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.6);
}
h2 {
    color: #f0c000;
    text-align: center;
    margin-bottom: 20px;
}

/* 🔹 Ingressos */
.ingressos {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}
.ingresso {
    background: #2a2a2a;
    padding: 20px;
    border-radius: 10px;
    transition: transform 0.2s ease, background 0.3s ease;
}
.ingresso:hover {
    transform: scale(1.03);
    background: #333;
}
.ingresso h3 {
    margin: 0 0 10px 0;
    color: #a855f7;
}
.detalhes {
    font-size: 14px;
    line-height: 1.6;
}

/* 🔹 Responsividade */
@media (max-width: 768px) {
    header {
        flex-direction: column;
        align-items: flex-start;
        padding: 15px 20px;
    }
    nav {
        justify-content: center;
        margin-top: 10px;
    }
    .container {
        margin: 20px;
        padding: 15px;
    }
    h2 {
        font-size: 20px;
    }
    .detalhes {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    header h1 {
        font-size: 18px;
    }
    nav a {
        font-size: 14px;
    }
    .ingresso {
        padding: 15px;
    }
    .detalhes {
        font-size: 12px;
    }
}

  </style>
</head>
<body>
<header>
    <h1>Festify</h1>
    <nav>
        <a href="Home.php">Home</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<div class="container">
    <h2>Meus Ingressos</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="ingresso">
                <h3><?php echo htmlspecialchars($row['EVE_NOME']); ?></h3>
                <div class="detalhes">
                    <p><b>Cantor:</b> <?php echo htmlspecialchars($row['EVE_CANTOR']); ?></p>
                    <p><b>Local:</b> <?php echo htmlspecialchars($row['EVE_LOCAL']); ?></p>
                    <p><b>Data do Evento:</b> <?php echo date("d/m/Y", strtotime($row['EVE_DATA'])); ?></p>
                    <hr>
                    <p><b>Ingresso:</b> <?php echo htmlspecialchars($row['VEI_TIPO']); ?> (<?php echo $row['VEI_MEIA_INTEIRA']; ?>)</p>
                    <p><b>Quantidade:</b> <?php echo $row['VEI_QUANTIDADE']; ?></p>
                    <p><b>Valor Pago:</b> R$ <?php echo number_format($row['VEI_VALOR'], 2, ',', '.'); ?></p>
                    <p><b>Data da Compra:</b> <?php echo date("d/m/Y H:i", strtotime($row['VEI_DATA_VENDA'])); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; margin-top:20px;">Você ainda não comprou nenhum ingresso 🎟️</p>
    <?php endif; ?>
</div>
</body>
</html>
