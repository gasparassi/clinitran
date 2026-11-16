# Clinitran Exames – Exames médicos e psicotécnicos para CNH

Site institucional da Clinitran Exames, clínica de trânsito em Colatina (ES) que realiza exames médicos
e psicotécnicos para CNH (renovação, primeira habilitação, adição e mudança de categoria), desenvolvido
como atividade avaliativa do Módulo I – Desenvolvimento Web Front-end.

**Tecnologias:** HTML5, CSS3, Bootstrap 5.3, JavaScript (ES6+) e PHP 8.2+ puro com Composer
(autoload PSR-4) + SQLite, usado para salvar o formulário de contato.

## Demonstração online

Versão estática publicada no GitHub Pages: <https://gasparassi.github.io/clinitran/>

O GitHub Pages não executa PHP. Por isso, nessa versão o formulário é validado normalmente,
mas em vez de gravar no banco oferece o envio dos dados pelo WhatsApp da clínica. A área
administrativa não é publicada. Para a versão completa, execute o projeto como descrito abaixo.

A publicação é automática: a cada `git push` na branch `main` que altere o `index.html` ou as pastas
`css/`, `js/`, `imagens/`, `videos/` ou `paginas/`, o workflow `.github/workflows/pages.yml` publica
somente esses arquivos estáticos (os `.php` são removidos).

## Como executar

Requisitos: PHP 8.2 ou superior (extensões `pdo_sqlite`, `curl` e `mbstring`) e [Composer](https://getcomposer.org).

```bash
composer install     # gera o autoload em vendor/
composer serve       # inicia em http://localhost:8000
```

`composer serve` executa `php -S localhost:8000 bin/router.php`. O roteador só libera o `index.html`
e as pastas do site; as pastas internas (`src/`, `database/`, `vendor/`...) respondem 404, assim como o `.env`.

- Site: <http://localhost:8000>
- Web: <https://gasparassi.github.io/clinitran>
- Área administrativa (mensagens recebidas): <http://localhost:8000/paginas/admin/login.php>

O banco `database/clinitran.sqlite` é criado automaticamente no primeiro acesso.

### Configurar a área administrativa

Os segredos ficam no arquivo **`.env`** (padrão de variáveis de ambiente, lido com
[`vlucas/phpdotenv`](https://github.com/vlucas/phpdotenv)), que nunca vai para o git.

1. Copie `.env.example` para `.env`.
2. Execute `composer gerar-senha -- "sua senha forte"` e cole no `.env` a linha `ADMIN_SENHA_HASH='...'`
   que ele mostra. Mantenha as **aspas simples**: o hash contém `$`, que ferramentas como Docker e o
   shell interpretariam como variável.

Sem o `.env`, o site funciona normalmente, mas a área administrativa fica desativada.

Em hospedagens que permitem definir variáveis de ambiente no painel, use os mesmos nomes do
`.env.example`. As variáveis do servidor **têm prioridade** sobre o arquivo `.env`.

## Estrutura de pastas

```
Avaliacao_final/                       # Pasta principal (raiz do site)
├── index.html                         # Página principal: Início, Serviços, Sobre nós, Contato
├── css/
│   └── style.css                      # Estilos próprios (personalização do Bootstrap)
├── js/
│   └── main.js                        # Interatividade do site
├── imagens/                           # Logo e ilustrações (SVG/PNG)
├── paginas/
│   └── admin/                         # Área administrativa: login.php, mensagens.php, sair.php
├── api/
│   └── contato.php                    # Recebe e grava o formulário (JSON)
├── Descrição Empresa Clinitran.docx   # Documento de identidade visual (etapa 1)
├── Descrição Empresa Clinitran.pdf
├── README.md
│
│   # ---- Arquivos internos (bloqueados para o navegador) ----
├── src/                               # Classes PHP (namespace Clinitran)
│   ├── Banco.php                      # Conexão PDO com o SQLite
│   ├── Configuracao.php               # Leitura das variáveis de ambiente (.env)
│   ├── ValidadorContato.php           # Validação do formulário no servidor
│   ├── LimiteTentativas.php           # Limite de envios e de logins por IP
│   ├── Autenticacao.php               # Login, sessão e token CSRF
│   ├── Seguranca.php                  # Cabeçalhos HTTP de segurança (CSP etc.)
│   └── NotificadorWhatsApp.php        # Aviso automático por WhatsApp (API oficial)
├── database/schema.sql                # Tabelas "mensagens" e "tentativas"
├── bin/router.php                     # Roteador do servidor embutido (lista de permissões)
├── bin/gerar-senha.php                # Gera o hash da senha do admin
├── .env.example                       # Modelo das variáveis de ambiente (o .env fica fora do git)
├── bootstrap.php                      # Carrega o autoload e o .env (usado por todas as páginas PHP)
├── composer.json / composer.lock      # Projeto Composer (autoload PSR-4: Clinitran\ → src/)
├── .htaccess                          # Bloqueios e cabeçalhos de segurança (hospedagem Apache)
└── .github/workflows/pages.yml        # Publicação automática no GitHub Pages
```

Novos arquivos seguem a mesma lógica: estilos em `css/`, scripts em `js/`, imagens em `imagens/`,
vídeos em `videos/` e outras páginas em `paginas/`. Todos usam caminhos relativos ao `index.html`.

Para instalar um pacote no futuro, use `composer require vendor/pacote`. Ele fica disponível
automaticamente em todas as páginas que incluem o `bootstrap.php`.

## Funcionalidades em JavaScript

- Cards de serviços gerados a partir de um array, com filtro por categoria (evento `click`).
- Modal de detalhes preenchido dinamicamente. O botão "Quero agendar" preenche o formulário.
- Validação do formulário campo a campo (`blur`, `input`, `submit`), com mensagens de erro.
- Máscara de telefone e contador de caracteres da mensagem.
- Campo "Data de preferência" exibido apenas quando o assunto é um exame (evento `change`). Datas passadas e fins de semana não são aceitos.
- Envio com `fetch` para a API PHP, com alerta de sucesso ou de erro.
- Após o envio, botão "Enviar também pelo WhatsApp", que abre o WhatsApp do visitante com os dados do contato já preenchidos (link `wa.me`).
- Navbar que muda ao rolar, link ativo conforme a seção e botão "voltar ao topo".
- Números animados na seção "Sobre nós" (`IntersectionObserver`).
- Indicador de "Aberto/Fechado" calculado pelo dia e pela hora atuais (seg. a sex., 8h às 17h).

## Segurança

| Proteção | Onde |
|---|---|
| Somente o `index.html` e as pastas do site são acessíveis; banco, configuração, código, `vendor/` e `.git` respondem 404 | `bin/router.php`, `.htaccess` |
| Área administrativa com login, senha com hash (`password_hash`), sessão com cookie `HttpOnly` e `SameSite=Strict`, expiração por inatividade e token CSRF | `src/Autenticacao.php`, `paginas/admin/` |
| Limite de 5 tentativas de login erradas por IP a cada 15 minutos | `paginas/admin/login.php` |
| Limite de 5 mensagens por IP a cada 10 minutos e campo "honeypot" contra robôs | `api/contato.php` |
| Validação no servidor de tipo, tamanho e formato de cada campo (incluindo data real e dia útil) | `src/ValidadorContato.php` |
| Consultas com *prepared statements* (contra SQL injection) e saída escapada com `htmlspecialchars` (contra XSS) | `api/contato.php`, `paginas/admin/mensagens.php` |
| Cabeçalhos CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy e Permissions-Policy | `src/Seguranca.php`, `.htaccess`, `<meta>` no `index.html` |
| Arquivos do CDN com verificação de integridade (SRI) | `index.html` |
| Segredos (hash da senha e token) em variáveis de ambiente / `.env`, fora do git e inacessível pelo navegador | `.env`, `.gitignore`, `bin/router.php`, `.htaccess` |
| Erros do PHP nunca aparecem para o visitante (vão apenas para o log) | `bootstrap.php` |

> `composer serve` usa o servidor embutido do PHP, que serve só para desenvolvimento.
> Em produção, use Apache (o `.htaccess` já bloqueia as pastas internas) ou Nginx com regras
> equivalentes, sempre com HTTPS ativado.

## Aviso automático por WhatsApp (opcional)

O site já funciona sem esta etapa: depois de salvar o contato, o visitante pode clicar em
"Enviar também pelo WhatsApp", que abre a conversa com a clínica e a mensagem já preenchida.

Para que **o servidor** avise a clínica automaticamente a cada contato, usando a API oficial
do WhatsApp (Meta Cloud API):

1. Crie uma conta no Meta Business e um app com o produto **WhatsApp**. Cadastre um número
   remetente, que deve ser diferente do número que vai receber os avisos.
2. Crie e envie para aprovação um modelo de mensagem chamado `novo_contato_site`
   (idioma `pt_BR`), com 5 variáveis:
   `Novo contato pelo site (protocolo {{1}}). Nome: {{2}}. Telefone: {{3}}. Assunto: {{4}}. Data de preferência: {{5}}.`
3. No `.env`, preencha `WHATSAPP_TOKEN` e `WHATSAPP_PHONE_NUMBER_ID` e mude `WHATSAPP_ATIVO` para `true`.

Se o envio falhar, o contato continua salvo normalmente e o erro fica registrado no log do PHP.
