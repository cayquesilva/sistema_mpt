<?php
// Iniciar sessão é a PRIMEIRA coisa a se fazer em qualquer script.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurações de conexão com o banco de dados
// ... (suas configurações de conexão continuam as mesmas) ...
$host = 'localhost';
$dbname = 'cayque_mpt';
$username = 'cayque_mpt';
$password = 'Kiq3506!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}


// --- FUNÇÕES EXISTENTES ---
function verificarJurado($codigo)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jurados WHERE codigo_acesso = :codigo");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();
    return $stmt->fetch();
}
function obterGrupos()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM grupos ORDER BY id");
    return $stmt->fetchAll();
}
function obterCategorias()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome");
    return $stmt->fetchAll();
}
function obterGrupoPorId($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM grupos WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}
function obterCategoriaPorId($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}
function gerarEmbedGdrive($url)
{
    return preg_replace('/\/view.*|\/edit.*/', '/preview', $url);
}

// --- NOVA FUNÇÃO ---
// Busca os critérios para uma determinada categoria
function obterCriteriosPorCategoria($categoria_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM criterios WHERE categoria_id = :categoria_id ORDER BY id");
    $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// --- FUNÇÕES MODIFICADAS ---
function obterTrabalhos($grupo_id, $categoria_id, $jurado_id)
{
    global $pdo;
    // A lógica de ver se foi avaliado agora checa a tabela de observações
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            (CASE WHEN obs.id IS NOT NULL THEN 1 ELSE 0 END) as avaliado
        FROM trabalhos t
        LEFT JOIN avaliacoes_observacoes obs ON t.id = obs.trabalho_id AND obs.jurado_id = :jurado_id
        WHERE t.grupo_id = :grupo_id AND t.categoria_id = :categoria_id 
        ORDER BY t.aluno_nome
    ");
    $stmt->bindParam(':grupo_id', $grupo_id, PDO::PARAM_INT);
    $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    $stmt->bindParam(':jurado_id', $jurado_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obterTrabalhoParaAvaliacao($trabalho_id, $jurado_id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            (CASE WHEN obs.id IS NOT NULL THEN 1 ELSE 0 END) as avaliado,
            obs.observacao
        FROM trabalhos t
        LEFT JOIN avaliacoes_observacoes obs ON t.id = obs.trabalho_id AND obs.jurado_id = :jurado_id
        WHERE t.id = :trabalho_id
    ");
    $stmt->bindParam(':trabalho_id', $trabalho_id, PDO::PARAM_INT);
    $stmt->bindParam(':jurado_id', $jurado_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Função para buscar as notas já dadas para um trabalho
function obterNotasDadas($trabalho_id, $jurado_id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.nome, a.nota 
        FROM avaliacoes a
        JOIN criterios c ON a.criterio_id = c.id
        WHERE a.trabalho_id = :trabalho_id AND a.jurado_id = :jurado_id
    ");
    $stmt->execute(['trabalho_id' => $trabalho_id, 'jurado_id' => $jurado_id]);
    return $stmt->fetchAll();
}


function salvarAvaliacao($jurado_id, $trabalho_id, $notas, $observacao)
{
    global $pdo;
    try {
        $pdo->beginTransaction();

        // Salvar a observação geral
        $stmt_obs = $pdo->prepare("INSERT INTO avaliacoes_observacoes (jurado_id, trabalho_id, observacao) VALUES (:jurado_id, :trabalho_id, :observacao)");
        $stmt_obs->execute(['jurado_id' => $jurado_id, 'trabalho_id' => $trabalho_id, 'observacao' => $observacao]);

        // Salvar a nota de cada critério
        $stmt_nota = $pdo->prepare("INSERT INTO avaliacoes (jurado_id, trabalho_id, criterio_id, nota) VALUES (:jurado_id, :trabalho_id, :criterio_id, :nota)");
        foreach ($notas as $criterio_id => $nota) {
            $stmt_nota->execute([
                ':jurado_id' => $jurado_id,
                ':trabalho_id' => $trabalho_id,
                ':criterio_id' => $criterio_id,
                ':nota' => $nota
            ]);
        }
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

function obterRanking($grupo_id, $categoria_id)
{
    global $pdo;
    // Query mais complexa para calcular a média das médias dos jurados
    $sql = "
        WITH MediasPorJurado AS (
            SELECT
                a.trabalho_id,
                a.jurado_id,
                AVG(a.nota) AS media_jurado
            FROM avaliacoes a
            GROUP BY a.trabalho_id, a.jurado_id
        )
        SELECT
            t.aluno_nome,
            t.escola_nome,
            t.titulo_trabalho,
            AVG(mpj.media_jurado) AS pontuacao_final,
            DENSE_RANK() OVER (ORDER BY AVG(mpj.media_jurado) DESC) as posicao
        FROM trabalhos t
        JOIN MediasPorJurado mpj ON t.id = mpj.trabalho_id
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
