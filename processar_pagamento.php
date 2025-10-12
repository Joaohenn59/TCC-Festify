<?php
session_start();
include("config.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$valor_total = $_POST['valor_total'] ?? 0;
$forma = $_POST['forma'] ?? '';

if ($valor_total <= 0) {
    echo "Carrinho vazio ou valor inválido!";
    exit;
}

// Salvar pagamento 
$sql_pag = "INSERT INTO TB_PAGAMENTO (CLI_ID, PAG_FORMA_PAGAMENTO, PAG_VALOR)
            VALUES (?, ?, ?)";
$stmt_pag = $conexao->prepare($sql_pag);
$stmt_pag->bind_param("isd", $usuario_id, $forma, $valor_total);

if ($stmt_pag->execute()) {
    $pagamento_id = $conexao->insert_id;

    // Pega os itens do carrinho
    $sql_carrinho = "SELECT * FROM TB_CARRINHO WHERE CLI_ID = ?";
    $stmt_car = $conexao->prepare($sql_carrinho);
    $stmt_car->bind_param("i", $usuario_id);
    $stmt_car->execute();
    $res_carrinho = $stmt_car->get_result();

    if ($res_carrinho->num_rows > 0) {
        // Preparar venda
        $sql_venda = "INSERT INTO TB_VENDA_INGRESSO 
            (CLI_ID, ING_ID, VEI_QUANTIDADE, VEI_MEIA_INTEIRA, VEI_VALOR, VEI_TIPO, VEI_DATA_VENDA)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt_venda = $conexao->prepare($sql_venda);

        // Preparar update estoque
        $sql_update = "UPDATE TB_INGRESSO SET ING_QUANTIDADE = ING_QUANTIDADE - ? WHERE ING_ID = ?";
        $stmt_upd = $conexao->prepare($sql_update);

        while ($item = $res_carrinho->fetch_assoc()) {
            $ingresso_id  = (int) $item['ING_ID'];
            $quantidade   = (int) $item['CAR_QUANTIDADE'];
            $meia_inteira = $item['CAR_MEIA_INTEIRA']; // meia ou inteira
            $valor        = (float) $item['CAR_VALOR'];
            $tipo         = $meia_inteira;

            // Registrar venda
            $stmt_venda->bind_param(
                "iiisds",
                $usuario_id,
                $ingresso_id,
                $quantidade,
                $meia_inteira,
                $valor,
                $tipo
            );
            $stmt_venda->execute();

            // Atualizar estoque
            $stmt_upd->bind_param("ii", $quantidade, $ingresso_id);
            $stmt_upd->execute();
        }

        // Limpa carrinho
        $sql_clear = "DELETE FROM TB_CARRINHO WHERE CLI_ID = ?";
        $stmt_clear = $conexao->prepare($sql_clear);
        $stmt_clear->bind_param("i", $usuario_id);
        $stmt_clear->execute();

        echo "<script>alert('Pagamento realizado com sucesso!'); window.location='meus_ingressos.php';</script>";
    } else {
        echo "Carrinho vazio!";
    }
} else {
    echo "Erro ao processar pagamento: " . $conexao->error;
}
?>
