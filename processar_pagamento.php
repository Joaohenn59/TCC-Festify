<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar itens do carrinho
$sql_carrinho = "SELECT C.ING_ID, I.ING_TIPO, C.CAR_VALOR, C.CAR_QUANTIDADE, C.CAR_MEIA_INTEIRA
                 FROM TB_CARRINHO C
                 JOIN TB_INGRESSO I ON C.ING_ID = I.ING_ID
                 WHERE C.CLI_ID = '$usuario_id'";
$result_carrinho = mysqli_query($conexao, $sql_carrinho);

if (mysqli_num_rows($result_carrinho) == 0) {
    echo "Carrinho vazio!";
    exit;
}

// Registrar venda
while ($item = mysqli_fetch_assoc($result_carrinho)) {
    $ing_id = $item['ING_ID'];
    $quantidade = $item['CAR_QUANTIDADE'];
    $valor = $item['CAR_VALOR'];
    $tipo = $item['ING_TIPO']; // vem da tabela de ingressos
    $meia_inteira = $item['CAR_MEIA_INTEIRA'];

    $sql_venda = "INSERT INTO TB_VENDA_INGRESSO 
        (CLI_ID, ING_ID, VEI_QUANTIDADE, VEI_VALOR, VEI_TIPO, VEI_MEIA_INTEIRA, VEI_DATA_VENDA)
        VALUES 
        ('$usuario_id', '$ing_id', '$quantidade', '$valor', '$tipo', '$meia_inteira', NOW())";

    if (!mysqli_query($conexao, $sql_venda)) {
        die("Erro ao salvar venda: " . mysqli_error($conexao));
    }
}

// Esvaziar carrinho só depois de registrar a venda
$sql_clear = "DELETE FROM TB_CARRINHO WHERE CLI_ID = '$usuario_id'";
mysqli_query($conexao, $sql_clear);

echo "<script>alert('Pagamento concluído com sucesso!'); window.location='meus_ingressos.php';</script>";
?>
