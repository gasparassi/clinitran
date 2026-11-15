<?php

/**
 * Modelo de configuração. Copie para "config/config.php" e preencha.
 *
 * O arquivo config.php fica fora do git (.gitignore) e fora da pasta pública,
 * pois guarda a senha da área administrativa e o token do WhatsApp.
 */

return [
    // Acesso à página de mensagens (public/admin/)
    // Gere o hash da senha com:  php bin/gerar-senha.php "sua senha"
    'admin' => [
        'usuario' => 'admin',
        'senha_hash' => '',
    ],

    // Aviso automático por WhatsApp (API oficial - Meta Cloud API)
    'whatsapp' => [
        'ativo' => false,

        // Painel da Meta > WhatsApp > Configuração da API
        'token' => 'COLE_AQUI_O_TOKEN_DE_ACESSO',
        'phone_number_id' => 'COLE_AQUI_O_ID_DO_NUMERO_REMETENTE',
        'versao_api' => 'v21.0',

        // Número da clínica que vai RECEBER o aviso (DDI + DDD + número, só dígitos)
        'destino' => '5527998330777',

        // Modelo de mensagem aprovado pela Meta, com 5 variáveis no corpo:
        // "Novo contato pelo site (protocolo {{1}}). Nome: {{2}}. Telefone: {{3}}. Assunto: {{4}}. Data de preferência: {{5}}."
        'modelo' => 'novo_contato_site',
        'idioma' => 'pt_BR',
    ],
];
