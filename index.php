<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Metadados básicos -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldMind ✨ IA de Conteúdo</title>

    <!-- Bootstrap (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Estilos customizados -->
    <style>
        /* Layout geral */
        body {
            background-color: #0d0d0d;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
            margin: 0;
        }

        /* Cabeçalho */
        header {
            background: linear-gradient(90deg, #FFD700, #b8860b);
            color: #000;
            text-align: center;
            padding: 10px;
            font-weight: bold;
            font-size: 1.3rem;
            letter-spacing: 1px;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.4);
        }

        /* Área de chat */
        .chat-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            scrollbar-width: thin;
            scrollbar-color: #FFD700 #1a1a1a;
        }

        .msg {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 15px;
            animation: fadeIn 0.3s ease;
            white-space: pre-wrap;
            line-height: 1.5;
        }

        .user-msg {
            align-self: flex-end;
            background-color: #FFD700;
            color: #000;
            border-top-right-radius: 0;
        }

        .bot-msg {
            align-self: flex-start;
            background-color: #222;
            border: 1px solid #FFD700;
            border-top-left-radius: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Área de entrada */
        .input-area {
            display: flex;
            gap: 10px;
            padding: 15px;
            border-top: 1px solid #333;
            background-color: #111;
        }

        .input-area input {
            flex: 1;
            border-radius: 25px;
            padding: 10px 20px;
            background-color: #1a1a1a;
            border: 1px solid #FFD700;
            color:#fff;
            outline:none;
        }

        .input-area button {
            background-color:#FFD700;
            border:none;
            color:#000;
            font-weight:bold;
            border-radius:25px;
            padding:10px 25px;
            transition:.2s;
        }

        .input-area button:hover {
            background-color:#b8860b;
        }

        /* Logo */
        .logo {
            text-align:center;
            margin-top:10px;
        }

        .logo img {
            width:90px;
            border-radius:10px;
            animation:glow 3s infinite alternate;
        }

        @keyframes glow {
           from { filter: drop-shadow(0 0 5px #FFD700); }
           to   { filter: drop-shadow(0 0 20px #FFD700); }
        }
    </style>
</head>

<body>
    <!-- Cabeçalho com logo -->
    <header>
        <div class="logo">
           <!-- Substitua pelo caminho correto da imagem -->
           <img src="goldmind.png" alt="Logo GoldMind IA">
        </div>
        GoldMind ✨ Inteligência de Conteúdo
    </header>

    <!-- Área de mensagens -->
    <div id="chat" class="chat-container">
        <div class="bot-msg msg">
           Olá! 💬 Eu sou a <b>GoldMind</b> — sua IA de criação de conteúdo.
           Digite um tema ou ideia e eu gero legendas, descrições e hashtags incríveis pra você! 💎
        </div>
    </div>

    <!-- Entrada do usuário -->
    <div class="input-area">
        <input type="text"
               id="userInput"
               placeholder="Digite sua ideia... (ex.: camiseta tie-dye, loja de moda)">
        <button id="sendBtn">Gerar ✨</button>
    </div>

    <!-- (Opcional) Script para interatividade futura -->
    <!--
    <script src="seu-script.js"></script>
    -->

<script>

const chat = document.getElementById('chat');

const input = document.getElementById('userInput');

const sendBtn = document.getElementById('sendBtn');



const BENEFICIOS = [

  "um equilíbrio perfeito entre conforto e sofisticação",

  "materiais de alta performance e toque premium",

  "detalhes que revelam autenticidade e exclusividade",

  "acabamento impecável e design inovador",

  "um toque de luxo em cada detalhe",

  "estilo moderno com essência atemporal",

  "leveza e elegância para o dia a dia",

  "qualidade que fala por si",

  "inspiração nas maiores tendências do mundo fashion",

  "presença marcante e energia única"

];



const EMOCOES = [

  "inspirando confiança e atitude em cada momento",

  "feita para quem entende que estilo é poder",

  "expressando personalidade em cada traço",

  "carregando o DNA da autenticidade",

  "para quem transforma o comum em extraordinário",

  "conectando essência e modernidade",

  "refletindo o brilho de quem pensa diferente",

  "despertando o desejo de ser notado",

  "para quem quer viver com propósito e elegância",

  "mostrando que o luxo está nos detalhes"

];



const CTAs = [

  "Garanta o seu agora e viva essa experiência.",

  "Vista-se de confiança — descubra o poder do seu estilo.",

  "Transforme o seu visual com autenticidade e classe.",

  "Aposte no que te diferencia. Compre agora.",

  "Sinta a energia de vestir algo feito para você.",

  "Eleve seu padrão — disponível por tempo limitado.",

  "O novo luxo começa em você. Adquira o seu.",

  "Conecte-se com a essência GoldMind.",

  "Vista sua atitude, conquiste o mundo.",

  "O futuro da moda está aqui — e começa com você."

];



const EMOJIS = ["💎", "✨", "🔥", "🌟", "⚡", "💥", "🖤", "🏆", "🎯", "🌌"];



function pick(arr) {

  return arr[Math.floor(Math.random() * arr.length)];

}



function gerarTextoPremium(nome) {

  const beneficio = pick(BENEFICIOS);

  const emocao = pick(EMOCOES);

  const cta = pick(CTAs);

  const emoji = pick(EMOJIS);



  const frasesExtras = [

    "Mais do que um produto — uma expressão de identidade.",

    "Cada linha e textura carrega um propósito: destacar quem você é.",

    "Inspirado em mentes ousadas que acreditam em criar o próprio caminho.",

    "Um símbolo de poder e autenticidade, pensado nos mínimos detalhes.",

    "A fusão entre tecnologia, arte e moda contemporânea.",

    "Ideal para quem busca exclusividade e presença marcante.",

    "Criado para quem transforma cada momento em estilo.",

    "Porque o verdadeiro luxo é ser único.",

    "Design inteligente para pessoas que pensam grande.",

    "Perfeito para quem não segue tendências — cria as suas."

  ];



  return `

  ${emoji} <b>${nome.toUpperCase()}</b> — ${beneficio}.  

  ${pick(frasesExtras)}  

  ${emocao}.  

  ${cta} ${pick(EMOJIS)}

  `;

}



function gerarConteudo(entrada) {

  const palavras = entrada.split(",").map(p => p.trim());

  const nome = palavras[0] || "produto";



  let respostas = [];

  for (let i = 1; i <= 10; i++) {

    const texto = gerarTextoPremium(nome).trim();

    const idTexto = `texto${i}`;

    respostas.push(`

      <div style="margin-bottom:18px; border-bottom:1px solid #333; padding-bottom:8px;">

        <b style="color:#FFD700;">${i}️⃣ Opção ${i}</b><br>

        <div id="${idTexto}" style="margin-top:5px;">${texto}</div>

        <button onclick="copiarTexto('${idTexto}', event)" 

          style="margin-top:6px; font-size:0.8rem; background:#FFD700; color:#000; border:none; padding:4px 10px; border-radius:15px; font-weight:600; cursor:pointer; transition:0.2s;">

          📋 Copiar

        </button>

      </div>

    `);

  }



  const hashtags = palavras

    .map(p => "#" + p.replace(/\s+/g, "").toLowerCase())

    .concat([

      "#GoldMind",

      "#LuxuryStyle",

      "#EssênciaOuro",

      "#EstiloComInteligencia",

      "#ModaPremium",

      "#DesignExclusivo",

      "#MarketingIA",

      "#InspiraçãoDeLuxo",

      "#PoderDoEstilo",

      "#GoldVibes"

    ])

    .slice(0, 10)

    .join(" ");



  return {

    resposta: respostas.join(""),

    hashtags

  };

}



function copiarTexto(id, event) {

  const texto = document.getElementById(id).innerText;

  navigator.clipboard.writeText(texto)

    .then(() => {

      const btn = event.target;

      const original = btn.innerText;

      btn.innerText = "✅ Copiado!";

      setTimeout(() => (btn.innerText = original), 1000);

    })

    .catch(() => alert("❌ Erro ao copiar o texto."));

}



function addMessage(text, sender = "bot") {

  const div = document.createElement("div");

  div.classList.add("msg", sender === "user" ? "user-msg" : "bot-msg");

  div.innerHTML = text;

  chat.appendChild(div);

  chat.scrollTop = chat.scrollHeight;

}



sendBtn.addEventListener("click", () => {

  const entrada = input.value.trim();

  if (!entrada) return;

  addMessage(entrada, "user");

  input.value = "";

  setTimeout(() => {

    const { resposta, hashtags } = gerarConteudo(entrada);

    addMessage(`${resposta}<br><small style="color:#FFD700;">${hashtags}</small>`, "bot");

  }, 900);

});



input.addEventListener("keypress", e => {

  if (e.key === "Enter") sendBtn.click();

});

</script>




</body>
</html>