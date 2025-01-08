
<?php

session_start();
if (!isset($_SESSION['certificado'])) {
    header("Location: ../../index.php");
    exit;
}

include '../../.Privado/Server/Server.php';
$conexao = conectar();

$user = Verificar_Certificado($conexao, $_SESSION['certificado']);
if (!$user) {
    setMensagem('erro', 'Certificado inválido');
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postar'])) {
    $conteudo = trim($_POST['post']);

    if (empty($conteudo) || $conteudo === "O que você está pensando?") {
        setMensagem('erro', 'Campo de texto vazio');
        header("Location: Home.php");
        exit;
    }

    if (strlen($conteudo) > 1000) {
        setMensagem('erro', 'O conteúdo da postagem é muito longo.');
        header("Location: Home.php");
        exit;
    }

    // Chamar a função Add_post apenas após validações
    $nome = $user['Nome'];
    $res = Add_post($conexao, $user['Certificado'], $nome, $conteudo);

    if ($res) {
        setMensagem('sucesso', 'Postagem realizada com sucesso!');
    } else {
        setMensagem('erro', 'Erro ao salvar a postagem.');
    }

    header("Location: Home.php");
    exit;
}


mysqli_close($conexao);
?>