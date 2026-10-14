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
  foto_url       TEXT NOT NULL DEFAULT '',
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

ALTER TABLE vereadores ADD COLUMN IF NOT EXISTS foto_url TEXT NOT NULL DEFAULT '';

-- Migração idempotente: links externos e catálogo de partidos.
ALTER TABLE noticias ADD COLUMN IF NOT EXISTS imagem_url TEXT NOT NULL DEFAULT '';
CREATE TABLE IF NOT EXISTS partidos (
  id SERIAL PRIMARY KEY,
  sigla TEXT NOT NULL UNIQUE,
  nome TEXT NOT NULL DEFAULT '',
  logo_url TEXT NOT NULL DEFAULT '',
  logo_id INTEGER REFERENCES imagens(id) ON DELETE SET NULL
);
INSERT INTO partidos (sigla) VALUES ('MDB'),('PDT'),('PL'),('PRD'),('PSD'),('PSDB'),('PT'),('REPUBLICANOS'),('PP'),('PSB'),('UNIÃO'),('PODE'),('NOVO'),('PCdoB'),('PV'),('SOLIDARIEDADE') ON CONFLICT (sigla) DO NOTHING;
ALTER TABLE vereadores ADD COLUMN IF NOT EXISTS partido_id INTEGER REFERENCES partidos(id) ON DELETE SET NULL;
INSERT INTO partidos(sigla) SELECT DISTINCT UPPER(TRIM(partido)) FROM vereadores WHERE TRIM(partido) <> '' ON CONFLICT(sigla) DO NOTHING;
UPDATE vereadores v SET partido_id=p.id FROM partidos p WHERE UPPER(TRIM(v.partido))=p.sigla AND v.partido_id IS NULL;

-- Aproveita logos já armazenados dos vereadores para cada sigla, sem duplicar arquivos.
UPDATE partidos p SET logo_id = x.logo_id FROM (SELECT DISTINCT ON (UPPER(TRIM(partido))) UPPER(TRIM(partido)) AS sigla, logo_id FROM vereadores WHERE logo_id IS NOT NULL AND TRIM(partido) <> '' ORDER BY UPPER(TRIM(partido)), id) x WHERE p.sigla=x.sigla AND p.logo_id IS NULL;
