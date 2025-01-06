<?php
session_start();
include '../../../.Privado/Server/Server.php';

$conexao = conectar();

$response = ['sucesso' => false];

if (isset($_SESSION['certificado']) && isset($_POST['nova_imagem'])) {
    $certificado = $_SESSION['certificado'];
    $novaImagem = $_POST['nova_imagem'];
    $caminhoImagem =  $certificado . "/" . $novaImagem;

    // Verificar se a imagem existe
    if (file_exists($caminhoImagem)) {
        $stmt = mysqli_prepare($conexao, "UPDATE users SET IMG = ? WHERE Certificado = ?");
        mysqli_stmt_bind_param($stmt, "ss", $novaImagem, $certificado);

        if (mysqli_stmt_execute($stmt)) {
            $response['sucesso'] = true;
        } else {
            $response['erro'] = 'Falha ao atualizar o banco de dados.';
        }
    } else {
        $response['erro'] = 'Imagem não encontrada.';
    }
} else {
    $response['erro'] = 'Parâmetros inválidos.';
}

desconectar($conexao);

// Retornar resposta como JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
