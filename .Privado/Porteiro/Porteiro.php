<?php
session_start();
include '../Server/Server.php';

$conexao = conectar();

if (isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao === 'Cadastro') {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        if (Cadastrar($conexao, $nome, $senha, $email)) {
            header("Location: ../../.Publico/Entrada/Cadastro.php");
            exit;
        } else {
            header("Location: ../../.Publico/Entrada/Cadastro.php");
            exit;
        }
    }

    if ($acao === 'Login') {
        $identificador = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);

        if (Login($conexao, $identificador, $senha)) {
            header("Location: ../../.Publico/Home/Home.php");
            exit;
        } else {
            header("Location: ../../.Publico/Entrada/Login.php");
            exit;
        }
    }

    if ($acao === 'RecuperarSenha') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $resultado = RecuperarSenha($conexao, $email);
        if ($resultado) {
            $_SESSION['resultado'] = $resultado;
            header("Location: ../../.Publico/Entrada/RecuperarSenha.php");
            exit;
        } else {
            header("Location: ../../.Publico/Entrada/RecuperarSenha.php");
            exit;
        }

    }
} 
desconectar($conexao);
?>