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
  <style>
    body {
  font-family: Arial, sans-serif;
  background: #0b0010;
  color: white;
  text-align: center;
  padding: 50px;
  margin: 0;
}

/* 🔹 Caixa central */
.box {
  background: #1c1c1c;
  padding: 30px;
  border-radius: 10px;
  display: inline-block;
  width: 100%;
  max-width: 400px; /* responsivo */
  box-sizing: border-box;
}

h1 {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-top: 15px;
  text-align: left;
}

input, select {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border-radius: 5px;
  border: none;
  font-size: 15px;
}

button {
  margin-top: 20px;
  padding: 12px 18px;
  background: #8000c8;
  color: white;
  font-size: 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s ease;
}

button:hover {
  background: #a44dff;
}

#qrcode {
  margin-top: 20px;
  display: none;
}

/* 🔹 Responsividade */
@media (max-width: 768px) {
  body {
    padding: 20px;
  }
  .box {
    padding: 20px;
  }
  h1 {
    font-size: 22px;
  }
  button {
    width: 100%;
  }
}

@media (max-width: 480px) {
  h1 {
    font-size: 20px;
  }
  input, select {
    font-size: 14px;
  }
  button {
    font-size: 15px;
  }
}

  </style>
</head>
<body>
  <div class="box">
    <h1>Pagamento</h1>
    <p>Valor total: <strong>R$ <?= number_format($valor_total, 2, ',', '.') ?></strong></p>

    <form action="processar_pagamento.php" method="POST">
      <input type="hidden" name="valor_total" value="<?= $valor_total ?>">

      <label for="nome">Nome no Cartão</label>
      <input type="text" id="nome" name="nome">

      <label for="numero">Número do Cartão</label>
      <input type="text" id="numero" name="numero" maxlength="16">

      <label for="validade">Validade</label>
      <input type="month" id="validade" name="validade">

      <label for="cvv">CVV</label>
      <input type="text" id="cvv" name="cvv" maxlength="3">

      <label for="forma">Forma de Pagamento</label>
      <select id="forma" name="forma" onchange="mostrarQrCode()">
        <option value="credito">Cartão de Crédito</option>
        <option value="debito">Cartão de Débito</option>
        <option value="pix">Pix</option>
        <option value="boleto">Boleto</option>
      </select>

      <!-- QR Code aparece só se for Pix -->
      <div id="qrcode">
        <h3>Escaneie o QR Code para pagar com Pix</h3>
        <?php
          $pix_code = "00020126580014BR.GOV.BCB.PIX0136chavepix@festify.com5204000053039865802BR5925FESTIFY PAGAMENTOS LTDA6009SAO PAULO62070503***6304ABCD";
          $url_qr = "https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=" . urlencode($pix_code);
        ?>
        <img src="<?= $url_qr ?>" alt="QR Code Pix">
        <p><small>Código copia e cola:</small></p>
        <textarea readonly style="width:100%;height:80px;"><?= $pix_code ?></textarea>
      </div>

      <button type="submit">Confirmar Pagamento</button>
    </form>
  </div>

  <script>
    function mostrarQrCode() {
      const forma = document.getElementById("forma").value;
      const qr = document.getElementById("qrcode");
      const inputsCartao = ["nome","numero","validade","cvv"];

      if (forma === "pix") {
        qr.style.display = "block";
        // Desabilita campos de cartão
        inputsCartao.forEach(id => document.getElementById(id).disabled = true);
      } else {
        qr.style.display = "none";
        // Reabilita campos de cartão
        inputsCartao.forEach(id => document.getElementById(id).disabled = false);
      }
    }
  </script>
</body>
</html>
