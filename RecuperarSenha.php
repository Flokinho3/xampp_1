<?php

include_once '.Privado/Server/Server.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'RecuperarSenha') {
        // Recuperar senha - enviar link
        if (isset($_POST['email'])) {
            $email = $_POST['email'];
            $conexao = conectar();
            $resultado = RecuperarSenha($conexao, $email);
            desconectar($conexao);

            echo "<p>{$resultado['mensagem']}</p>";
        } else {
            echo "<p>Por favor, insira um email válido.</p>";
        }
    } elseif (isset($_POST['nova_senha'], $_POST['confirmar_senha'], $_POST['certificado'])) {
        // Trocar senha
        $nova_senha = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];
        $certificado = $_POST['certificado'];

        if ($nova_senha !== $confirmar_senha) {
            die('<p>As senhas não coincidem.</p>');
        }

        if (strlen($nova_senha) < 8) {
            die('<p>A senha deve ter pelo menos 8 caracteres.</p>');
        }

        $conexao = conectar();
        $stmt = mysqli_prepare($conexao, "SELECT Email FROM users WHERE Certificado = ? AND Link IS NOT NULL");
        mysqli_stmt_bind_param($stmt, "s", $certificado);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($resultado)) {
            $email = $row['Email'];
            $hash_senha = password_hash($nova_senha, PASSWORD_BCRYPT);

            $stmt = mysqli_prepare($conexao, "UPDATE users SET Senha = ?, Link = NULL WHERE Email = ?");
            mysqli_stmt_bind_param($stmt, "ss", $hash_senha, $email);

            if (mysqli_stmt_execute($stmt)) {
                echo "<p>Senha alterada com sucesso! Você pode fazer login agora.</p>";
            } else {
                echo "<p>Erro ao atualizar a senha. Tente novamente mais tarde.</p>";
            }
        } else {
            echo "<p>Certificado inválido ou expirado.</p>";
        }

        desconectar($conexao);
    } else {
        echo "<p>Dados inválidos. Certifique-se de preencher todos os campos.</p>";
    }
} elseif (isset($_GET['certificado'])) {
    // Verifica o link de recuperação
    $conexao = conectar();
    if (Verificar_Link_Recuperacao($conexao, $_GET['certificado'])) {
        $html_recuperar_senha = file_get_contents('TrocarSenha.html');
        $html_recuperar_senha = str_replace('{certificado}', $_GET['certificado'], $html_recuperar_senha);
        echo $html_recuperar_senha;
    } else {
        echo "<p>Link inválido ou expirado.</p>";
    }
    desconectar($conexao);
} else {
    // Exibe o formulário inicial de recuperação
    echo file_get_contents('RecuperarSenha.html');
}
?>
