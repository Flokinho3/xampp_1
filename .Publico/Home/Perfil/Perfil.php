<?php
session_start();
include '../../../.Privado/Server/Server.php';

$conexao = conectar();

$img_padrao = "../../IMGS/Perfi_padrao.png";

// Verificar se o certificado está na sessão
if (isset($_SESSION['certificado'])) {
    $dadosUsuario = Verificar_Certificado($conexao, $_SESSION['certificado']);
    if (!$dadosUsuario) {
        header("Location: ../../../.Publico/Entrada/Login.php");
        exit;
    }
} else {
    header("Location: ../../../.Publico/Entrada/Login.php");
    exit;
}

// Definir a imagem do usuário
$img_user = $img_padrao;
echo "<script>console.log('Imagem padrão: $img_user');</script>";
if (!empty($dadosUsuario['IMG']) && !empty($dadosUsuario['Certificado'])) {
    echo "<script>console.log('Certificado: " . $dadosUsuario['Certificado'] . "');</script>";
    $caminhoImagem = "../Perfil/" . $dadosUsuario['Certificado'] . "/" . $dadosUsuario['IMG'];
    echo "<script>console.log('Caminho da imagem: $caminhoImagem');</script>";
    if (file_exists($caminhoImagem)) {
        $img_user = $caminhoImagem . "?v=" . time(); // Adicionar timestamp para evitar cache
    }
    echo "<script>console.log('Imagem do usuário: $img_user');</script>";
}

// Verificar imagens personalizadas
$imagensUsuario = !empty($dadosUsuario['Certificado']) ? Verificar_Imgs($dadosUsuario['Certificado']) : [];
$imagensAleatorias = array_slice($imagensUsuario, 0, 3);
shuffle($imagensAleatorias);

desconectar($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../CSS/Perfil.css?v=<?php echo time(); ?>">
    <title>Perfil <?php echo htmlspecialchars($dadosUsuario['Nome'], ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body>
    <script>
    function selecionarImagem(imagem) {
        const formData = new FormData();
        formData.append('nova_imagem', imagem);

        fetch('AtualizarImagem.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                location.reload();
            } else {
                alert('Erro ao atualizar imagem de perfil: ' + data.erro);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar a solicitação.');
        });
    }
    </script>
    <div class="top-bar">
        <a href="../../../.Publico/Home/Home.php">Voltar</a>
        <a href="#">Perfil</a>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert <?php echo $_SESSION['mensagem']['tipo']; ?>">
                <?php 
                    echo htmlspecialchars($_SESSION['mensagem']['conteudo'], ENT_QUOTES, 'UTF-8'); 
                    unset($_SESSION['mensagem']);
                ?>
            </div>
        <?php endif; ?>

        <div class="perfil">
            <img src="<?php echo htmlspecialchars($img_user, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem de perfil">
            <h1><?php echo htmlspecialchars($dadosUsuario['Nome'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Email: <?php echo htmlspecialchars($dadosUsuario['Email'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p>ID: <?php echo htmlspecialchars($dadosUsuario['ID'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <div class="adicionar-img">
            <h1>Adicionar Imagem</h1>
            <form action="../../../.Privado/Server/Server.php" method="post" enctype="multipart/form-data">
                <input type="file" name="img" accept="image/*" required>
                <button type="submit" name="adicionar_img">Adicionar</button>
            </form>
            <div class="imagens-aleatorias">
                <h2>Imagens Disponíveis</h2>
                <?php foreach ($imagensAleatorias as $img): ?>
                    <img 
                        src="<?php echo htmlspecialchars($dadosUsuario['Certificado'] . "/" . $img, ENT_QUOTES, 'UTF-8'); ?>" 
                        alt="Imagem aleatória" 
                        class="img-miniatura" 
                        onclick="selecionarImagem('<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>')"
                    >
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="botao-excluir">
        <form action="../../../.Privado/Porteiro/Excluir_conta.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação é irreversível!');">
            <input type="hidden" name="acao" value="excluir_conta">
            <input type="hidden" name="certificado" value="<?php echo htmlspecialchars($dadosUsuario['Certificado'], ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Excluir Conta?</button>
        </form>
    </div>
</body>
</html>
