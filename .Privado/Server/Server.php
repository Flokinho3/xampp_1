<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function conectar() {
    $usuario = "root";
    $senha = "";
    $host = "localhost";
    $banco = "usuarios";

    $conexao = mysqli_connect($host, $usuario, $senha, $banco);
    if (!$conexao) {
        die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
    }
    return $conexao;
}

function desconectar($conexao) {
    mysqli_close($conexao);
}

function setMensagem($tipo, $mensagem) {
    $_SESSION[$tipo] = $mensagem;
}

function Cadastrar($conexao, $nome, $senha, $email) {
    // Verificar se o nome de usuário já existe
    $stmt = mysqli_prepare($conexao, "SELECT ID FROM users WHERE Nome = ?");
    mysqli_stmt_bind_param($stmt, "s", $nome);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) > 0) {
        setMensagem('erro', 'Nome de usuário já existe');
        return false;
    }

    // Verificar se o email já existe
    $stmt = mysqli_prepare($conexao, "SELECT ID FROM users WHERE Email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) > 0) {
        setMensagem('erro', 'Email já existe');
        return false;
    }

    // Verificar se a senha tem comprimento mínimo
    if (strlen($senha) < 8) {
        setMensagem('erro', 'Senha muito curta');
        return false;
    }

    // Criptografar a senha
    $senha = password_hash($senha, PASSWORD_DEFAULT);

    // Gerar um certificado único
    $certificado = bin2hex(random_bytes(16));

    // Inserir novo usuário
    $stmt = mysqli_prepare($conexao, "INSERT INTO users (Nome, Senha, Email, Certificado) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $nome, $senha, $email, $certificado);

    if (mysqli_stmt_execute($stmt)) {
        setMensagem('sucesso', 'Usuário cadastrado com sucesso');
        return true;
    } else {
        setMensagem('erro', 'Erro ao cadastrar usuário');
        return false;
    }
}

function Login($conexao, $identificador, $senha) {
    // Verifica se o identificador é um ID (número), email ou nome
    if (filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
        $campo = "Email";
    } elseif (is_numeric($identificador)) {
        $campo = "ID";
    } else {
        $campo = "Nome";
    }

    // Prepara a consulta de login com base no tipo de identificador
    $stmt = mysqli_prepare($conexao, "SELECT * FROM users WHERE $campo = ?");
    mysqli_stmt_bind_param($stmt, "s", $identificador);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    // Processa o resultado da consulta
    if ($row = mysqli_fetch_assoc($resultado)) {
        // Verifica a senha
        if (password_verify($senha, $row['Senha'])) {
            $_SESSION['user_id'] = $row['ID'];
            $_SESSION['user_nome'] = $row['Nome'];
            $_SESSION['certificado'] = $row['Certificado'];
            setMensagem('sucesso', 'Login realizado com sucesso');
            return true;
        } else {
            setMensagem('erro', 'Senha incorreta');
            return false;
        }
    } else {
        setMensagem('erro', 'Usuário não encontrado');
        return false;
    }
}

function Verificar_Certificado($conexao, $certificado) {
    // Consulta os dados do usuário pelo certificado
    $stmt = mysqli_prepare($conexao, "SELECT Email, IMG, Nome, ID, Certificado FROM users WHERE Certificado = ?");
    mysqli_stmt_bind_param($stmt, "s", $certificado);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Retorna os dados se encontrados, ou null se o certificado não existir
    return $resultado ?: null;
}


function Verificar_Imgs($certificado) {
    $diretorioBase = realpath(__DIR__ . '/../../.Publico/Home/Perfil/') . "/";
    $diretorioUsuario = $diretorioBase . $certificado . "/";

    // Verificar ou criar o diretório do usuário
    if (!is_dir($diretorioUsuario)) {
        if (!mkdir($diretorioUsuario, 0777, true)) {
            return ["erro" => "Falha ao criar o diretório do usuário."];
        }
    }

    // Listar imagens na pasta do usuário
    $imagens = array_filter(scandir($diretorioUsuario), function($arquivo) use ($diretorioUsuario) {
        return is_file($diretorioUsuario . $arquivo) && preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $arquivo);
    });

    return array_values($imagens); // Retornar os nomes das imagens
}

function Adicionar_Img($conexao, $certificado, $img) {
    $diretorioBase = realpath(__DIR__ . '/../../.Publico/Home/Perfil/') . "/";
    $diretorioUsuario = $diretorioBase . $certificado . "/";

    // Garantir que o diretório exista
    if (!is_dir($diretorioUsuario)) {
        if (!mkdir($diretorioUsuario, 0777, true)) {
            return ["tipo" => "alert-error", "conteudo" => "Falha ao criar o diretório do usuário."];
        }
    }

    // Validar o tipo de arquivo
    $tipoMime = mime_content_type($img['tmp_name']);
    $extensoesValidas = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];

    if (!in_array($tipoMime, $extensoesValidas)) {
        return ["tipo" => "alert-error", "conteudo" => "Formato de imagem inválido."];
    }

    // Gerar um nome aleatório para o arquivo com a extensão correta
    $extensao = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
    $nomeArquivo = bin2hex(random_bytes(16)) . "." . $extensao;

    // Mover o arquivo para o diretório do usuário
    $caminhoDestino = $diretorioUsuario . $nomeArquivo;
    if (move_uploaded_file($img['tmp_name'], $caminhoDestino)) {
        // Atualizar o banco de dados
        $stmt = mysqli_prepare($conexao, "UPDATE users SET IMG = ? WHERE Certificado = ?");
        mysqli_stmt_bind_param($stmt, "ss", $nomeArquivo, $certificado);
        mysqli_stmt_execute($stmt);
        return ["tipo" => "alert-success", "conteudo" => "Imagem adicionada com sucesso.", "arquivo" => $nomeArquivo];
    } else {
        return ["tipo" => "alert-error", "conteudo" => "Falha ao mover o arquivo para o diretório do usuário."];
    }
}

// Função para definir mensagens de sessão
if (!function_exists('setMensagem')) {
    function setMensagem($tipo, $conteudo) {
        $_SESSION['mensagem'] = [
            'tipo' => $tipo,
            'conteudo' => $conteudo
        ];
    }
}

// Verificar se o formulário de upload foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_img'])) {
    $conexao = conectar();
    if (isset($_SESSION['certificado'])) {
        $certificado = $_SESSION['certificado'];
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $resultado = Adicionar_Img($conexao, $certificado, $_FILES['img']);
            if (isset($resultado['tipo']) && isset($resultado['conteudo'])) {
                setMensagem($resultado['tipo'], $resultado['conteudo']);
            }
        } else {
            setMensagem('alert-error', 'Nenhuma imagem foi enviada ou ocorreu um erro no upload.');
        }
    } else {
        setMensagem('alert-error', 'Usuário não autenticado.');
    }

    // Redirecionar de volta para a página de perfil
    header("Location: ../../.Publico/Home/Perfil/Perfil.php");
    exit;
}

function Add_post($conexao, $certificado, $nome, $conteudo) {
    if (strlen($conteudo) > 1000) { // Exemplo: limite de 1000 caracteres
        setMensagem('erro', 'O conteúdo da postagem é muito longo.');
        header("Location: Home.php");
        exit;
    }
    
    // Salva a postagem no banco de dados
    $stmt = mysqli_prepare($conexao, "INSERT INTO posts (certificado, nome, conteudo) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $certificado, $nome, $conteudo);

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $success; // Retorna verdadeiro ou falso
}

function Feed($pagina = 1, $itens_por_pagina = 10) {
    $conexao = conectar();
    $offset = ($pagina - 1) * $itens_por_pagina;

    $stmt = $conexao->prepare(
        "SELECT posts.*, users.IMG, users.Certificado,
         DATE_FORMAT(posts.data_postagem, '%d/%m/%Y %H:%i') AS data_formatada 
         FROM posts 
         LEFT JOIN users ON posts.certificado = users.Certificado 
         ORDER BY data_postagem DESC 
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param("ii", $itens_por_pagina, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }

    $stmt->close();
    $conexao->close();

    return $posts;
}

function Pesquisa_img_feed($certificado, $conexao) {
    // Corrigindo o nome da tabela de 'usuarios' para 'users'
    $stmt = $conexao->prepare("SELECT IMG FROM users WHERE Certificado = ?");
    $stmt->bind_param("s", $certificado);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['IMG'];
    }

    return null;
}

function Feed_Usuario($certificado, $pagina = 1, $itens_por_pagina = 10) {
    $conexao = conectar();
    $offset = ($pagina - 1) * $itens_por_pagina;

    $stmt = $conexao->prepare(
        "SELECT posts.*, users.IMG, users.Certificado,
         DATE_FORMAT(posts.data_postagem, '%d/%m/%Y %H:%i') AS data_formatada 
         FROM posts 
         LEFT JOIN users ON posts.certificado = users.Certificado 
         WHERE posts.certificado = ?
         ORDER BY data_postagem DESC 
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param("sii", $certificado, $itens_por_pagina, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }

    $stmt->close();
    $conexao->close();

    return $posts;
}

?>

