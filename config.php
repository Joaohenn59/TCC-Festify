<?php
// Definições da conexão

$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = 'root';
$dbName = 'festfy';

// Cria a conexão
$conexao = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);


// Verifica a conexão
if ($conexao->connect_error) {
  die("Falha na conexão: " . $conexao->connect_error);
}
echo "Conectado com sucesso!";

// Fecha a conexão (opcional, o PHP fecha automaticamente ao final do script)
//$conexao->close();

?>