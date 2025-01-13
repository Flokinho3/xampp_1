<?php

include_once '../Server/Server.php';
// Determinar ação com base nos dados enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    switch ($acao) {
        case 'excluir_conta':
            if (isset($_POST['certificado'])) {
                $certificado = $_POST['certificado'];
                $conexao = conectar();

                if (excluirContaPorCertificado($conexao, $certificado)) {
                    header('Location: ../../.Publico/Home/Home.php');
                } else {
                    echo "Erro ao excluir a conta.";
                }

                desconectar($conexao);
            } else {
                echo "Certificado não fornecido.";
            }
            break;

        default:
            echo "Ação desconhecida.";
            break;
    }
}

function excluirContaPorCertificado($conexao, $certificado) {
    // Iniciar uma transação para garantir a consistência
    mysqli_begin_transaction($conexao);

    try {
        // Excluir registros da tabela `post_reactions`
        $sqlReactions = "DELETE FROM post_reactions WHERE post_id IN (SELECT id FROM posts WHERE certificado = ?)";
        $stmtReactions = mysqli_prepare($conexao, $sqlReactions);
        mysqli_stmt_bind_param($stmtReactions, "s", $certificado);
        mysqli_stmt_execute($stmtReactions);
        mysqli_stmt_close($stmtReactions);

        // Excluir registros da tabela `posts`
        $sqlPosts = "DELETE FROM posts WHERE certificado = ?";
        $stmtPosts = mysqli_prepare($conexao, $sqlPosts);
        mysqli_stmt_bind_param($stmtPosts, "s", $certificado);
        mysqli_stmt_execute($stmtPosts);
        mysqli_stmt_close($stmtPosts);

        // Excluir registro da tabela `users`
        $sqlUsers = "DELETE FROM users WHERE Certificado = ?";
        $stmtUsers = mysqli_prepare($conexao, $sqlUsers);
        mysqli_stmt_bind_param($stmtUsers, "s", $certificado);
        mysqli_stmt_execute($stmtUsers);
        mysqli_stmt_close($stmtUsers);

        // Confirmar a transação
        mysqli_commit($conexao);

        return true;
    } catch (Exception $e) {
        // Reverter a transação em caso de erro
        mysqli_rollback($conexao);

        return false;
    }
}
?>