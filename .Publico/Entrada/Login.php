<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width    =device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../CSS/Login.css?v=1.0">
</head>   
<body>
    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-error">
            <?php 
                echo $_SESSION['erro']; 
                unset($_SESSION['erro']);
            ?>
        </div>
    <?php endif; ?>
    <div class="container">
        <h1>Login</h1>
        <div class="Logo">
            <img src="../IMGS/Logo.png" alt="Logo">
        </div>
        <form action="../../.Privado/Porteiro/Porteiro.php" method="post">
            <input type="hidden" name="acao" value="Login">
            <input type="email" name="nome" placeholder="Email">
            <input type="password" name="senha" placeholder="Senha">
            <button type="submit">Login</button>
        </form>
        <a href="RecuperarSenha.php">Esqueceu a senha?</a>    
        <a href="Cadastro.php">Cadastre-se</a>
    </div>
</body>
</html>