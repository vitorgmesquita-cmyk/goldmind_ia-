<?php

// Arquivo: api_translate.php

// Inclui o arquivo de configuração, onde OPENAI_ENABLED e OPENAI_KEY devem estar definidos.
require 'config.php';

// Define o cabeçalho para indicar que a resposta será em formato JSON.
header('Content-Type: application/json');

// Verifica se a funcionalidade da OpenAI está habilitada e se a chave da API foi configurada.
if (!OPENAI_ENABLED || !OPENAI_KEY) {
    // Se a OpenAI não estiver ativada ou a chave não estiver definida, retorna um erro JSON e encerra a execução.
    exit(json_encode(['error' => 'OpenAI não ativado']));
}

// Obtém o corpo da requisição HTTP e decodifica o JSON recebido em um array associativo PHP.
$data = json_decode(file_get_contents('php://input'), true);

// Extrai o texto a ser traduzido e o idioma de destino dos dados recebidos.
// Se 'text' ou 'to' não estiverem presentes, usa valores padrão ('', 'en' respectivamente).
$text = $data['text'] ?? ''; // O texto original a ser traduzido.
$to = $data['to'] ?? 'en';   // O idioma de destino para a tradução (padrão é inglês).

// Monta o prompt que será enviado para a API da OpenAI.
// A instrução pede para traduzir o texto fornecido para o idioma especificado,
// com ênfase em manter o tom e a naturalidade.
$prompt = "Traduza o texto abaixo para o idioma '{$to}' mantendo o tom e a naturalidade:\n\n" . $text;

// Define o payload (carga útil) para a requisição à API Chat Completions da OpenAI.
$payload = [
    "model" => "gpt-3.5-turbo", // Especifica qual modelo da OpenAI usar (neste caso, gpt-3.5-turbo).
    "messages" => [
        // Define a mensagem do sistema para guiar o comportamento da IA.
        // Neste caso, instrui a IA a atuar como um tradutor que preserva o estilo.
        ["role" => "system", "content" => "Você traduz textos preservando o estilo."],
        // Define a mensagem do usuário, que contém o prompt com o texto real a ser traduzido.
        ["role" => "user", "content" => $prompt]
    ],
    // Define a "temperatura" da resposta. Um valor baixo (0.2) torna a saída mais determinística e focada,
    // o que é bom para traduções onde a precisão é importante.
    "temperature" => 0.2
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");



