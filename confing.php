<?php

// config.php

// Inicia a sessão, essencial para manter o estado do usuário entre as requisições.
session_start();

// --- Configurações do Banco de Dados (PDO) ---
$dbHost = '127.0.0.1';
$dbName = 'sistema_vendas';
$dbUser = 'root';
$dbPass = ''; // Senha em branco, comum em ambientes de desenvolvimento local.

try {
    // Cria a conexão com o banco de dados MySQL usando PDO.
    // ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION: Lança exceções em caso de erros.
    // ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC: Retorna os resultados como arrays associativos.
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Exception $e) {
    // Em caso de falha na conexão, exibe uma mensagem de erro e encerra o script.
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

// --- Leitura Opcional de Arquivo .env ---
// Verifica se o arquivo .env existe no mesmo diretório.
if (file_exists(__DIR__ . '/.env')) {
    // Lê todas as linhas do arquivo, ignorando linhas vazias e de comentários.
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignora linhas que começam com '#' (comentários).
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Divide a linha em chave e valor, separadas pelo primeiro '='.
        // O '+ [1=>'']' garante que sempre teremos um segundo elemento no array, mesmo que não haja '='.
        [$key, $value] = array_map('trim', explode('=', $line, 2) + [1 => '']);

        // Se uma chave foi encontrada, define a variável de ambiente.
        if ($key) {
            putenv("{$key}={$value}");
        }
    }
}

// --- Define Constantes para Configurações da OpenAI ---
// Verifica se a OpenAI está habilitada através da variável de ambiente.
define('OPENAI_ENABLED', getenv('ENABLE_OPENAI') === '1');
// Obtém a chave da API da OpenAI, se estiver definida.
define('OPENAI_KEY', getenv('OPENAI_API_KEY') ?: '');

