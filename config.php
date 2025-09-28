<?php
// Dados da conexão com o MySQL
$servidor = "localhost";   // geralmente "localhost" no XAMPP
$usuario  = "root";        // usuário padrão do MySQL no XAMPP
$senha    = "root";            // senha em branco no XAMPP (se você não alterou)
$banco    = "festify";     // nome do banco que você criou no phpMyAdmin

// Cria a conexão
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

// Verifica se deu erro
if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}
?>
