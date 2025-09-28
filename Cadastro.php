<?php
session_start();
include("config.php");

$erros = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $telefone = preg_replace('/\D/', '', $_POST['telefone']);
    $cpf      = preg_replace('/\D/', '', $_POST['cpf']);
    $senha    = $_POST['senha'];
    $confirmar= $_POST['confirmar'];

    // ✅ Validações
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

    // ✅ Verifica duplicidade no banco
    if (empty($erros)) {
        // Email
        $checkEmail = "SELECT CLI_ID FROM TB_CLIENTE WHERE CLI_EMAIL = '$email'";
        $resEmail = mysqli_query($conexao, $checkEmail);
        if (mysqli_num_rows($resEmail) > 0) {
            $erros['email'] = "Este email já está cadastrado. Faça login ou use outro email.";
        }

        // CPF
        $checkCpf = "SELECT CLI_ID FROM TB_CLIENTE WHERE CLI_CPF = '$cpf'";
        $resCpf = mysqli_query($conexao, $checkCpf);
        if (mysqli_num_rows($resCpf) > 0) {
            $erros['cpf'] = "Este CPF já está cadastrado.";
        }

        // Telefone
        $checkTel = "SELECT CLI_ID FROM TB_CLIENTE WHERE CLI_FONE = '$telefone'";
        $resTel = mysqli_query($conexao, $checkTel);
        if (mysqli_num_rows($resTel) > 0) {
            $erros['telefone'] = "Este telefone já está cadastrado.";
        }
    }

    // ✅ Se não houver erros → cadastrar
    if (empty($erros)) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO TB_CLIENTE (CLI_NOME, CLI_EMAIL, CLI_SENHA, CLI_CPF, CLI_FONE) 
                VALUES ('$nome', '$email', '$hash', '$cpf', '$telefone')";

        if (mysqli_query($conexao, $sql)) {
            header("Location: login.php?cadastro=sucesso");
            exit;
        } else {
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
/* Estilos Gerais */
  body {
  font-family: Arial, sans-serif;
  background:#190016;
  color:white;
  display:flex;
  justify-content:center;
  align-items:center;
  height:100vh;
  margin:0;
  padding:20px; /* evita colar na borda do celular */
}

.form-box {
  background:#1c1c1c;
  padding:30px;
  border-radius:10px;
  width:100%;
  max-width:400px;
  box-shadow:0 0 15px rgba(0,0,0,0.5);
}

h1 {
  text-align:center;
  color:#ffcc00;
  margin-bottom:20px;
  font-size:28px;
}

form {
  display:flex;
  flex-direction:column;
  gap:15px;
}

input {
  padding:12px;
  border:1px solid #555;
  border-radius:6px;
  background:transparent;
  color:white;
  font-size:16px;
}

input::placeholder {
  color:#aaa;
}

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

button:hover {
  background:#a400ff;
  transform:scale(1.03);
}

.erro {
  color:#ff4d4d;
  font-size:13px;
  margin-top:-10px;
}

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

/* 📱 Responsividade */
@media (max-width: 480px) {
  .form-box {
    padding:20px;
    max-width:95%;
  }

  h1 {
    font-size:24px;
  }

  input, button {
    font-size:14px;
    padding:10px;
  }

  a {
    font-size:13px;
  }
}

@media (max-width: 768px) {
  .form-box {
    max-width:90%;
  }
}
</style>

</head>
<body>
  <div class="form-box">
    <h1>Cadastro</h1>
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

    <div class="erro" style="text-align:center;"><?php echo $erros['geral'] ?? ""; ?></div>

    <a href="login.php">Já tem conta? Faça login</a>
  </div>

  <script>
    document.getElementById("formCadastro").addEventListener("submit", function(e) {
      let cpf = document.getElementById("cpf").value.replace(/\D/g, '');
      let tel = document.getElementById("telefone").value.replace(/\D/g, '');
      let senha = document.getElementById("senha").value;
      let confirmar = document.getElementById("confirmar").value;
      let valid = true;

      // Limpa mensagens
      document.getElementById("erroCpf").textContent = "";
      document.getElementById("erroTelefone").textContent = "";
      document.getElementById("erroSenha").textContent = "";
      document.getElementById("erroConfirmar").textContent = "";

      if (cpf.length !== 11) {
        document.getElementById("erroCpf").textContent = "CPF deve ter 11 números.";
        valid = false;
      }

      if (tel.length < 10 || tel.length > 11) {
        document.getElementById("erroTelefone").textContent = "Telefone deve ter 10 ou 11 números.";
        valid = false;
      }

      if (senha.length < 6) {
        document.getElementById("erroSenha").textContent = "Senha deve ter no mínimo 6 caracteres.";
        valid = false;
      }

      if (senha !== confirmar) {
        document.getElementById("erroConfirmar").textContent = "As senhas não coincidem.";
        valid = false;
      }

      if (!valid) e.preventDefault();
    });

    // Máscara CPF
    document.getElementById("cpf").addEventListener("input", function(e) {
      let value = e.target.value.replace(/\D/g, "");
      if (value.length > 11) value = value.slice(0, 11);
      e.target.value = value
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    });

    // Máscara Telefone
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
