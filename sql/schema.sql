-- Schema do painel de TVs da Câmara (PostgreSQL / Neon). Pode ser executado várias vezes.

CREATE TABLE IF NOT EXISTS usuarios (
  id          SERIAL PRIMARY KEY,
  nome        TEXT NOT NULL,
  email       TEXT NOT NULL UNIQUE,
  senha_hash  TEXT NOT NULL,
  ativo       BOOLEAN NOT NULL DEFAULT TRUE,
  criado_em   TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Fotos ficam no próprio banco: o disco da Vercel é somente leitura.
CREATE TABLE IF NOT EXISTS imagens (
  id         SERIAL PRIMARY KEY,
  mime       TEXT NOT NULL,
  dados      BYTEA NOT NULL,
  tamanho    INTEGER NOT NULL,
  criado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS vereadores (
  id             SERIAL PRIMARY KEY,
  tela           TEXT NOT NULL CHECK (tela IN ('noticias','qrcode')),
  ordem          INTEGER NOT NULL DEFAULT 0,
  nome           TEXT NOT NULL,
  gabinete       TEXT NOT NULL DEFAULT '',
  partido        TEXT NOT NULL DEFAULT '',
  foto_id        INTEGER REFERENCES imagens(id) ON DELETE SET NULL,
  logo_id        INTEGER REFERENCES imagens(id) ON DELETE SET NULL,
  atualizado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS noticias (
  id             SERIAL PRIMARY KEY,
  ordem          INTEGER NOT NULL DEFAULT 0,
  titulo         TEXT NOT NULL,
  link           TEXT NOT NULL DEFAULT '',
  imagem_id      INTEGER REFERENCES imagens(id) ON DELETE SET NULL,
  ativo          BOOLEAN NOT NULL DEFAULT TRUE,
  atualizado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS qrcodes (
  id             SERIAL PRIMARY KEY,
  ordem          INTEGER NOT NULL DEFAULT 0,
  titulo         TEXT NOT NULL,
  link           TEXT NOT NULL,
  ativo          BOOLEAN NOT NULL DEFAULT TRUE,
  atualizado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS configuracoes (
  chave  TEXT PRIMARY KEY,
  valor  TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS tentativas_login (
  id         BIGSERIAL PRIMARY KEY,
  ip         TEXT NOT NULL,
  criado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_vereadores_tela_ordem ON vereadores (tela, ordem, id);
CREATE INDEX IF NOT EXISTS idx_noticias_ordem ON noticias (ordem, id);
CREATE INDEX IF NOT EXISTS idx_qrcodes_ordem ON qrcodes (ordem, id);
CREATE INDEX IF NOT EXISTS idx_tentativas_ip ON tentativas_login (ip, criado_em);
