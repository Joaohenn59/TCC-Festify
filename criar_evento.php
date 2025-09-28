<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/* Buscar nome do usuário */
$sql_user = "SELECT CLI_NOME FROM TB_CLIENTE WHERE CLI_ID = '$usuario_id' LIMIT 1";
$result_user = mysqli_query($conexao, $sql_user);
$usuario_nome = "Usuário";
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $usuario_nome = mysqli_fetch_assoc($result_user)['CLI_NOME'];
}

/* Criação do evento */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Campos do evento
    $nome       = $_POST['nome'] ?? '';
    $cantor     = $_POST['cantor'] ?? '';
    $local      = $_POST['local'] ?? '';
    $data       = $_POST['data'] ?? '';
    $hora       = $_POST['hora'] ?? '';
    $tipo       = $_POST['tipo'] ?? '';
    $descricao  = $_POST['descricao'] ?? '';

    // Monta datetime
    $dataHora = trim($data . ' ' . $hora);

    // Insere o evento com o criador em EVE_CRIADOR
    $sql_evento = "
        INSERT INTO TB_EVENTO
            (EVE_NOME, EVE_CANTOR, EVE_LOCAL, EVE_DATA, EVE_TIPO, EVE_DESCRICAO, EVE_CRIADOR)
        VALUES
            ('$nome', '$cantor', '$local', '$dataHora', '$tipo', '$descricao', '$usuario_id')";
    
    if (mysqli_query($conexao, $sql_evento)) {
        $evento_id = mysqli_insert_id($conexao);

        // Ingressos (podem ter vários)
        if (!empty($_POST['ingresso_tipo'])) {
            foreach ($_POST['ingresso_tipo'] as $i => $tipo_ingresso) {
                if (trim($tipo_ingresso) === '') continue;

                $valor       = $_POST['ingresso_valor'][$i] ?? 0;
                $quantidade  = $_POST['ingresso_quantidade'][$i] ?? 0;
                $beneficios  = $_POST['ingresso_beneficios'][$i] ?? '';

                $sql_ingresso = "
                    INSERT INTO TB_INGRESSO
                        (ING_TIPO, ING_VALOR, ING_QUANTIDADE, ING_BENEFICIOS, EVE_ID)
                    VALUES
                        ('$tipo_ingresso', '$valor', '$quantidade', '$beneficios', '$evento_id')";
                mysqli_query($conexao, $sql_ingresso);
            }
        }

        echo "<script>alert('Evento criado com sucesso!'); window.location='meus_eventos.php';</script>";
        exit;
    } else {
        $err = mysqli_error($conexao);
        echo "<script>alert('Erro ao criar evento: ".htmlspecialchars($err)."');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Criar Evento - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --bg: #0b0010;
      --card: #1c1c1c;
      --card2:#2a2a2a;
      --accent:#8000c8;
      --accent2:#a44dff;
      --ink:#fff;
      --ink2:#ffcc00;
    }
    *{box-sizing:border-box}
    body{font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:var(--ink);margin:0}

    /* Header */
    header{
      background:#0a0013;
      padding:15px 40px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      position:relative;
      flex-wrap:wrap;
    }
    .logo{font-size:22px;font-weight:bold;color:#fff;text-decoration:none}
    nav{position:absolute;left:50%;transform:translateX(-50%);display:flex;gap:20px}
    nav a{color:#fff;text-decoration:none;font-weight:bold}
    nav a:hover{color:#ffb800}
    .user-area{display:flex;align-items:center;gap:15px}
    .btn-carrinho{padding:8px 14px;background:var(--accent);border-radius:6px;color:#fff;font-weight:bold;text-decoration:none}
    .btn-carrinho:hover{background:var(--accent2)}
    .user-menu{position:relative}
    .user-name{font-weight:bold;color:#a76dff;cursor:pointer}
    .dropdown{display:none;position:absolute;right:0;background:#1c1c1c;border-radius:6px;margin-top:8px;padding:10px;min-width:180px;z-index:20}
    .dropdown a,.dropdown button{display:block;padding:8px 10px;color:#fff;text-decoration:none;background:none;border:none;text-align:left;width:100%;cursor:pointer}
    .dropdown a:hover,.dropdown button:hover{background:#2a2a2a}

    /* Conteúdo */
    .container{max-width:800px;margin:30px auto;background:var(--card);padding:30px;border-radius:12px;box-shadow:0 6px 24px rgba(0,0,0,.5)}
    h1{text-align:center;color:var(--ink2);margin-top:0}
    form{display:flex;flex-direction:column;gap:12px}
    label{font-weight:bold}
    input,textarea{
      width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.12);
      background:#333;color:#fff;font-size:15px;outline:none
    }
    input:focus,textarea:focus{border-color:var(--accent2);box-shadow:0 0 0 2px rgba(164,77,255,.25)}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .ingresso-box{background:var(--card2);padding:14px;border-radius:10px;margin-top:6px}
    .add-btn{
      background:#ffcc00;color:#000;border:none;border-radius:8px;padding:10px 12px;
      font-weight:bold;cursor:pointer;align-self:flex-start
    }
    .add-btn:hover{background:#ffdb4d}
    .submit{
      margin-top:6px;background:linear-gradient(90deg,var(--accent),var(--accent2));
      border:none;border-radius:10px;color:#fff;font-weight:bold;padding:12px;cursor:pointer
    }
    .submit:hover{opacity:.9}

    @media (max-width:800px){
      nav{position:static;transform:none;margin-top:8px}
      .row{grid-template-columns:1fr}
      header{gap:10px;justify-content:center}
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
    <a class="btn-carrinho" href="carrinho.php">🛒 Carrinho</a>
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
  <h1>Criar Evento</h1>

  <form method="POST">
    <label>Nome do Evento</label>
    <input type="text" name="nome" required>

    <div class="row">
      <div>
        <label>Cantor</label>
        <input type="text" name="cantor" required>
      </div>
      <div>
        <label>Local</label>
        <input type="text" name="local" required>
      </div>
    </div>

    <div class="row">
      <div>
        <label>Data</label>
        <input type="date" name="data" required>
      </div>
      <div>
        <label>Hora</label>
        <input type="time" name="hora" required>
      </div>
    </div>

    <label>Tipo</label>
    <input type="text" name="tipo" placeholder="Ex.: Show, Festival..." required>

    <label>Descrição</label>
    <textarea name="descricao" rows="3" placeholder="Detalhes do evento..."></textarea>

    <h2 style="color:#ffcc00;margin:8px 0 0 0;">Ingressos</h2>
    <p style="margin:6px 0 10px 0;color:#cfcfcf;font-size:14px">Cadastre um ou mais tipos de ingresso:</p>

    <div id="ingressos">
      <div class="ingresso-box">
        <div class="row">
          <div>
            <label>Tipo de Ingresso</label>
            <input type="text" name="ingresso_tipo[]" placeholder="Ex.: Pista, VIP" required>
          </div>
          <div>
            <label>Valor (R$)</label>
            <input type="number" step="0.01" name="ingresso_valor[]" placeholder="0,00" required>
          </div>
        </div>
        <div class="row">
          <div>
            <label>Quantidade</label>
            <input type="number" name="ingresso_quantidade[]" min="0" placeholder="0" required>
          </div>
          <div>
            <label>Benefícios</label>
            <input type="text" name="ingresso_beneficios[]" placeholder="Ex.: Open bar, acesso antecipado...">
          </div>
        </div>
      </div>
    </div>

    <button type="button" class="add-btn" onclick="adicionarIngresso()">+ Adicionar outro ingresso</button>

    <button type="submit" class="submit">Criar Evento</button>
  </form>
</div>

<script>
function toggleMenu(){
  const d = document.getElementById('menuDropdown');
  d.style.display = (d.style.display === 'block') ? 'none' : 'block';
}
window.addEventListener('click', function(e){
  const t = e.target;
  const d = document.getElementById('menuDropdown');
  if (!t.classList.contains('user-name') && d && d.style.display === 'block') d.style.display='none';
});

function adicionarIngresso(){
  const wrap = document.getElementById('ingressos');
  const box = document.createElement('div');
  box.className = 'ingresso-box';
  box.innerHTML = `
    <div class="row">
      <div>
        <label>Tipo de Ingresso</label>
        <input type="text" name="ingresso_tipo[]" placeholder="Ex.: Pista, VIP" required>
      </div>
      <div>
        <label>Valor (R$)</label>
        <input type="number" step="0.01" name="ingresso_valor[]" placeholder="0,00" required>
      </div>
    </div>
    <div class="row">
      <div>
        <label>Quantidade</label>
        <input type="number" name="ingresso_quantidade[]" min="0" placeholder="0" required>
      </div>
      <div>
        <label>Benefícios</label>
        <input type="text" name="ingresso_beneficios[]" placeholder="Ex.: Open bar, acesso antecipado...">
      </div>
    </div>`;
  wrap.appendChild(box);
}
</script>
</body>
</html>
