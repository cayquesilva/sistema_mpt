<?php
require_once 'config.php';
// Proteção: Apenas admin pode acessar esta página
if (!isset($_SESSION['jurado_codigo_acesso']) || $_SESSION['jurado_codigo_acesso'] !== 'admin') {
    die('Acesso negado.');
}

$grupos = obterGrupos();
$categorias = obterCategorias();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Relatórios - Prêmio MPT</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="pagina-header">
            <img src="img/logo.png" alt="Logo do Prêmio MPT" class="logo-header">
            <div class="pagina-titulo">
                <h1><i class="fas fa-print"></i> Painel de Geração de Relatórios</h1>
                <p>Selecione os filtros para gerar os relatórios em PDF.</p>
            </div>
            <div class="pagina-acoes">
                <a href="index.php" class="btn-acao neutro"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>
        </div>

        <div class="form-container">
            <form action="gerar_relatorio_mpt.php" method="get" target="_blank">
                <h3>Filtros do Relatório</h3>
                <div class="form-group">
                    <label for="grupo_id">1. Selecione o Grupo:</label>
                    <select name="grupo_id" id="grupo_id" class="form-control" required>
                        <option value="" disabled selected>-- Escolha um grupo --</option>
                        <?php foreach ($grupos as $grupo): ?>
                            <option value="<?php echo $grupo['id']; ?>"><?php echo htmlspecialchars($grupo['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="categoria_id">2. Selecione a Categoria:</label>
                    <select name="categoria_id" id="categoria_id" class="form-control">
                        <option value="">-- Todas as Categorias (Apenas para Ranking) --</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>3. Selecione o Tipo de Relatório:</label>
                    <div class="opcoes-relatorio">
                        <div class="opcao-radio">
                            <input type="radio" id="tipo_ranking" name="tipo" value="ranking" checked>
                            <label for="tipo_ranking">
                                <i class="fas fa-award"></i>
                                <div>
                                    <strong>Ranking Final</strong>
                                    <span>Gera a classificação final dos trabalhos.</span>
                                </div>
                            </label>
                        </div>
                        <div class="opcao-radio">
                            <input type="radio" id="tipo_detalhado" name="tipo" value="detalhado">
                            <label for="tipo_detalhado">
                                <i class="fas fa-file-alt"></i>
                                <div>
                                    <strong>Notas Detalhadas</strong>
                                    <span>Mostra as notas de cada jurado por critério (requer categoria).</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-salvar"><i class="fas fa-file-pdf"></i> Gerar Relatório</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>