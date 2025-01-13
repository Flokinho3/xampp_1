<?php

include_once '../../.Privado/Server/Server.php';

if(isset($_GET['acao']) && isset($_GET['post']) && isset($_SESSION['certificado'])) {
    $conexao = conectar();
    $acao = $_GET['acao'];
    $id_post = $_GET['post'];
    $certificado = $_SESSION['certificado'];

    if ($acao == 'like' || $acao == 'deslike') {
        $column = $acao === 'like' ? 'Likes' : 'Deslike';
        try {
            if (updatePostReaction($conexao, $id_post, $column, $certificado)) {
                setMensagem('sucesso', 'Reação registrada com sucesso');
            } else {
                setMensagem('erro', 'Você já reagiu a esta postagem');
            }
        } catch (Exception $e) {
            setMensagem('erro', 'Erro ao processar reação');
        }
    }

    desconectar($conexao);
    header('Location: Home.php');
    exit;
} else {
    setMensagem('erro', 'Parâmetros inválidos');
    header('Location: Home.php');
    exit;
}

?>