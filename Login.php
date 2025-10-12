<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT CLI_ID, CLI_SENHA FROM TB_CLIENTE WHERE CLI_EMAIL = '$email'";
    $result = mysqli_query($conexao, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Aceita tanto senha em texto puro quanto criptografada
        if ($senha === $row['CLI_SENHA'] || password_verify($senha, $row['CLI_SENHA'])) {
            $_SESSION['usuario_id'] = $row['CLI_ID'];
            header("Location: Home.php");
            exit;
        } else {
            echo "<script>alert('Senha incorreta!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Usuário não encontrado!'); window.location='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Festify</title>
 <link rel="icon" type="image/png" href="PNG/Logo.png">
  <style>
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Arial', sans-serif;
  background-color: #190016;
  color: white;
  height: 100vh;
}

/* Container principal */
.container {
  display: flex;
  height: 100vh;
}

/* Metade esquerda */
.left {
  flex: 1;
  background-color: #3c0078;
}

/* Metade direita */
.right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Box do formulário */
.form-box {
  width: 100%;
  max-width: 400px;
  padding: 20px;
}

h1 {
  font-size: 64px;
  margin-bottom: 40px;
  font-weight: 300;
  letter-spacing: 2px;
  text-align: center;
}

form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

input {
  padding: 15px;
  background-color: transparent;
  border: 1px solid #ffffff80;
  color: white;
  font-size: 16px;
  border-radius: 4px;
}

input::placeholder {
  color: #ccc;
}

button {
  padding: 15px;
  background-color: #8000c8;
  color: white;
  border: none;
  font-weight: bold;
  font-size: 18px;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s ease;
}

button:hover {
  background-color: #a400ff;
}

/* Botões de links (cadastro/esqueci senha) */
.links {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
  gap: 10px;
}

.link-btn {
  flex: 1;
  text-align: center;
  padding: 12px;
  background: #3c0078;
  color: white;
  border-radius: 6px;
  font-weight: bold;
  text-decoration: none;
  transition: background 0.3s ease, transform 0.2s ease;
}

.link-btn:hover {
  background: #5a00b3;
  transform: scale(1.05);
}

/* 🔹 Responsividade */
@media (max-width: 768px) {
  .container {
    flex-direction: column; /* pilha vertical */
  }

  .left {
    height: 200px; /* metade superior só como banner */
    flex: none;
  }

  h1 {
    font-size: 36px;
    margin-bottom: 20px;
  }

  .form-box {
    max-width: 90%;
  }

  .links {
    flex-direction: column;
  }

  .link-btn {
    width: 100%;
  }
}

  </style>
</head>
<body>
  <div class="container">
    <div class="right">
      <div class="form-box">
        <h1>LOGIN</h1>
        <form action="login.php" method="POST">
          <input type="email" name="email" placeholder="Digite seu email" required>
          <input type="password" name="senha" placeholder="Digite sua senha" required>
          <button type="submit">ENTRAR</button>
        </form>

        <div class="links">
          <a href="Cadastro.php" class="link-btn">Criar Conta</a>
          <a href="redefinir_senha.php" class="link-btn">Esqueci a Senha</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
