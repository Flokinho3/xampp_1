<?php
session_start();
include '../../.Privado/Server/Server.php';

$conexao = conectar();
$img_padrao = "../IMGS/Perfi_padaro.png";

if(isset($_GET['certificado'])) {
    $certificado = $_GET['certificado'];
} else {
    header('Location: Home.php');
    exit;
}

// Recebe o usuário com o certificado
$user = Verificar_Certificado($conexao, $certificado);

if($user) {
    // Verificar se o usuário tem uma imagem personalizada
    $img_user = $img_padrao;
    if (!empty($user['IMG']) && !empty($user['Certificado'])) {
        $caminhoImagem = "Perfil/" . $user['Certificado'] . "/" . $user['IMG'];
        if (file_exists($caminhoImagem)) {
            $img_user = $caminhoImagem;
        }
    }
} else {
    header('Location: Home.php');
    exit;
}

// Buscar posts do usuário
$posts_usuario = Feed_Usuario($certificado);

desconectar($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/Home.css">
    <title>Perfil de <?php echo htmlspecialchars($user['Nome'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        .perfil-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            width: 90%;
            max-width: 600px;
            text-align: center;
        }

        .perfil-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .perfil-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #fff;
            margin-bottom: 15px;
            object-fit: cover;
        }

        .perfil-info {
            margin: 10px 0;
        }

        .voltar-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(90deg, #572ddd 36%, #1a73e8 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            transition: background 0.3s ease;
        }

        .voltar-btn:hover {
            background: linear-gradient(90deg, #1a73e8 36%, #572ddd 100%);
        }

        .posts-titulo {
            text-align: center;
            margin: 20px 0;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="Top_bar">
        <h1>Perfil de <?php echo htmlspecialchars($user['Nome'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <ul>
            <li>
                <a href="Home.php" class="voltar-btn">Voltar para Home</a>
            </li>
        </ul>
    </div>

    <div class="perfil-container">
        <div class="perfil-header">
            <img src="<?php echo htmlspecialchars($img_user, ENT_QUOTES, 'UTF-8'); ?>" 
                 alt="Foto de <?php echo htmlspecialchars($user['Nome'], ENT_QUOTES, 'UTF-8'); ?>">
            <h2><?php echo htmlspecialchars($user['Nome'], ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>
        <div class="perfil-info">
            <p>Email: <?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>

    <h2 class="posts-titulo">Posts de <?php echo htmlspecialchars($user['Nome'], ENT_QUOTES, 'UTF-8'); ?></h2>
    
    <div class="Feed">
        <?php if (!empty($posts_usuario)): ?>
            <?php foreach ($posts_usuario as $post): ?>
                <div class="Postagem">
                    <div class="User">
                        <img src="<?php echo htmlspecialchars($img_user, ENT_QUOTES, 'UTF-8'); ?>" 
                             alt="Foto do usuário">
                        <div class="UserInfo">
                            <h2><?php echo htmlspecialchars($post['nome'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <span class="DataPostagem">Postado em <?php echo htmlspecialchars($post['data_formatada'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                    <div class="PostContent">
                        <p><?php echo nl2br(htmlspecialchars($post['conteudo'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #fff;">Este usuário ainda não fez nenhuma postagem.</p>
        <?php endif; ?>
    </div>
</body>
</html>