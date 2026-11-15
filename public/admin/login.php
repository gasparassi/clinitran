<?php
/**
 * Login da área administrativa.
 */

declare(strict_types=1);

require __DIR__.'/../../bootstrap.php';

use Clinitran\Autenticacao;
use Clinitran\Banco;
use Clinitran\LimiteTentativas;
use Clinitran\Seguranca;

Seguranca::enviarCabecalhos();
Autenticacao::iniciarSessao();

if (Autenticacao::logado()) {
    header('Location: mensagens.php');
    exit;
}

$configurado = Autenticacao::configuracao() !== null;
$erro = '';

if ($configurado && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Até 5 tentativas erradas por IP a cada 15 minutos
    $limite = new LimiteTentativas(Banco::conexao(), 'login', 5, 900);

    if (! Autenticacao::csrfValido($_POST['csrf'] ?? null)) {
        $erro = 'Sessão expirada. Tente novamente.';
    } elseif ($limite->excedido()) {
        $erro = 'Muitas tentativas. Aguarde 15 minutos.';
    } else {
        $usuario = is_string($_POST['usuario'] ?? null) ? $_POST['usuario'] : '';
        $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';

        if (Autenticacao::credenciaisValidas($usuario, $senha)) {
            Autenticacao::entrar();
            header('Location: mensagens.php');
            exit;
        }

        $limite->registrar();
        $erro = 'Usuário ou senha incorretos.'; // mensagem genérica de propósito
    }
}

$csrf = Autenticacao::tokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Área administrativa - Clinitran Exames</title>
  <link rel="icon" type="image/svg+xml" href="../assets/img/logo-icone.svg">
  <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,600;0,700;0,800;1,800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="secao-clara">
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-9 col-md-6 col-lg-4">
        <div class="text-center mb-4">
          <img src="../assets/img/logo-icone.svg" alt="Símbolo da Clinitran" width="64" height="64">
          <h1 class="h4 mt-3">Área administrativa</h1>
        </div>

        <?php if (! $configurado) { ?>
          <div class="alert alert-warning">
            O acesso ainda não foi configurado. Crie o arquivo <code>config/config.php</code>
            a partir de <code>config/config.exemplo.php</code> e defina a senha (veja o README).
          </div>
        <?php } else { ?>
          <form method="post" class="form-clinica">
            <?php if ($erro !== '') { ?>
              <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
              <label for="usuario" class="form-label">Usuário</label>
              <input type="text" class="form-control" id="usuario" name="usuario" autocomplete="username" required>
            </div>
            <div class="mb-3">
              <label for="senha" class="form-label">Senha</label>
              <input type="password" class="form-control" id="senha" name="senha" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primario w-100">Entrar</button>
          </form>
        <?php } ?>

        <p class="text-center mt-3"><a href="../index.html">Voltar ao site</a></p>
      </div>
    </div>
  </main>
</body>
</html>
