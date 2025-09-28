<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($nova_senha !== $confirmar_senha) {
        echo "<script>alert('As senhas não coincidem!'); window.location='redefinir_senha.php';</script>";
        exit;
    }

    $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    $sql = "UPDATE TB_CLIENTE SET CLI_SENHA = '$hash' WHERE CLI_EMAIL = '$email'";
    if (mysqli_query($conexao, $sql) && mysqli_affected_rows($conexao) > 0) {
        echo "<script>alert('Senha redefinida com sucesso!'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Email não encontrado!'); window.location='redefinir_senha.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Redefinir Senha - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
body {
  font-family: Arial, sans-serif;
  background-color: #190016;
  color: white;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  padding: 20px; /* garante espaço em telas pequenas */
}

.form-box {
  background: #1c1c1c;
  padding: 30px;
  border-radius: 10px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 0 15px rgba(0,0,0,0.5);
  text-align: center;
}

h1 {
  font-size: 28px;
  margin-bottom: 20px;
  color: #ffcc00;
}

form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

input {
  padding: 12px;
  border: 1px solid #555;
  border-radius: 6px;
  font-size: 14px;
  background: transparent;
  color: white;
}

input::placeholder {
  color: #aaa;
}

button {
  padding: 12px;
  background-color: #8000c8;
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s ease;
}

button:hover {
  background-color: #a400ff;
}

a {
  display: block;
  margin-top: 15px;
  color: #aaa;
  text-decoration: none;
  font-size: 14px;
}

a:hover {
  color: #fff;
}

/* 🔹 Responsividade */
@media (max-width: 768px) {
  h1 {
    font-size: 24px;
  }
  .form-box {
    padding: 20px;
  }
}

@media (max-width: 480px) {
  h1 {
    font-size: 20px;
  }
  input, button {
    font-size: 13px;
    padding: 10px;
  }
  a {
    font-size: 13px;
  }
}

  </style>
</head>
<body>
  <div class="form-box">
    <h1>Redefinir Senha</h1>
    <form action="redefinir_senha.php" method="POST">
      <input type="email" name="email" placeholder="Digite seu email" required>
      <input type="password" name="nova_senha" placeholder="Nova senha" required>
      <input type="password" name="confirmar_senha" placeholder="Confirmar senha" required>
      <button type="submit">Redefinir</button>
    </form>
    <a href="login.php">Voltar ao Login</a>
  </div>
</body>
</html>
