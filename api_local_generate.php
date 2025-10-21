<?php

// api_local_generate.php

// Inclui o arquivo de configuração (onde provavelmente estão as credenciais do banco de dados)
require 'config.php';

// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

// Decodifica os dados recebidos na requisição como um array associativo
$data = json_decode(file_get_contents('php://input'), true);

// Verifica se a decodificação falhou e retorna um erro se for o caso
if (!$data) {
    exit(json_encode(['error' => 'Requisição inválida']));
}

// Extrai os parâmetros da requisição, com valores padrão caso não sejam fornecidos
$keywords = $data['keywords'] ?? ''; // Palavras-chave separadas por vírgula
$tipo = $data['tipo'] ?? 'caption'; // Tipo de texto a ser gerado (caption, descricao, bio, ideia, tweet)
$tom = $data['tom'] ?? 'divertido'; // Tom da escrita (divertido, profissional, emocional, direto)
$qtd = max(1, min(6, (int)($data['qtd'] ?? 3))); // Quantidade de textos a serem gerados (mínimo 1, máximo 6)
$translateTo = $data['translateTo'] ?? ''; // Parâmetro para tradução (não utilizado no código atual)

/**
 * Seleciona um elemento aleatório de um array.
 * @param array $arr O array do qual selecionar um elemento.
 * @return mixed Um elemento aleatório do array.
 */
function pick($arr) {
    return $arr[array_rand($arr)];
}

/**
 * Converte uma string em um slug (remove caracteres não alfanuméricos e converte para minúsculas).
 * @param string $s A string a ser convertida.
 * @return string A string convertida em slug.
 */
function slug($s) {
    return preg_replace('/[^\w]+/','', mb_strtolower($s));
}

// Divide as palavras-chave em um array, remove espaços em branco e filtra entradas vazias
$kwArr = array_values(array_filter(array_map('trim', explode(',', $keywords))));

// Define os templates para cada tipo de texto
$templates = [
    'caption' => [
        "{lead} {keywords_short} — perfeito pra quem quer {beneficio}.",
        "{lead} Experimente {keywords_short} e sinta a diferença: {beneficio}."
    ],
    'descricao' => [
        "{nome}. {descricao_curta} | Tamanho: {tamanho}. Cor: {cor}. Preço: R$ {preco}. Estoque: {estoque}.",
        "{nome} — {descricao_curta}. Confeccionado em {material}."
    ],
    'bio' => [
        "{lead} ✨ Criando {keywords_short}. Encomendas via DM.",
        "{lead} • {keywords_short} • Atendimento rápido"
    ],
    'ideia' => [
        "Post: Mostre um close do produto em uso e conte a história por trás dele.",
        "Tutorial rápido: 3 formas de usar {keywords_short}."
    ],
    'tweet' => [
        "{lead} {keywords_short} — simples assim.",
        "{lead} Oferta relâmpago: {cta}"
    ]
];

// Define os leads (inícios de frases) para cada tom
$leads = [
    'divertido' => ['Olha essa belezura!', 'Quem não ama uma novidade?'],
    'profissional' => ['Apresentamos', 'Lançamento oficial'],
    'emocional' => ['Feito com carinho', 'Feito pensando em você'],
    'direto' => ['Novo', 'Oferta']
];

// Define alguns benefícios e chamadas para ação (CTAs) genéricos
$beneficios = ['conforto e estilo', 'durabilidade', 'acabamento premium', 'melhor custo-benefício'];
$ctas = ['Garanta já o seu!', 'Compre agora', 'Link na bio'];

$items = []; // Array para armazenar os textos gerados
$hashtags = []; // Array para armazenar as hashtags geradas

// Loop para gerar a quantidade desejada de textos
for ($i = 0; $i < $qtd; $i++) {
    // Seleciona os templates para o tipo de texto especificado, ou usa 'caption' como padrão
    $t = $templates[$tipo] ?? $templates['caption'];
    // Escolhe um template aleatório
    $tpl = $t[array_rand($t)];

    // Monta um contexto com os valores a serem substituídos no template
    $ctx = [
        '{keywords_short}' => implode(' e ', array_slice($kwArr, 0, 2)) ?: 'produto', // Usa as duas primeiras palavras-chave ou 'produto'
        '{nome}' => $kwArr[0] ?? 'Produto', // Usa a primeira palavra-chave ou 'Produto'
        '{descricao_curta}' => 'peça feita com atenção aos detalhes', // Descrição curta genérica
        '{tamanho}' => 'único', // Tamanho genérico
        '{cor}' => 'varias cores', // Cor genérica
        '{preco}' => '0,00', // Preço genérico
        '{estoque}' => '—', // Estoque genérico
        '{material}' => 'algodão', // Material genérico
        '{beneficio}' => $beneficios[array_rand($beneficios)], // Benefício aleatório
        '{lead}' => $leads[$tom][array_rand($leads[$tom])] ?? 'Olha isso', // Lead aleatório para o tom especificado
        '{cta}' => $ctas[array_rand($ctas)] // CTA aleatório
    ];

    // Substitui os placeholders no template pelos valores do contexto
    $text = strtr($tpl, $ctx);

    // Adiciona um emoji aleatório ao texto com 50% de chance
    if (rand(0, 1)) {
        $text .= ' ' . (array_rand(['🔥' => 1, '✨' => 1, '🎉' => 1]) ? '🔥' : '✨');
    }

    // Adiciona o texto gerado e sua origem ao array de itens
    $items[] = ['text' => $text, 'origem' => 'local'];
}

// Gera hashtags simples a partir das palavras-chave e adiciona algumas genéricas
foreach ($kwArr as $k) {
    $hashtags[] = preg_replace('/\s+/', '', mb_strtolower($k)); // Remove espaços e converte para minúsculas
}
$hashtags = array_values(array_unique(array_merge($hashtags, ['loja', 'moda', 'estilo']))); // Remove duplicatas e adiciona genéricas

// Salva a primeira variação gerada no banco de dados para referência
// Apenas se não houver erros ao salvar, o bloco try-catch ignora os erros
try {
    $stmt = $pdo->prepare("INSERT INTO geracoes (tipo, tom, keywords, resultado, origem) VALUES (?, ?, ?, ?, 'local')");
    $stmt->execute([$tipo, $tom, $keywords, $items[0]['text']]);
} catch (Exception $e) {
    /* ignora erros de salvamento */
}

// Retorna os itens gerados e as hashtags em formato JSON
echo json_encode(['items' => $items, 'hashtags' => $hashtags]);


