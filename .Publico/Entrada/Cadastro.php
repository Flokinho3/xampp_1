<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/Cadastro.css">
    <title>Cadastro</title>
    <style>
        
    </style>
</head>
<body>
    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['sucesso']; 
                unset($_SESSION['sucesso']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-error">
            <?php 
                echo $_SESSION['erro']; 
                unset($_SESSION['erro']);
            ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="logo">
            <img src="../IMGS/logo.png" alt="Logo">
        </div>
        <h1>Cadastro</h1>
        <form action="../../.Privado/Porteiro/Porteiro.php" method="post">
            <input type="hidden" name="acao" value="Cadastro">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" minlength="8" required>
            <button type="submit">Cadastrar</button>
        </form>
        <a href="Login.php">Já tem uma conta? Faça login</a>
        <a href="RecuperarSenha.php">Esqueceu a senha?</a>
    </div>
</body>
</html>
