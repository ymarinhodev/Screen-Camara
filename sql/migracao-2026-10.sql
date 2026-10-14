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
