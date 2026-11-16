-- Estrutura do banco SQLite da Clinitran
-- O arquivo clinitran.sqlite é criado automaticamente por src/Banco.php

CREATE TABLE IF NOT EXISTS mensagens (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    nome           TEXT    NOT NULL,
    email          TEXT    NOT NULL,
    telefone       TEXT    NOT NULL,
    assunto        TEXT    NOT NULL,
    data_preferida TEXT,
    mensagem       TEXT    NOT NULL,
    criado_em      TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

-- Controle de limite de envios e de tentativas de login por IP
CREATE TABLE IF NOT EXISTS tentativas (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    ip        TEXT    NOT NULL,
    acao      TEXT    NOT NULL,
    criado_em INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_tentativas_ip_acao ON tentativas (ip, acao, criado_em);
