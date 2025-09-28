<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$valor_total = $_POST['valor_total'] ?? 0;

if ($valor_total <= 0) {
    echo "Carrinho vazio ou valor inválido!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Pagamento - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root {
      --bg: #0b0010;
      --accent: #8000c8;
      --accent-2: #a44dff;
      --muted: #cfcfcf;
    }
    body {
      font-family: Arial, sans-serif;
      background: var(--bg);
      color: white;
      margin: 0;
      padding: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .box {
      background: #1c1c1c;
      padding: 26px;
      border-radius: 12px;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.6);
    }

    h1 { margin: 0 0 14px 0; font-size: 22px; color: var(--accent); }
    p.subtitle { margin: 0 0 18px 0; color: var(--muted); font-size: 14px; }

    label { display: block; margin-top: 12px; margin-bottom: 6px; text-align: left; font-size: 13px; }

    input[type="text"], textarea, select {
      width: 100%;
      padding: 11px 12px;
      border-radius: 8px;
      border: 1px solid rgba(255,255,255,0.1);
      background: rgba(255,255,255,0.05);
      color: #fff;
      font-size: 15px;
      outline: none;
    }
    input:focus, select:focus, textarea:focus {
      border-color: var(--accent-2);
      box-shadow: 0 0 0 2px rgba(164,77,255,0.3);
    }

    select {
      background-color: #1c1c1c;
      color: #fff;
      border: 1px solid #a44dff;
    }

    .validade-container { display:flex; gap:10px; }
    .validade-container input { flex:1; text-align:center; }

    .btn {
      margin-top: 20px;
      width: 100%;
      padding: 12px 14px;
      background: linear-gradient(90deg, var(--accent), var(--accent-2));
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      font-size: 15px;
    }
    .btn:hover { opacity: 0.9; }

    #qrcode { margin-top: 16px; display: none; text-align:center; }
    #pixQr { display:inline-block; }
    #pixQrImgFallback { display:none; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); }
    textarea { resize: none; min-height: 78px; }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
</head>
<body>
  <div class="box">
    <h1>Pagamento</h1>
    <p class="subtitle">Valor total: <strong>R$ <?= number_format((float)$valor_total, 2, ',', '.') ?></strong></p>

    <form id="formPagamento" action="processar_pagamento.php" method="POST" onsubmit="return validarFormulario()">
      <input type="hidden" name="valor_total" value="<?= htmlspecialchars($valor_total, ENT_QUOTES) ?>">

      <label for="nome">Nome no Cartão</label>
      <input type="text" id="nome" name="nome" autocomplete="cc-name">

      <label for="numero">Número do Cartão</label>
      <input type="text" id="numero" name="numero" maxlength="19" placeholder="0000 0000 0000 0000" autocomplete="cc-number">

      <label>Validade (MM/AA)</label>
      <div class="validade-container">
        <input type="text" id="mes" name="mes" maxlength="2" placeholder="MM">
        <input type="text" id="ano" name="ano" maxlength="2" placeholder="AA">
      </div>

      <label for="cvv">CVV</label>
      <input type="text" id="cvv" name="cvv" maxlength="4" placeholder="123">

      <label for="forma">Forma de Pagamento</label>
      <select id="forma" name="forma" onchange="mostrarQrCode()">
        <option value="credito">Cartão de Crédito</option>
        <option value="debito">Cartão de Débito</option>
        <option value="pix">Pix</option>
      </select>

      <!-- QR Code do Pix -->
      <div id="qrcode">
        <h3>Escaneie o QR Code (Pix)</h3>
        <?php
          $pix_code = "00020126580014BR.GOV.BCB.PIX0136chavepix@festify.com5204000053039865802BR5925FESTIFY PAGAMENTOS LTDA6009SAO PAULO62070503***6304ABCD";
        ?>
        <div id="pixQr" role="img"></div>
        <img id="pixQrImgFallback" alt="QR Code Pix (fallback)">
        <p><small>Código copia e cola:</small></p>
        <textarea readonly><?= htmlspecialchars($pix_code) ?></textarea>
      </div>

      <button class="btn" type="submit">Confirmar Pagamento</button>
    </form>
  </div>

<script>
  // Máscara para número do cartão
  document.getElementById("numero").addEventListener("input", function(e) {
    let v = e.target.value.replace(/\D/g, "").substring(0,16);
    let formatado = v.replace(/(\d{4})(?=\d)/g, "$1 ");
    e.target.value = formatado;
  });

  // Apenas números nos campos
  ["mes","ano","cvv"].forEach(id => {
    document.getElementById(id).addEventListener("input", function(e) {
      e.target.value = e.target.value.replace(/\D/g, "");
    });
  });

  // QR Code Pix
  const PIX_CODE = <?php echo json_encode($pix_code); ?>;
  function renderPixQr(){
    const qrDiv = document.getElementById('pixQr');
    qrDiv.innerHTML = '';
    new QRCode(qrDiv, { text: PIX_CODE, width: 220, height: 220 });
  }
  function mostrarQrCode(){
    const forma = document.getElementById("forma").value;
    const qr = document.getElementById("qrcode");
    const campos = ["nome","numero","mes","ano","cvv"];
    if (forma === "pix") {
      qr.style.display = "block";
      campos.forEach(id => document.getElementById(id).disabled = true);
      renderPixQr();
    } else {
      qr.style.display = "none";
      campos.forEach(id => document.getElementById(id).disabled = false);
    }
  }
  mostrarQrCode();
</script>
</body>
</html>
 