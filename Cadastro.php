<?php
$mensagem_sucesso = "";

if(isset($_POST['submit'])) { 
    include_once ('config.php'); 
    
    $nome = $_POST['nome']; 
    $senha = $_POST['senha']; 
    $email = $_POST['email']; 
    $telefone = $_POST['telefone'];   
    $data_nasc = $_POST['data_nascimento']; 
    $cidade = $_POST['cidade']; 
    $estado = $_POST['estado']; 
    $endereco = $_POST['endereco']; 
    
    $result = mysqli_query($conexao, "INSERT INTO usuarios(nome,senha,email,telefone,sexo,data_nasc,cidade,estado,endereco) VALUES('$nome','$senha','$email','$telefone','$sexo','$data_nasc','$cidade','$estado','$endereco')");
    
    $mensagem_sucesso = $result ? "Cadastro realizado com sucesso!" : "Erro ao cadastrar. Tente novamente.";
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro - Festify</title>
  <link rel="icon" type="image/png" href="PNG/Logo.png">
  <link rel="stylesheet" href="cadastro.css">
</head>
<body>
  <div class="container">
    <div class="right">
      <div class="form-box">
        <h1>CADASTRO</h1>
        <form>
          <input type="text" id="nome" placeholder="Nome Completo" required>
          <input type="email" id="email" placeholder="Email" required>
          <input type="tel" id="telefone" placeholder="Número de Telefone" required>
          <input type="text" id="cpf" placeholder="CPF" required>
          <input type="password" placeholder="Senha" required>
          <input type="password" placeholder="Confirmar Senha" required>
          <button type="submit">CADASTRAR</button>
        </form>
        <p class="login-link">
          Já possui conta? <a href="login.html">Faça login</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>