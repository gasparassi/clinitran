<?php
/**
 * Lista as mensagens gravadas no SQLite (acesso somente com login).
 */

declare(strict_types=1);

require __DIR__.'/../../bootstrap.php';

use Clinitran\Autenticacao;
use Clinitran\Banco;
use Clinitran\Seguranca;

Seguranca::enviarCabecalhos();
Autenticacao::exigirLogin();

// Evita que o navegador guarde em cache dados pessoais dos clientes
header('Cache-Control: no-store');

$mensagens = Banco::conexao()
    ->query('SELECT * FROM mensagens ORDER BY id DESC')
    ->fetchAll();

$csrf = Autenticacao::tokenCsrf();

// Escapa o texto antes de exibir (evita injeção de HTML)
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Mensagens recebidas - Clinitran Exames</title>
  <link rel="icon" type="image/svg+xml" href="../assets/img/logo-icone.svg">
  <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,600;0,700;0,800;1,800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="secao-clara">
  <main class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <h1 class="h3 mb-0">Mensagens recebidas (<?= count($mensagens) ?>)</h1>
      <div class="d-flex gap-2">
        <a href="../index.html" class="btn btn-outline-clinica">Voltar ao site</a>
        <form method="post" action="sair.php">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <button type="submit" class="btn btn-primario">Sair</button>
        </form>
      </div>
    </div>

    <?php if (count($mensagens) === 0) { ?>
      <p class="alert alert-info">Nenhuma mensagem recebida ainda.</p>
    <?php } else { ?>
      <div class="table-responsive form-clinica p-0">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>#</th><th>Data</th><th>Nome</th><th>Contato</th><th>Assunto</th><th>Data pref.</th><th>Mensagem</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($mensagens as $m) { ?>
              <tr>
                <td><?= (int) $m['id'] ?></td>
                <td class="text-nowrap"><?= e($m['criado_em']) ?></td>
                <td><?= e($m['nome']) ?></td>
                <td><?= e($m['email']) ?><br><small><?= e($m['telefone']) ?></small></td>
                <td><?= e($m['assunto']) ?></td>
                <td><?= e($m['data_preferida'] ?? '-') ?></td>
                <td><?= nl2br(e($m['mensagem'])) ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    <?php } ?>
  </main>
</body>
</html>
