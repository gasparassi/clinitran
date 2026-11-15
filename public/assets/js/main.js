/* ==========================================================
   Clinitran - JavaScript principal
   - Cards de serviços gerados dinamicamente + filtro
   - Modal com detalhes do serviço
   - Validação do formulário de contato e envio ao SQLite
   - Navbar, link ativo, botão topo, contadores e status
   ========================================================== */

/* ---------- 1. Dados dos serviços ---------- */
const servicos = [
  {
    id: 1,
    titulo: 'Exame médico',
    categoria: 'exames',
    assunto: 'Renovação',
    icone: 'bi-eye',
    resumo: 'Avaliação da visão e da saúde para dirigir com segurança.',
    descricao: 'Exame de aptidão física e mental exigido pelo Detran-ES. A visão é a parte mais importante: se você usa óculos ou lentes de contato, leve-os no dia.',
    itens: ['Acuidade visual, campo visual e visão de cores', 'Medida da pressão arterial', 'Avaliação de coordenação e reflexos', 'Avaliação auditiva para motoristas profissionais'],
    publico: 'todos os motoristas, de A a E'
  },
  {
    id: 2,
    titulo: 'Exame psicotécnico',
    categoria: 'exames',
    assunto: 'Primeira habilitação',
    icone: 'bi-clipboard2-check',
    resumo: 'Avaliação psicológica exigida para dirigir.',
    descricao: 'Avaliação psicológica feita por profissional do trânsito. É obrigatória na primeira habilitação e para quem exerce atividade remunerada ao volante.',
    itens: ['Testes de atenção', 'Testes de memória e raciocínio', 'Entrevista com o(a) psicólogo(a)'],
    publico: 'primeira habilitação e motoristas profissionais'
  },
  {
    id: 3,
    titulo: 'Renovação da CNH',
    categoria: 'habilitacao',
    assunto: 'Renovação',
    icone: 'bi-arrow-repeat',
    resumo: 'Renovação para todas as categorias: A, B, C, D e E.',
    descricao: 'Fazemos os exames necessários para renovar a sua carteira, de moto a carreta, mesmo que ela já esteja vencida.',
    itens: ['Exame médico completo', 'Exame psicotécnico, quando exigido', 'Orientação sobre os próximos passos no Detran-ES'],
    publico: 'motoristas de todas as categorias'
  },
  {
    id: 4,
    titulo: 'Primeira habilitação',
    categoria: 'habilitacao',
    assunto: 'Primeira habilitação',
    icone: 'bi-person-vcard',
    resumo: 'Os exames do início do processo para tirar a sua CNH.',
    descricao: 'Antes das aulas, o futuro motorista precisa ser aprovado nos exames médico e psicotécnico. Explicamos cada etapa.',
    itens: ['Exame médico', 'Exame psicotécnico', 'Orientação sobre as etapas seguintes'],
    publico: 'quem vai tirar a primeira CNH'
  },
  {
    id: 5,
    titulo: 'Adição e mudança de categoria',
    categoria: 'habilitacao',
    assunto: 'Adição ou mudança de categoria',
    icone: 'bi-truck',
    resumo: 'Para quem quer pilotar moto ou dirigir caminhão, ônibus e carreta.',
    descricao: 'Exames para incluir uma categoria na sua CNH (por exemplo, de B para AB) ou mudar para uma categoria profissional (C, D ou E).',
    itens: ['Exame médico', 'Exame psicotécnico, quando exigido', 'Orientação sobre o exame toxicológico (C, D e E)'],
    publico: 'motoristas que querem uma nova categoria'
  },
  {
    id: 6,
    titulo: 'Orientação completa',
    categoria: 'orientacao',
    assunto: 'Dúvidas',
    icone: 'bi-info-circle',
    resumo: 'Ajudamos em todo o processo, até com a carteira vencida.',
    descricao: 'Nossa equipe explica o passo a passo e o que levar no dia, para você não perder a viagem. Confirme sempre as exigências atuais no site do Detran-ES.',
    itens: ['O que fazer com a CNH vencida', 'Documentos necessários', 'Exame toxicológico para C, D e E'],
    publico: 'qualquer motorista com dúvidas'
  }
];

// Nome exibido na etiqueta de cada card
const nomesCategorias = {
  exames: 'Exames',
  habilitacao: 'Habilitação',
  orientacao: 'Orientação'
};

/* ---------- 2. Atalhos para elementos ---------- */
const listaServicos = document.getElementById('listaServicos');
const botoesFiltro = document.querySelectorAll('.btn-filtro');
const menu = document.getElementById('menu');
const btnTopo = document.getElementById('btnTopo');
const form = document.getElementById('formContato');

/* ---------- 3. Cards de serviços ---------- */

// Cria o HTML de um card a partir de um objeto de serviço
function criarCard(servico) {
  const coluna = document.createElement('div');
  coluna.className = 'col-12 col-md-6 col-lg-4';
  coluna.innerHTML = `
    <article class="card card-servico h-100">
      <div class="card-topo"><i class="bi ${servico.icone}"></i></div>
      <div class="card-body d-flex flex-column">
        <span class="etiqueta">${nomesCategorias[servico.categoria]}</span>
        <h3 class="card-title">${servico.titulo}</h3>
        <p class="card-text">${servico.resumo}</p>
        <button type="button" class="btn btn-outline-clinica mt-auto btn-detalhes" data-id="${servico.id}">
          Saiba mais
        </button>
      </div>
    </article>`;
  return coluna;
}

// Mostra na tela os serviços da categoria escolhida
function exibirServicos(categoria) {
  listaServicos.innerHTML = '';

  const filtrados = categoria === 'todos'
    ? servicos
    : servicos.filter((s) => s.categoria === categoria);

  if (filtrados.length === 0) {
    listaServicos.innerHTML = '<p class="text-center">Nenhum serviço encontrado.</p>';
    return;
  }

  filtrados.forEach((servico) => listaServicos.appendChild(criarCard(servico)));
}

// Evento de clique nos botões de filtro
botoesFiltro.forEach((botao) => {
  botao.addEventListener('click', () => {
    botoesFiltro.forEach((b) => b.classList.remove('ativo'));
    botao.classList.add('ativo');
    exibirServicos(botao.dataset.categoria);
  });
});

/* ---------- 4. Modal de detalhes ---------- */
const modalServico = new bootstrap.Modal(document.getElementById('modalServico'));
let servicoSelecionado = null;

function abrirDetalhes(id) {
  servicoSelecionado = servicos.find((s) => s.id === id);
  if (!servicoSelecionado) return;

  document.getElementById('modalServicoTitulo').textContent = servicoSelecionado.titulo;
  document.getElementById('modalServicoDescricao').textContent = servicoSelecionado.descricao;
  document.getElementById('modalServicoPublico').textContent = servicoSelecionado.publico;

  const lista = document.getElementById('modalServicoItens');
  lista.innerHTML = '';
  servicoSelecionado.itens.forEach((item) => {
    const li = document.createElement('li');
    li.textContent = item;
    lista.appendChild(li);
  });

  modalServico.show();
}

// Delegação de evento: os cards são criados depois, então escutamos o container
listaServicos.addEventListener('click', (evento) => {
  const botao = evento.target.closest('.btn-detalhes');
  if (botao) {
    abrirDetalhes(Number(botao.dataset.id));
  }
});

// "Quero agendar": fecha o modal e preenche o formulário de contato
document.getElementById('btnAgendarServico').addEventListener('click', () => {
  modalServico.hide();

  const assunto = document.getElementById('assunto');
  const mensagem = document.getElementById('mensagem');

  assunto.value = servicoSelecionado.assunto;
  assunto.dispatchEvent(new Event('change'));

  mensagem.value = `Olá! Gostaria de agendar: ${servicoSelecionado.titulo}.`;
  mensagem.dispatchEvent(new Event('input'));

  document.getElementById('contato').scrollIntoView();
  document.getElementById('nome').focus({ preventScroll: true });
});

/* ---------- 5. Validação do formulário ---------- */
const campos = {
  nome: document.getElementById('nome'),
  email: document.getElementById('email'),
  telefone: document.getElementById('telefone'),
  assunto: document.getElementById('assunto'),
  dataPreferida: document.getElementById('dataPreferida'),
  mensagem: document.getElementById('mensagem'),
  aceite: document.getElementById('aceite')
};

// Marca o campo como válido ou inválido e mostra a mensagem de erro
function marcarCampo(nomeCampo, erro) {
  const campo = campos[nomeCampo];
  const areaErro = document.getElementById('erro-' + nomeCampo);

  campo.classList.toggle('is-invalid', erro !== '');
  campo.classList.toggle('is-valid', erro === '' && campo.type !== 'checkbox');
  areaErro.textContent = erro;

  return erro === '';
}

// Cada função retorna '' quando o valor está correto ou o texto do erro
const regras = {
  nome(valor) {
    if (valor.trim() === '') return 'Informe seu nome.';
    if (valor.trim().split(/\s+/).length < 2) return 'Informe nome e sobrenome.';
    if (/\d/.test(valor)) return 'O nome não pode conter números.';
    return '';
  },
  email(valor) {
    const padrao = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    if (valor.trim() === '') return 'Informe seu e-mail.';
    if (!padrao.test(valor.trim())) return 'Digite um e-mail válido (ex.: nome@email.com).';
    return '';
  },
  telefone(valor) {
    const numeros = valor.replace(/\D/g, '');
    if (numeros === '') return 'Informe seu telefone.';
    if (numeros.length < 10) return 'O telefone deve ter DDD + número.';
    return '';
  },
  assunto(valor) {
    return valor === '' ? 'Selecione um assunto.' : '';
  },
  dataPreferida(valor) {
    if (campos.dataPreferida.closest('#grupoData').classList.contains('d-none')) return '';
    if (valor === '') return 'Escolha uma data de preferência.';

    const escolhida = new Date(valor + 'T00:00:00');
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);

    if (escolhida < hoje) return 'A data não pode estar no passado.';
    if (escolhida.getDay() === 0 || escolhida.getDay() === 6) return 'Atendemos apenas de segunda a sexta.';
    return '';
  },
  mensagem(valor) {
    if (valor.trim().length < 10) return 'A mensagem deve ter pelo menos 10 caracteres.';
    return '';
  },
  aceite() {
    return campos.aceite.checked ? '' : 'É necessário autorizar o contato.';
  }
};

function validarCampo(nomeCampo) {
  return marcarCampo(nomeCampo, regras[nomeCampo](campos[nomeCampo].value));
}

function validarFormulario() {
  let valido = true;
  Object.keys(campos).forEach((nomeCampo) => {
    if (!validarCampo(nomeCampo)) valido = false;
  });
  return valido;
}

// Valida cada campo ao sair dele (blur) e, se já tiver erro, enquanto digita
Object.keys(campos).forEach((nomeCampo) => {
  const campo = campos[nomeCampo];
  campo.addEventListener('blur', () => validarCampo(nomeCampo));
  campo.addEventListener('input', () => {
    if (campo.classList.contains('is-invalid')) validarCampo(nomeCampo);
  });
});
campos.aceite.addEventListener('change', () => validarCampo('aceite'));

// Máscara de telefone: (35) 99999-9999
campos.telefone.addEventListener('input', () => {
  let n = campos.telefone.value.replace(/\D/g, '').slice(0, 11);

  if (n.length > 10) {
    n = n.replace(/^(\d{2})(\d{5})(\d{0,4})$/, '($1) $2-$3');
  } else if (n.length > 6) {
    n = n.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
  } else if (n.length > 2) {
    n = n.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
  } else if (n.length > 0) {
    n = n.replace(/^(\d{0,2})$/, '($1');
  }
  campos.telefone.value = n;
});

// Contador de caracteres da mensagem
const contador = document.getElementById('contadorMensagem');
campos.mensagem.addEventListener('input', () => {
  const total = campos.mensagem.value.length;
  contador.textContent = `${total}/500`;
  contador.classList.toggle('limite', total >= 450);
});

// Evento change: mostra o campo de data só quando o assunto é um exame
campos.assunto.addEventListener('change', () => {
  const grupoData = document.getElementById('grupoData');
  const precisaData = campos.assunto.value !== '' && campos.assunto.value !== 'Dúvidas' && campos.assunto.value !== 'Outro';

  grupoData.classList.toggle('d-none', !precisaData);
  if (!precisaData) {
    campos.dataPreferida.value = '';
    campos.dataPreferida.classList.remove('is-invalid', 'is-valid');
  }
  validarCampo('assunto');
});

// Não permite escolher datas passadas no calendário
const hoje = new Date();
campos.dataPreferida.min = [
  hoje.getFullYear(),
  String(hoje.getMonth() + 1).padStart(2, '0'),
  String(hoje.getDate()).padStart(2, '0')
].join('-');

// Mostra um alerta acima do botão de envio
function mostrarAlerta(tipo, texto) {
  const alerta = document.getElementById('alertaFormulario');
  alerta.className = `alert alert-${tipo}`;
  alerta.textContent = texto;
}

/* ---------- WhatsApp: mensagem pronta para a clínica ---------- */
const WHATSAPP_CLINICA = '5527998330777'; // (27) 99833-0777

// Converte "2026-11-20" em "20/11/2026"
function formatarData(dataISO) {
  if (!dataISO) return '';
  const [ano, mes, dia] = dataISO.split('-');
  return `${dia}/${mes}/${ano}`;
}

// Monta o link wa.me com o texto do contato já preenchido
function criarLinkWhatsApp(dados, protocolo) {
  const linhas = [
    `Olá! Acabei de enviar um contato pelo site (protocolo nº ${protocolo}).`,
    `*Nome:* ${dados.nome}`,
    `*Telefone:* ${dados.telefone}`,
    `*Assunto:* ${dados.assunto}`
  ];

  if (dados.data_preferida) {
    linhas.push(`*Data de preferência:* ${formatarData(dados.data_preferida)}`);
  }
  linhas.push(`*Mensagem:* ${dados.mensagem}`);

  return `https://wa.me/${WHATSAPP_CLINICA}?text=${encodeURIComponent(linhas.join('\n'))}`;
}

// Acrescenta ao alerta de sucesso o botão que abre o WhatsApp
function adicionarBotaoWhatsApp(link) {
  const alerta = document.getElementById('alertaFormulario');
  const botao = document.createElement('a');

  botao.href = link;
  botao.target = '_blank';
  botao.rel = 'noopener';
  botao.className = 'btn btn-whatsapp mt-3';
  botao.innerHTML = '<i class="bi bi-whatsapp me-1"></i> Enviar também pelo WhatsApp';

  alerta.appendChild(document.createElement('br'));
  alerta.appendChild(botao);
}

// Envio do formulário (evento submit)
form.addEventListener('submit', async (evento) => {
  evento.preventDefault();

  if (!validarFormulario()) {
    mostrarAlerta('danger', 'Verifique os campos destacados em vermelho.');
    form.querySelector('.is-invalid').focus();
    return;
  }

  const btnEnviar = document.getElementById('btnEnviar');
  btnEnviar.disabled = true;
  btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

  const dados = {
    nome: campos.nome.value.trim(),
    email: campos.email.value.trim(),
    telefone: campos.telefone.value,
    assunto: campos.assunto.value,
    data_preferida: campos.dataPreferida.value,
    mensagem: campos.mensagem.value.trim(),
    empresa_site: document.getElementById('empresaSite').value // honeypot (deve vir vazio)
  };

  try {
    const resposta = await fetch('api/contato.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    const resultado = await resposta.json();

    if (resposta.ok && resultado.sucesso) {
      const primeiroNome = dados.nome.split(' ')[0];
      mostrarAlerta('success', `Obrigado, ${primeiroNome}! Sua mensagem foi registrada (protocolo nº ${resultado.id}).`);
      adicionarBotaoWhatsApp(criarLinkWhatsApp(dados, resultado.id));
      form.reset();
      Object.values(campos).forEach((c) => c.classList.remove('is-valid', 'is-invalid'));
      document.getElementById('grupoData').classList.add('d-none');
      contador.textContent = '0/500';
    } else {
      mostrarAlerta('danger', resultado.erro || 'Não foi possível enviar. Tente novamente.');
    }
  } catch (erro) {
    // Acontece quando o site é aberto sem o servidor PHP (ex.: duplo clique no index.html)
    mostrarAlerta('warning', 'Servidor indisponível. Inicie o site com "composer serve" para salvar as mensagens.');
  } finally {
    btnEnviar.disabled = false;
    btnEnviar.innerHTML = '<i class="bi bi-send me-1"></i> Enviar mensagem';
  }
});

/* ---------- 6. Navbar, link ativo e botão topo ---------- */
const linksMenu = document.querySelectorAll('.navbar-clinica .nav-link');
const secoes = document.querySelectorAll('main section[id]');

function aoRolar() {
  const posicao = window.scrollY;

  menu.classList.toggle('rolando', posicao > 50);
  btnTopo.classList.toggle('visivel', posicao > 400);

  // Descobre qual seção está visível para destacar o link do menu
  let secaoAtual = 'inicio';
  secoes.forEach((secao) => {
    if (posicao >= secao.offsetTop - 120) secaoAtual = secao.id;
  });

  // No fim da página, destaca o último item (Contato)
  if (window.innerHeight + posicao >= document.body.offsetHeight - 5) {
    secaoAtual = 'contato';
  }

  linksMenu.forEach((link) => {
    link.classList.toggle('active', link.getAttribute('href') === '#' + secaoAtual);
  });
}

window.addEventListener('scroll', aoRolar);

btnTopo.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// No celular, fecha o menu ao clicar em um link
const menuLinks = document.getElementById('menuLinks');
menuLinks.querySelectorAll('a').forEach((link) => {
  link.addEventListener('click', () => {
    if (menuLinks.classList.contains('show')) {
      bootstrap.Collapse.getOrCreateInstance(menuLinks).hide();
    }
  });
});

/* ---------- 7. Contadores animados (Sobre nós) ---------- */
function animarNumero(elemento) {
  const alvo = Number(elemento.dataset.alvo);
  const duracao = 1500;
  const inicio = performance.now();

  function passo(agora) {
    const progresso = Math.min((agora - inicio) / duracao, 1);
    const valor = Math.floor(alvo * progresso);
    elemento.textContent = (progresso === 1 && alvo >= 1000 ? '+' : '') + valor.toLocaleString('pt-BR');
    if (progresso < 1) requestAnimationFrame(passo);
  }
  requestAnimationFrame(passo);
}

// Só anima quando os números aparecem na tela
const observador = new IntersectionObserver((entradas) => {
  entradas.forEach((entrada) => {
    if (entrada.isIntersecting) {
      animarNumero(entrada.target);
      observador.unobserve(entrada.target);
    }
  });
}, { threshold: 0.6 });

document.querySelectorAll('.numero').forEach((n) => observador.observe(n));

/* ---------- 8. Status de atendimento (aberto/fechado) ---------- */
function atualizarStatus() {
  const agora = new Date();
  const dia = agora.getDay();          // 0 = domingo ... 6 = sábado
  const hora = agora.getHours() + agora.getMinutes() / 60;
  const status = document.getElementById('statusAtendimento');

  // Segunda a sexta, das 8h às 17h
  const diaUtil = dia >= 1 && dia <= 5;
  const aberto = diaUtil && hora >= 8 && hora < 17;

  status.classList.toggle('aberto', aberto);
  status.classList.toggle('fechado', !aberto);
  status.innerHTML = aberto
    ? '<i class="bi bi-door-open me-1"></i> Estamos abertos agora'
    : '<i class="bi bi-door-closed me-1"></i> Fechado no momento';
}

/* ---------- 9. Inicialização ---------- */
document.getElementById('anoAtual').textContent = new Date().getFullYear();
exibirServicos('todos');
atualizarStatus();
setInterval(atualizarStatus, 60000);
aoRolar();
