<?php

/**
 * Recebe os dados do formulário de contato (JSON via POST)
 * e grava na tabela "mensagens" do SQLite.
 */

declare(strict_types=1);

require __DIR__.'/../bootstrap.php';

use Clinitran\Banco;
use Clinitran\LimiteTentativas;
use Clinitran\NotificadorWhatsApp;
use Clinitran\Seguranca;
use Clinitran\ValidadorContato;

Seguranca::enviarCabecalhos();
header('Content-Type: application/json; charset=utf-8');

function responder(int $status, array $dados): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['sucesso' => false, 'erro' => 'Método não permitido.']);
}

$entrada = json_decode((string) file_get_contents('php://input'), true);
if (! is_array($entrada)) {
    responder(400, ['sucesso' => false, 'erro' => 'Requisição inválida.']);
}

// Honeypot: campo invisível para pessoas. Se veio preenchido, é robô.
// Respondemos "sucesso" para o robô não perceber, mas nada é gravado.
if (($entrada['empresa_site'] ?? '') !== '') {
    responder(201, ['sucesso' => true, 'id' => 0]);
}

$validador = new ValidadorContato;
if (! $validador->validar($entrada)) {
    responder(422, ['sucesso' => false, 'erro' => $validador->erro()]);
}
$contato = $validador->dados();

try {
    $pdo = Banco::conexao();

    // Até 5 mensagens por IP a cada 10 minutos
    $limite = new LimiteTentativas($pdo, 'contato', 5, 600);
    if ($limite->excedido()) {
        responder(429, ['sucesso' => false, 'erro' => 'Muitas mensagens enviadas. Aguarde alguns minutos ou fale pelo WhatsApp.']);
    }

    $comando = $pdo->prepare(
        'INSERT INTO mensagens (nome, email, telefone, assunto, data_preferida, mensagem)
         VALUES (:nome, :email, :telefone, :assunto, :data_preferida, :mensagem)'
    );
    $comando->execute([
        ':nome' => $contato['nome'],
        ':email' => $contato['email'],
        ':telefone' => $contato['telefone'],
        ':assunto' => $contato['assunto'],
        ':data_preferida' => $contato['data_preferida'] !== '' ? $contato['data_preferida'] : null,
        ':mensagem' => $contato['mensagem'],
    ]);

    $protocolo = (int) $pdo->lastInsertId();
    $limite->registrar();

    // Aviso automático para a clínica (só funciona se as variáveis WHATSAPP_* estiverem no .env)
    $notificado = NotificadorWhatsApp::notificar($protocolo, $contato);

    responder(201, ['sucesso' => true, 'id' => $protocolo, 'whatsapp_enviado' => $notificado]);
} catch (Throwable $e) {
    error_log('Erro ao salvar contato: '.$e->getMessage());
    responder(500, ['sucesso' => false, 'erro' => 'Erro ao salvar a mensagem. Tente novamente mais tarde.']);
}
