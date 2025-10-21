<?php
// Inclui o arquivo de configuração, que contém a conexão com o banco de dados e outras configurações.
require 'config.php';

// Verifica se o usuário está logado. Descomente e adapte se você tiver uma função de login.
// require_login();

// --- Busca Histórico de Gerações ---
// Prepara e executa uma consulta para buscar as últimas 20 gerações, ordenadas pela data de criação em ordem decrescente.
$stmt = $pdo->query("SELECT id, tipo, tom, keywords, origem, criado_em FROM geracoes ORDER BY criado_em DESC LIMIT 20");
$history = $stmt->fetchAll(); // Busca todos os resultados da consulta.
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Gerador de Conteúdo - Integrado</title>
    <!-- Link para o CSS do Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        /* Estilos personalizados podem ser adicionados aqui */
        .list-group-item .small { font-size: 0.85em; }
        .text-end small { color: #6c757d; } /* Cor do texto para datas */
    </style>
</head>
<body>

    <?php
    // Inclui o arquivo de navegação (menu).
    include 'nav.php';
    ?>

    <div class="container mt-4">
        <h3>Gerador de Conteúdo (Local + OpenAI + Tradução)</h3>

        <div class="row">
            <!-- Coluna do Formulário -->
            <div class="col-md-6">
                <!-- Formulário para gerar conteúdo -->
                <div class="card p-3 mb-3 shadow-sm">
                    <div class="mb-2"><strong>Palavras-chave</strong></div>
                    <input id="keywords" class="form-control mb-2" placeholder="ex: camiseta, verão, algodão">

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label for="tipo" class="form-label">Tipo de Conteúdo</label>
                            <select id="tipo" class="form-select">
                                <option value="caption">Legenda Instagram</option>
                                <option value="descricao">Descrição de Produto</option>
                                <option value="bio">Bio curta</option>
                                <option value="ideia">Ideia de post</option>
                                <option value="tweet">Tweet</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="tom" class="form-label">Tom</label>
                            <select id="tom" class="form-select">
                                <option value="divertido">Divertido</option>
                                <option value="profissional">Profissional</option>
                                <option value="emocional">Emocional</option>
                                <option value="direto">Direto</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label for="qtd" class="form-label">Quantidade</label>
                            <input type="number" id="qtd" class="form-control" value="3" min="1" max="6">
                        </div>
                        <div class="col-4">
                            <label for="useOpenAI" class="form-label">Motor de Geração</label>
                            <select id="useOpenAI" class="form-select">
                                <option value="0">Gerador Local</option>
                                <option value="1">OpenAI (Recomendado)</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="translateTo" class="form-label">Traduzir para</label>
                            <select id="translateTo" class="form-select">
                                <option value="">Sem Tradução</option>
                                <option value="en">Inglês</option>
                                <option value="es">Espanhol</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 d-grid">
                        <button id="gerarBtn" class="btn btn-primary btn-lg">Gerar Conteúdo</button>
                    </div>
                </div>

                <!-- Área para exibir os resultados -->
                <div id="results" class="mb-4">
                    <!-- Os resultados gerados serão inseridos aqui via JavaScript -->
                </div>
            </div>

            <!-- Coluna do Histórico -->
            <div class="col-md-6">
                <div class="card p-3 shadow-sm">
                    <h6>Histórico das Últimas 20 Gerações</h6>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($history)): ?>
                            <li class="list-group-item text-center text-muted">Nenhuma geração recente encontrada.</li>
                        <?php else: ?>
                            <?php foreach ($history as $h): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($h['tipo']) ?></strong>
                                        <span class="text-muted ms-2">— <?= htmlspecialchars($h['tom']) ?> • <?= htmlspecialchars($h['origem']) ?></span>
                                        <div class="small mt-1">
                                            <strong>Keywords:</strong> <?= htmlspecialchars($h['keywords'] ?? 'N/A') ?>
                                        </div>
                                    </div>
                                    <small class="text-end"><?= date('d/m H:i', strtotime($h['criado_em'])) ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>

async function postJSON(url, data){

  const r = await fetch(url, {

    method: 'POST',

    headers: {'Content-Type':'application/json'},

    body: JSON.stringify(data)

  });

  return r.json();

}



document.getElementById('gerarBtn').onclick = async () => {

  const keywords = document.getElementById('keywords').value;

  const tipo = document.getElementById('tipo').value;

  const tom = document.getElementById('tom').value;

  const qtd = parseInt(document.getElementById('qtd').value) || 3;

  const useOpenAI = document.getElementById('useOpenAI').value === '1';

  const translateTo = document.getElementById('translateTo').value || '';



  const target = useOpenAI ? 'api_openai.php' : 'api_local_generate.php';

  const payload = { keywords, tipo, tom, qtd, translateTo };



  const res = await postJSON(target, payload);



  if (res.error) {

    alert('Erro: ' + res.error);

    return;

  }



  const container = document.getElementById('results');

  container.innerHTML = '';



  res.items.forEach((it, idx) => {

    const card = document.createElement('div');

    card.className = 'card p-3 mb-2';

    card.innerHTML = `

      <div class="d-flex justify-content-between">

        <div><strong>Variação ${idx+1}</strong> <small class="text-muted">(${it.origem})</small></div>

        <div><button class="btn btn-sm btn-outline-secondary copy" data-txt="${encodeURIComponent(it.text)}">Copiar</button></div>

      </div>

      <div class="mt-2">${it.text.replace(/\n/g,'<br>')}</div>

    `;

    container.appendChild(card);

  });



  // copiar

  document.querySelectorAll('.copy').forEach(btn=>{

    btn.onclick = () => {

      const txt = decodeURIComponent(btn.dataset.txt);

      navigator.clipboard.writeText(txt).then(()=> {

        btn.innerText = 'Copiado!';

        setTimeout(()=> btn.innerText = 'Copiar',1200);

      });

    };

  });



  // mostrar hashtags se vierem

  if (res.hashtags && res.hashtags.length){

    const h = document.createElement('div');

    h.className = 'card p-2 mt-2';

    h.innerHTML = '<strong>Hashtags:</strong> ' + res.hashtags.map(t=>`<span class="badge bg-light text-dark me-1">#${t}</span>`).join(' ');

    container.appendChild(h);

  }

};

</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>