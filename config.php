<?php
// Iniciar sessão no início de tudo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurações de conexão com o banco de dados
$host = 'localhost';
$dbname = 'premio_mpt';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}


// Função para verificar se o jurado existe
function verificarJurado($codigo)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jurados WHERE codigo_acesso = :codigo");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();
    return $stmt->fetch();
}

// Função para registrar o acesso do jurado
function registrarAcesso($jurado_id)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE jurados SET updated_at = NOW() WHERE id = :id");
    $stmt->bindParam(':id', $jurado_id);
    $stmt->execute();
}

// Função para obter todos os grupos
function obterGrupos()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM grupos ORDER BY id");
    return $stmt->fetchAll();
}

// Função para obter todas as categorias
function obterCategorias()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome");
    return $stmt->fetchAll();
}

// Função para obter um grupo específico pelo ID
function obterGrupoPorId($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM grupos WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Função para obter uma categoria específica pelo ID
function obterCategoriaPorId($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Função para obter os trabalhos de um grupo e categoria específicos (COM STATUS DE AVALIAÇÃO)
function obterTrabalhos($grupo_id, $categoria_id, $jurado_id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            (CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END) as avaliado
        FROM trabalhos t
        LEFT JOIN avaliacoes a ON t.id = a.trabalho_id AND a.jurado_id = :jurado_id
        WHERE t.grupo_id = :grupo_id AND t.categoria_id = :categoria_id 
        ORDER BY t.aluno_nome
    ");
    $stmt->bindParam(':grupo_id', $grupo_id, PDO::PARAM_INT);
    $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    $stmt->bindParam(':jurado_id', $jurado_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Função para obter os dados de um trabalho específico e se já foi avaliado
function obterTrabalhoParaAvaliacao($trabalho_id, $jurado_id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            t.*, 
            a.nota, 
            a.observacao,
            (CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END) as avaliado
        FROM trabalhos t
        LEFT JOIN avaliacoes a ON t.id = a.trabalho_id AND a.jurado_id = :jurado_id
        WHERE t.id = :trabalho_id
    ");
    $stmt->bindParam(':trabalho_id', $trabalho_id, PDO::PARAM_INT);
    $stmt->bindParam(':jurado_id', $jurado_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Função para salvar a avaliação
function salvarAvaliacao($jurado_id, $trabalho_id, $nota, $observacao)
{
    global $pdo;
    $stmt_check = $pdo->prepare("SELECT id FROM avaliacoes WHERE jurado_id = :jurado_id AND trabalho_id = :trabalho_id");
    $stmt_check->execute(['jurado_id' => $jurado_id, 'trabalho_id' => $trabalho_id]);
    if ($stmt_check->fetch()) {
        return false; // Já avaliou
    }

    $stmt = $pdo->prepare("INSERT INTO avaliacoes (jurado_id, trabalho_id, nota, observacao) VALUES (:jurado_id, :trabalho_id, :nota, :observacao)");
    return $stmt->execute([
        ':jurado_id' => $jurado_id,
        ':trabalho_id' => $trabalho_id,
        ':nota' => $nota,
        ':observacao' => $observacao
    ]);
}

// Função para obter o ranking de um grupo e categoria (COM LÓGICA DE EMPATE)
function obterRanking($grupo_id, $categoria_id)
{
    global $pdo;
    $sql = "
        SELECT
            t.aluno_nome,
            t.escola_nome,
            t.titulo_trabalho,
            AVG(a.nota) AS pontuacao_media,
            DENSE_RANK() OVER (ORDER BY AVG(a.nota) DESC) as posicao
        FROM trabalhos t
        JOIN avaliacoes a ON t.id = a.trabalho_id
        WHERE t.grupo_id = :grupo_id AND t.categoria_id = :categoria_id
        GROUP BY t.id, t.aluno_nome, t.escola_nome, t.titulo_trabalho
        ORDER BY posicao ASC, t.aluno_nome ASC;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':grupo_id', $grupo_id, PDO::PARAM_INT);
    $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Gera um link de embed para o Google Drive.
 */
function gerarEmbedGdrive($url)
{
    return preg_replace('/\/view.*|\/edit.*/', '/preview', $url);
}
