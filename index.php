<?php
session_start(); // Inicia a sessão, se ainda não foi iniciada

// Verifica se o usuário está logado
if (!isset($_SESSION['certificado'])) {
    // Redireciona para a página de login
    header("Location: ./.Publico/Entrada/Login.php");
    exit();
} else {
    // Redireciona para a página inicial do sistema
    header("Location: ./.Publico/Home/Home.php");
    exit();
}
