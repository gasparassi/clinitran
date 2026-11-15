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

A publicação é automática: a cada `git push` na branch `main` que altere `public/index.html`
ou `public/assets/`, o workflow `.github/workflows/pages.yml` publica somente esses arquivos estáticos.

## Como executar

Requisitos: PHP 8.2 ou superior (extensões `pdo_sqlite`, `curl` e `mbstring`) e [Composer](https://getcomposer.org).

```bash
composer install     # gera o autoload em vendor/
composer serve       # inicia em http://localhost:8000
```

`composer serve` executa `php -S localhost:8000 -t public bin/router.php`. Só a pasta `public/` fica
acessível pelo navegador.

- Site: <http://localhost:8000>
- Web: <https://gasparassi.github.io/clinitran>
- Área administrativa (mensagens recebidas): <http://localhost:8000/admin/login.php>

O banco `database/clinitran.sqlite` é criado automaticamente no primeiro acesso.

### Configurar a área administrativa

1. Copie `config/config.exemplo.php` para `config/config.php`.
2. Gere o hash da senha com `composer gerar-senha -- "sua senha forte"` e cole o resultado em `'senha_hash'`.

Sem o `config.php`, o site funciona normalmente, mas a área administrativa fica desativada.

## Estrutura de pastas

```
Avaliacao_final/
├── composer.json / composer.lock      # Projeto Composer (autoload PSR-4: Clinitran\ → src/)
├── bootstrap.php                      # Carrega o autoload (usado por todas as páginas PHP)
├── public/                            # ÚNICA pasta acessível pela web
│   ├── index.html                     # Página única: Início, Serviços, Sobre nós, Contato
│   ├── assets/css/style.css           # Estilos próprios (personalização do Bootstrap)
│   ├── assets/js/main.js              # Interatividade do site
│   ├── assets/img/                    # Logo e ilustrações (SVG)
│   ├── api/contato.php                # Recebe e grava o formulário (JSON)
│   ├── admin/                         # login.php, mensagens.php, sair.php
│   └── .htaccess                      # Cabeçalhos de segurança (hospedagem Apache)
├── src/                               # Classes PHP (namespace Clinitran)
│   ├── Banco.php                      # Conexão PDO com o SQLite
│   ├── Configuracao.php               # Leitura de config/config.php
│   ├── ValidadorContato.php           # Validação do formulário no servidor
│   ├── LimiteTentativas.php           # Limite de envios e de logins por IP
│   ├── Autenticacao.php               # Login, sessão e token CSRF
│   ├── Seguranca.php                  # Cabeçalhos HTTP de segurança (CSP etc.)
│   └── NotificadorWhatsApp.php        # Aviso automático por WhatsApp (API oficial)
├── config/config.exemplo.php          # Modelo de configuração (config.php fica fora do git)
├── bin/router.php                     # Roteador do servidor embutido (desenvolvimento)
├── bin/gerar-senha.php                # Gera o hash da senha do admin
├── .github/workflows/pages.yml        # Publicação automática no GitHub Pages
├── database/schema.sql                # Tabelas "mensagens" e "tentativas"
├── Descrição Empresa Clinitran.docx   # Documento de identidade visual (etapa 1)
└── Descrição Empresa Clinitran.pdf
```

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
| Apenas `public/` é acessível: banco, configuração, código e `.git` ficam fora da web | estrutura de pastas, `bin/router.php` |
| Área administrativa com login, senha com hash (`password_hash`), sessão com cookie `HttpOnly` e `SameSite=Strict`, expiração por inatividade e token CSRF | `src/Autenticacao.php`, `public/admin/` |
| Limite de 5 tentativas de login erradas por IP a cada 15 minutos | `public/admin/login.php` |
| Limite de 5 mensagens por IP a cada 10 minutos e campo "honeypot" contra robôs | `public/api/contato.php` |
| Validação no servidor de tipo, tamanho e formato de cada campo (incluindo data real e dia útil) | `src/ValidadorContato.php` |
| Consultas com *prepared statements* (contra SQL injection) e saída escapada com `htmlspecialchars` (contra XSS) | `public/api/contato.php`, `public/admin/mensagens.php` |
| Cabeçalhos CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy e Permissions-Policy | `src/Seguranca.php`, `public/.htaccess` |
| Arquivos do CDN com verificação de integridade (SRI) | `public/index.html` |
| Erros do PHP nunca aparecem para o visitante (vão apenas para o log) | `bootstrap.php` |

> `composer serve` usa o servidor embutido do PHP, que serve só para desenvolvimento.
> Em produção, use Apache ou Nginx com o *document root* apontando para `public/` e HTTPS ativado.

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
3. Em `config/config.php`, preencha o token e o ID do número remetente na seção `whatsapp`
   e mude `'ativo'` para `true`.

Se o envio falhar, o contato continua salvo normalmente e o erro fica registrado no log do PHP.
