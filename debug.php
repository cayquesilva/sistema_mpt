<?php
// Inclui o config para garantir que a sessão seja iniciada
require_once 'config.php';

// Estilo simples para a página de debug
echo '<style>
    body { font-family: monospace; background-color: #282c34; color: #abb2bf; padding: 20px; font-size: 14px; }
    h1 { color: #61afef; border-bottom: 1px solid #61afef; padding-bottom: 10px; }
    pre { background-color: #21252b; padding: 15px; border-radius: 5px; white-space: pre-wrap; }
</style>';

echo '<h1>Visualizador de Sessão</h1>';

if (session_status() == PHP_SESSION_ACTIVE && !empty($_SESSION)) {
    echo '<h2>Conteúdo Atual da $_SESSION:</h2>';
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
} else {
    echo '<p>Nenhuma sessão ativa ou a sessão está vazia.</p>';
}
