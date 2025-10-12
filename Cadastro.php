<?php
// Inicia a sessão para gerenciar login e variáveis de usuário
session_start();

// Inclui o arquivo de conexão com o banco de dados
include("config.php");

// Carrega as classes do PHPMailer para envio de emails
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Array para armazenar mensagens de erro
$erros = [];

// Executa o código somente quando o formulário é enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura e trata os dados enviados pelo formulário
    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $telefone = preg_replace('/\D/', '', $_POST['telefone']); // Remove tudo que não for número
    $cpf      = preg_replace('/\D/', '', $_POST['cpf']);       // Mesma coisa para CPF
    $senha    = $_POST['senha'];
    $confirmar= $_POST['confirmar'];

    // ✅ Validações básicas dos campos
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = "Email inválido!";
    }

    if (!preg_match("/^[0-9]{11}$/", $cpf)) {
        $erros['cpf'] = "CPF deve ter 11 números.";
    }

    if (!preg_match("/^[0-9]{10,11}$/", $telefone)) {
        $erros['telefone'] = "Telefone deve ter 10 ou 11 números (DDD+Número).";
    }

    if (strlen($senha) < 6) {
        $erros['senha'] = "A senha deve ter no mínimo 6 caracteres.";
    }

    if ($senha !== $confirmar) {
        $erros['confirmar'] = "As senhas não coincidem.";
    }

    // ✅ Verifica se já existe o mesmo email, CPF ou telefone no banco
    if (empty($erros)) {
        $checkEmail = "SELECT CLI_ID FROM TB_CLIENTE WHERE CLI_EMAIL = '$email'";
        $resEmail = mysqli_query($conexao, $checkEmail);
        if (mysqli_num_rows($resEmail) > 0) {
            $erros['email'] = "Este email já está cadastrado. Faça login ou use outro email.";
        }

        $checkCpf = "SELECT CLI_ID FROM TB_CLIENTE WHERE CLI_CPF = '$cpf'";
        $resCpf = mysqli_query($conexao, $checkCpf);
        if (mysqli_num_rows($resCpf) > 0) {
            $erros['cpf'] = "Este CPF já está cadastrado.";
        }

        $checkTel = "SELECT CLI_ID FROM TB_CLIENTE WHERE CLI_FONE = '$telefone'";
        $resTel = mysqli_query($conexao, $checkTel);
        if (mysqli_num_rows($resTel) > 0) {
            $erros['telefone'] = "Este telefone já está cadastrado.";
        }
    }

    // ✅ Se não houver erros, prossegue para o cadastro
    if (empty($erros)) {
        // Criptografa a senha antes de salvar
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // Insere os dados na tabela de clientes
        $sql = "INSERT INTO TB_CLIENTE (CLI_NOME, CLI_EMAIL, CLI_SENHA, CLI_CPF, CLI_FONE) 
                VALUES ('$nome', '$email', '$hash', '$cpf', '$telefone')";

        if (mysqli_query($conexao, $sql)) {
            // Configuração do envio de email de boas-vindas
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();  // Usa SMTP (mais confiável que mail())
                $mail->Host       = 'smtp.gmail.com';  // Servidor SMTP do Gmail
                $mail->SMTPAuth   = true; // autenticação
                $mail->Username   = 'tccfestify@gmail.com'; // Conta de envio
                $mail->Password   = 'huad ruwb jfwy czwi'; // senha de aplicativo do Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Criptografia TLS via STARTTLS
                $mail->Port       = 587;  // Porta TLS do Gmail

                // Remetente e destinatário
                $mail->setFrom('tccfestify@gmail.com', 'Festify');
                $mail->addAddress($email, $nome);

                // Conteúdo do email em HTML
                $mail->isHTML(true);
                $mail->Subject = 'Bem-vindo ao Festify!';
                $mail->Body    = "<h2>Olá, $nome!</h2><p>Seu cadastro no <b>Festify</b> foi realizado com sucesso. Agora você pode criar e participar de eventos incríveis! 🎶</p>";
                $mail->AltBody = "Olá, $nome! Seu cadastro no Festify foi realizado com sucesso.";

                $mail->send(); // Envia o email
            } catch (Exception $e) {
                // Caso falhe o envio, registra o erro no log
                error_log("Erro ao enviar email: {$mail->ErrorInfo}");
            }

            // Redireciona o usuário para o login após sucesso
            header("Location: login.php?cadastro=sucesso");
            exit;
        } else {
            // Exibe erro geral caso o banco falhe
            $erros['geral'] = "Erro ao cadastrar: " . mysqli_error($conexao);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>

/* ESTILOS VISUAIS GERAIS*/

  body {
  font-family: Arial, sans-serif;
  background:#190016; /* Fundo roxo escuro */
  color:white;
  display:flex;
  justify-content:center;
  align-items:center;
  height:100vh;
  margin:0;
  padding:20px;
}

/* Caixa do formulário */
.form-box {
  background:#1c1c1c;
  padding:30px;
  border-radius:10px;
  width:100%;
  max-width:400px;
  box-shadow:0 0 15px rgba(0,0,0,0.5);
}

/* Título principal */
h1 {
  text-align:center;
  color:#ffcc00; /* Amarelo vibrante */
  margin-bottom:20px;
  font-size:28px;
}

/* Formulário organizado em coluna */
form {
  display:flex;
  flex-direction:column;
  gap:15px;
}

/* Campos de texto */
input {
  padding:12px;
  border:1px solid #555;
  border-radius:6px;
  background:transparent;
  color:white;
  font-size:16px;
}

/* Texto do placeholder mais claro */
input::placeholder {
  color:#aaa;
}

/* Botão de envio */
button {
  padding:12px;
  background:#8000c8;
  color:white;
  font-weight:bold;
  font-size:16px;
  border:none;
  border-radius:6px;
  cursor:pointer;
  transition:background 0.3s ease, transform 0.2s ease;
}

/* Efeito hover no botão */
button:hover {
  background:#a400ff;
  transform:scale(1.03);
}

/* Mensagens de erro */
.erro {
  color:#ff4d4d;
  font-size:13px;
  margin-top:-10px;
}

/* Link para login */
a {
  color:#ccc;
  font-size:14px;
  text-align:center;
  margin-top:10px;
  display:block;
}

a:hover {
  color:#fff;
}

/* Responsividade para celular */
@media (max-width: 480px) {
  .form-box { padding:20px; max-width:95%; }
  h1 { font-size:24px; }
  input, button { font-size:14px; padding:10px; }
  a { font-size:13px; }
}

/* Responsividade para tablet */
@media (max-width: 768px) {
  .form-box { max-width:90%; }
}
</style>
</head>
<body>
  <div class="form-box">
    <h1>Cadastro</h1>
    <!-- Formulário principal -->
    <form id="formCadastro" action="Cadastro.php" method="POST" novalidate>
      <input type="text" name="nome" placeholder="Nome Completo" required>
      <div class="erro"><?php echo $erros['nome'] ?? ""; ?></div>

      <input type="email" name="email" id="email" placeholder="Email" required>
      <div class="erro" id="erroEmail"><?php echo $erros['email'] ?? ""; ?></div>

      <input type="tel" name="telefone" id="telefone" placeholder="Telefone (DDD+Número)" required>
      <div class="erro" id="erroTelefone"><?php echo $erros['telefone'] ?? ""; ?></div>

      <input type="text" name="cpf" id="cpf" placeholder="CPF (000.000.000-00)" required>
      <div class="erro" id="erroCpf"><?php echo $erros['cpf'] ?? ""; ?></div>

      <input type="password" name="senha" id="senha" placeholder="Senha" required>
      <div class="erro" id="erroSenha"><?php echo $erros['senha'] ?? ""; ?></div>

      <input type="password" name="confirmar" id="confirmar" placeholder="Confirmar Senha" required>
      <div class="erro" id="erroConfirmar"><?php echo $erros['confirmar'] ?? ""; ?></div>

      <button type="submit">Cadastrar</button>
    </form>

    <!-- Exibe mensagens de erro gerais -->
    <div class="erro" style="text-align:center;"><?php echo $erros['geral'] ?? ""; ?></div>

    <!-- Link para a página de login -->
    <a href="login.php">Já tem conta? Faça login</a>
  </div>

<script>
  // Aplica máscara no campo CPF (formato 000.000.000-00)
  document.getElementById("cpf").addEventListener("input", function(e) {
    let value = e.target.value.replace(/\D/g, "");
    if (value.length > 11) value = value.slice(0, 11);
    e.target.value = value
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  });

  // Aplica máscara no telefone ((DDD) 99999-9999)
  document.getElementById("telefone").addEventListener("input", function(e) {
    let value = e.target.value.replace(/\D/g, "");
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length <= 10) {
      e.target.value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
    } else {
      e.target.value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, "($1) $2-$3");
    }
  });
</script>
</body>
</html>
