<?php
include("config.php");

// Buscar eventos válidos (até 1 dia após a data)
$data_limite = date('Y-m-d H:i:s', strtotime('-1 day'));
$sql_eventos = "
    SELECT e.*, 
           MIN(i.ING_VALOR) AS preco_min
    FROM TB_EVENTO e
    LEFT JOIN TB_INGRESSO i ON e.EVE_ID = i.EVE_ID
    WHERE e.EVE_DATA >= '$data_limite'
    GROUP BY e.EVE_ID
    ORDER BY e.EVE_ID DESC
";
$result_eventos = mysqli_query($conexao, $sql_eventos);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsivo -->
  <style>
   /*  ESTILO GERAL  */
body {
  font-family: Arial, sans-serif;
  background:#0b0010;
  color:white;
  margin:0;
}

/*  HEADER  */
header {
  background:#0a0013;
  padding:15px 40px;
  display:flex;
  align-items:center;
  justify-content:space-between;
}

.logo {
  font-size:22px;
  font-weight:bold;
  color:white;
  text-decoration:none;
}

nav {
  display:flex;
  gap:20px;
  justify-content:center;
  flex:1; /* centraliza o menu no meio */
}

nav a {
  color:white;
  text-decoration:none;
  font-weight:bold;
}

nav a:hover {
  color:#ffb800;
}

/*  CONTEÚDO PRINCIPAL  */
.container {
  padding:30px;
  text-align:center;
}

h2 {
  color:#ffcc00;
}

/* CARDS DE EVENTOS */
.eventos {
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(250px,1fr));
  gap:20px;
  margin-top:30px;
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

.evento-card:hover {
  background:#2a2a2a;
  transform:scale(1.02);
}

.evento-card h3 {
  color:#b44dff;
  margin-bottom:10px;
}

.evento-card p {
  margin:5px 0;
  border-bottom:1px solid #333;
  padding-bottom:5px;
}

/* RESPONSIVIDADE */
@media (max-width: 768px) {
  header {
    flex-direction:column;
    gap:10px;
  }

  nav {
    flex-direction:column;
    align-items:center;
  }

  .eventos {
    grid-template-columns:1fr;
  }
}

  </style>
</head>
<body>
<header>
  <a href="homesemlogin.php" class="logo">Festify</a>
  <nav>
    <a href="homesemlogin.php">Home</a>
    <a href="login.php">Entrar</a>
    <a href="Cadastro.php">Cadastrar</a>
  </nav>
</header>

<div class="container">
  <h1>Bem-vindo ao Festify!</h1>
  <h2>Eventos Disponíveis</h2>
  <div class="eventos">
    <?php
    if ($result_eventos && mysqli_num_rows($result_eventos) > 0) {
        while ($evento = mysqli_fetch_assoc($result_eventos)) {
            $preco = $evento['preco_min'] ? "A partir de R$ " . number_format($evento['preco_min'], 2, ',', '.') : "Grátis";
            echo "<a href='login.php' class='evento-card'>
                    <h3>{$evento['EVE_NOME']}</h3>
                    <p><strong>Tipo:</strong> {$evento['EVE_TIPO']}</p>
                    <p><strong>Música:</strong> {$evento['EVE_MUSICA']}</p>
                    <p><strong>Local:</strong> {$evento['EVE_LOCAL']}</p>
                    <p><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($evento['EVE_DATA'])) . "</p>
                    <p><strong>Preço:</strong> $preco</p>
                  </a>";
        }
    } else {
        echo "<p>Nenhum evento disponível.</p>";
    }
    ?>
  </div>
</div>
</body>
</html>
