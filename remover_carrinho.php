<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['car_id'])) {
    $car_id = intval($_POST['car_id']);

    // Verifica se o item realmente pertence ao usuário logado
    $sql_check = "SELECT * FROM TB_CARRINHO WHERE CAR_ID = '$car_id' AND CLI_ID = '$usuario_id'";
    $result_check = mysqli_query($conexao, $sql_check);

    if ($result_check && mysqli_num_rows($result_check) > 0) {
        // Remove o item do carrinho
        $sql_delete = "DELETE FROM TB_CARRINHO WHERE CAR_ID = '$car_id' AND CLI_ID = '$usuario_id'";
        mysqli_query($conexao, $sql_delete);
    }
}

// Retorna para o carrinho
header("Location: carrinho.php");
exit;
