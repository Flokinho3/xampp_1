<?php
session_start();
include '../../.Privado/Server/Server.php';

$conexao = conectar();

// Diretório padrão para imagens
$img_padrao = "../IMGS/Perfi_padaro.png";

// Verificar se o certificado está na sessão
if (!isset($_SESSION['certificado'])) {
    // Redirecionar se o certificado não estiver definido
    header("Location: ../../.Publico/Entrada/Login.php");
    exit;
}

// Obter dados do usuário com base no certificado
$dadosUsuario = Verificar_Certificado($conexao, $_SESSION['certificado']);
if (!$dadosUsuario) {
    // Certificado inválido ou usuário não encontrado
    header("Location: ../../.Publico/Entrada/Login.php");
    exit;
}

// Verificar se o usuário tem uma imagem personalizada
$img_user = $img_padrao; // Definir imagem padrão inicialmente
if (!empty($dadosUsuario['IMG']) && !empty($dadosUsuario['Certificado'])) {
    $caminhoImagem = "Perfil/" . $dadosUsuario['Certificado'] . "/" . $dadosUsuario['IMG'];
    if (file_exists($caminhoImagem)) {
        $img_user = $caminhoImagem; // Usar imagem do usuário se existir
    }
}

desconectar($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/Home.css?v=<?php echo time(); ?>">
    <title>Painel do Usuário</title>
</head>
<body>
    <div class="Top_bar">
        <h1>Bem-vindo, <?php echo htmlspecialchars($dadosUsuario['Nome'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <a href="Perfil/Perfil.php">
            <img src="<?php echo htmlspecialchars($img_user, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil" style="max-width: 150px; border-radius: 50%;">
        </a>
        <ul>
            <li>
                <a href="#">Home</a>
                <a href="Perfil/Perfil.php">Perfil</a>
                <a href="../../.Privado/Porteiro/Sair.php">Sair</a>
            </li>
        </ul>
    </div>
    <div class="container">
        <h1>Bem-vindo, <?php echo htmlspecialchars($dadosUsuario['Nome'], ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>
</body>
</html>
