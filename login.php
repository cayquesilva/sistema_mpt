<?php
// Iniciar a sessão é a PRIMEIRA coisa a se fazer
require_once 'config.php';

// Verifica se o parâmetro 'logout' está presente na URL
if (isset($_GET['logout'])) {
    session_unset();    // Limpa todas as variáveis da sessão
    session_destroy();  // Destrói a sessão
    header('Location: login.php'); // Redireciona para a própria página de login
    exit;
}

// Verificar se o formulário foi enviado
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';

    if (empty($codigo)) {
        $erro = 'Por favor, informe o código de acesso.';
    } else {
        $jurado = verificarJurado($codigo);

        if ($jurado) {
            // Salvar informações na sessão
            $_SESSION['jurado_id'] = $jurado['id'];
            $_SESSION['jurado_nome'] = $jurado['nome'];
            $_SESSION['jurado_codigo_acesso'] = $jurado['codigo_acesso'];
            $_SESSION['autenticado'] = true;

            // Redirecionar para a página principal
            header('Location: selecionar_grupo.php');
            exit;
        } else {
            $erro = 'Código de acesso inválido. Por favor, tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Prêmio MPT na Escola</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <img src="img/logo.png" alt="Logo do Festival" class="logo">
        </div>

        <div class="login-form">
            <h3>Acesso de Jurados</h3>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label for="codigo">Código de Acesso:</label>
                    <input type="text" id="codigo" name="codigo" placeholder="Digite seu código de acesso" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </div>
            </form>
        </div>

        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> - Secretaria de Educação - Campina Grande</p>
        </div>
    </div>
</body>

</html>