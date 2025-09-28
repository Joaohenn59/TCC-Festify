<?php
session_start();

// Destrói todas as variáveis da sessão
session_unset();

// Destrói a sessão
session_destroy();

// Redireciona para a home sem login
header("Location: HomeSemLogin.php");
exit;
?>
