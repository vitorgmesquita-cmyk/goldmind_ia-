<?php

// api_openai.php

require 'config.php'; // Inclui configurações, como as chaves da API.

header('Content-Type: application/json'); // Define o tipo de conteúdo da resposta como JSON.

// Verifica se a OpenAI está habilitada e se a chave da API está configurada.
if (!OPENAI_ENABLED || !OPENAI_KEY) {
    echo json_encode(['error' => 'OpenAI não ativado. Configure OPENAI_API_KEY e ENABLE_OPENAI=1']);
    exit;
}

// Decodifica os dados recebidos na requisição (espera-se um JSON).
$data = json_decode(file_get_contents('php://input'), true);

// Verifica se os dados foram decodificados corretamente.
if (!$data) {
    exit(json_encode(['error' => 'Requisição inválida']));
}

// Extrai os parâmetros da requisição, com valores padrão caso não sejam fornecidos.
$keywords = $data['keywords'] ?? ''; // Palavras-chave para gerar o texto.
$tipo = $data['tipo'] ?? 'caption'; // Tipo de conteúdo (ex: legenda, post).
$tom = $data['tom'] ?? 'divertido'; // Tom do texto (ex: divertido, sério, profissional).
$qtd = max(1, min(6, (int)($data['qtd'] ?? 3))); // Quantidade de variações a serem geradas (entre 1 e 6).
$translateTo = $data['translateTo'] ?? ''; // Idioma para tradução, se solicitado.

// Monta o prompt base para a API da OpenAI.
$prompt_base = "Você é um assistente que gera textos curtos para redes sociais. Gera {$qtd} variações do tipo '{$tipo}' com tom '{$tom}' usando estas palavras-chave: {$keywords}. Retorne as variações em JSON como um array 'variations'. Use linguagem em português (pt-BR).";

// Define as mensagens para a conversa com a API (incluindo o prompt do sistema e o do usuário).
$messages = [
    ["role" => "system", "content" => "Você é um gerador profissional de conteúdo para redes sociais."],
    ["role" => "user", "content" => $prompt_base]
];

// Configura o payload para a requisição à API Chat Completions da OpenAI.
$payload = [
    "model" => "gpt-3.5-turbo", // Modelo a ser utilizado (pode ser gpt-4 se disponível).
    "messages" => $messages,
    "max_tokens" => 400, // Limite de tokens na resposta.
    "n" => 1, // Número de respostas a serem geradas.
    "temperature" => 0.8 // Controla a criatividade da resposta (0.0 a 1.0).
];

// Inicializa a sessão cURL para fazer a requisição à API da OpenAI.
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna a resposta como string.
curl_setopt($ch, CURLOPT_POST, true); // Define o método como POST.
curl_setopt($ch, CURLOPT_HTTPHEADER, [ // Define os cabeçalhos da requisição.
    "Content-Type: application/json",
    "Authorization: Bearer " . OPENAI_KEY
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload)); // Define o corpo da requisição em JSON.

// Executa a requisição cURL e fecha a sessão.
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// Verifica se houve algum erro na requisição cURL.
if ($err) {
    echo json_encode(['error' => "cURL error: $err"]);
    exit;
}

// Decodifica a resposta da API da OpenAI.
$resp = json_decode($res, true);

// Verifica se a resposta da OpenAI é válida.
if (!$resp) {
    echo json_encode(['error' => 'Resposta inválida da OpenAI']);
    exit;
}

// Extrai o conteúdo gerado pela IA.
$text = $resp['choices'][0]['message']['content'] ?? '';

// Tenta extrair as variações de texto da resposta.
// Assume que a resposta é um texto com variações separadas por linhas.
$lines = array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $text)));
$variations = array_slice($lines, 0, $qtd); // Pega as primeiras 'qtd' variações.

// Tenta salvar a primeira variação no banco de dados.
try {
    $stmt = $pdo->prepare("INSERT INTO geracoes (tipo, tom, keywords, resultado, origem) VALUES (?, ?, ?, ?, 'openai')");
    $stmt->execute([$tipo, $tom, $keywords, $variations[0] ?? $text]);
} catch (Exception $e) {
    // Ignora erros de banco de dados, se houver.
}

// Realiza a tradução se o idioma de destino for especificado.
if ($translateTo) {
    // Constrói o prompt para tradução.
    $transPrompt = "Traduza para {$translateTo} estas linhas e retorne apenas as linhas traduzidas, preservando ordens:\n" . implode("\n", $variations);

    // Configura o payload para a requisição de tradução.
    $payload2 = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "Você é um assistente que traduz textos preservando tom e sentido."],
            ["role" => "user", "content" => $transPrompt]
        ],
        "max_tokens" => 800,
        "temperature" => 0.2 // Temperatura mais baixa para traduções mais precisas.
    ];

    // Faz a requisição para a tradução.
    $ch2 = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . OPENAI_KEY
    ]);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload2));

    $res2 = curl_exec($ch2);
    curl_close($ch2);

    $r2 = json_decode($res2, true);
    $translated_text = $r2['choices'][0]['message']['content'] ?? '';

    // Extrai as variações traduzidas.
    $lines = array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $translated_text)));
    $variations = array_slice($lines, 0, $qtd);
}

// Gera hashtags simples a partir das palavras-chave.
$kwPieces = array_values(array_filter(array_map('trim', explode(',', $keywords))));
$hashtags = [];
foreach ($kwPieces as $k) {
    // Remove espaços e converte para minúsculas.
    $hashtags[] = preg_replace('/\s+/', '', mb_strtolower($k));
}
// Adiciona hashtags genéricas e remove duplicatas.
$hashtags = array_values(array_unique(array_merge($hashtags, ['loja', 'moda', 'estilo'])));

// Formata o retorno final em JSON.
$items = [];
foreach ($variations as $v) {
    $items[] = ['text' => $v, 'origem' => 'openai'];
}

echo json_encode(['items' => $items, 'hashtags' => $hashtags]);
