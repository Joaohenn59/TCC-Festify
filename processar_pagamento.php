<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $valor_total = floatval($_POST['valor_total'] ?? 0);
    $forma = $_POST['forma'] ?? 'Cartão';

    if ($valor_total <= 0) {
        echo "<script>alert('Valor inválido!'); window.location='carrinho.php';</script>";
        exit;
    }

    // 🔹 Ajustar forma de pagamento para bater com o ENUM do banco
    if ($forma === "credito" || $forma === "debito") {
        $forma_pagamento = "Cartão";
    } elseif ($forma === "pix") {
        $forma_pagamento = "PIX";
    } else {
        $forma_pagamento = "Cartão"; // fallback
    }

    // 🔹 Registrar o pagamento
    $sql_pag = "INSERT INTO TB_PAGAMENTO 
                (CLI_ID, PAG_VALOR, PAG_FORMA_PAGAMENTO, PAG_STATUS, PAG_DATA_PAGAMENTO) 
                VALUES 
                ('$usuario_id', '$valor_total', '$forma_pagamento', 'APROVADO', NOW())";

    if (!mysqli_query($conexao, $sql_pag)) {
        die("Erro ao registrar pagamento: " . mysqli_error($conexao));
    }
    $pagamento_id = mysqli_insert_id($conexao);

    // 🔹 Buscar itens do carrinho
    $sql_carrinho = "SELECT c.*, i.ING_ID, i.ING_TIPO, i.ING_VALOR, e.EVE_ID
                     FROM TB_CARRINHO c
                     JOIN TB_INGRESSO i ON c.ING_ID = i.ING_ID
                     JOIN TB_EVENTO e ON i.EVE_ID = e.EVE_ID
                     WHERE c.CLI_ID = '$usuario_id'";
    $result_carrinho = mysqli_query($conexao, $sql_carrinho);

    if ($result_carrinho && mysqli_num_rows($result_carrinho) > 0) {
        while ($item = mysqli_fetch_assoc($result_carrinho)) {
            $ingresso_id  = $item['ING_ID'];
            $quantidade   = $item['CAR_QUANTIDADE'];
            $valor        = $item['CAR_VALOR'];
            $tipo         = $item['ING_TIPO'];
            $meiaInteira  = $item['CAR_MEIA_INTEIRA'];
            $evento_id    = $item['EVE_ID'];

            // 🔹 Registrar a venda
            $sql_venda = "INSERT INTO TB_VENDA_INGRESSO 
                          (CLI_ID, ING_ID, VEI_QUANTIDADE, VEI_VALOR, VEI_TIPO, VEI_MEIA_INTEIRA, VEI_DATA_VENDA) 
                          VALUES 
                          ('$usuario_id', '$ingresso_id', '$quantidade', '$valor', '$tipo', '$meiaInteira', NOW())";
            mysqli_query($conexao, $sql_venda);

            // 🔹 Atualizar estoque
            $sql_update = "UPDATE TB_INGRESSO 
                           SET ING_QUANTIDADE_RESTANTE = ING_QUANTIDADE_RESTANTE - $quantidade
                           WHERE ING_ID = '$ingresso_id'";
            mysqli_query($conexao, $sql_update);
        }
    }

    // 🔹 Limpar carrinho
    $sql_limpar = "DELETE FROM TB_CARRINHO WHERE CLI_ID = '$usuario_id'";
    mysqli_query($conexao, $sql_limpar);

    echo "<script>
            alert('Pagamento confirmado com sucesso! Seus ingressos estão disponíveis.');
            window.location='meus_ingressos.php';
          </script>";
    exit;
}
?>
