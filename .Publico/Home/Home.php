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

// lida com as mensagens de sessão
if (!function_exists('setMensagem')) {
    function setMensagem($tipo, $conteudo) {
        $_SESSION['mensagem'] = [
            'tipo' => $tipo,
            'conteudo' => $conteudo
        ];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <div class="Nova_Postagem">
        <h1>Nova Postagem</h1>
        <form action="Postar.php" method="post">
            <textarea name="post" id="Postagem" placeholder="O que você está pensando?"></textarea>
            <button type="submit" name="postar">Postar</button>
        </form>
    </div>
    <div class="Feed">
        <?php
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $posts = Feed($pagina);
        $conexao = conectar();

        if (!empty($posts)) {
            foreach ($posts as $post): 
                // Definir imagem padrão
                $img_user = $img_padrao;

                // Obter imagem personalizada se existir
                if (!empty($post['Certificado']) && !empty($post['IMG'])) {
                    $caminhoImagem = "Perfil/" . $post['Certificado'] . "/" . $post['IMG'];
                    if (file_exists($caminhoImagem)) {
                        $img_user = $caminhoImagem;
                    }
                }
                $likes = lokes($conexao, $post['id']);
                ?>
                <div class="Postagem">
                    <div class="User">
                        <a href="Perfil_pev.php?certificado=<?php echo htmlspecialchars($post['certificado'], ENT_QUOTES, 'UTF-8'); ?>">
                            <img src="<?php echo htmlspecialchars($img_user, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto do usuário">
                        </a>
                        <div class="UserInfo">
                            <h2><a href="Perfil_pev.php?certificado=<?php echo htmlspecialchars($post['certificado'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($post['nome'], ENT_QUOTES, 'UTF-8'); ?></a></h2>
                            <span class="DataPostagem">Postado em <?php echo htmlspecialchars($post['data_formatada'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="like">
                            <a href="Like.php?post=<?php echo htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8'); ?>&acao=like"><i class="fas fa-thumbs-up"></i></a>
                            <span><?php echo htmlspecialchars($post['Likes'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="deslike">
                            <a href="Like.php?post=<?php echo htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8'); ?>&acao=deslike"><i class="fas fa-thumbs-down"></i></a>
                            <span><?php echo htmlspecialchars($post['Deslike'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                    <div class="PostContent">
                        <p><?php echo nl2br(htmlspecialchars($post['conteudo'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                </div>
        <?php 
            endforeach;
            desconectar($conexao);
        } else {
            echo "<p>Não há postagens para exibir.</p>";
        }
        ?>
    </div>

</body>
</html>
