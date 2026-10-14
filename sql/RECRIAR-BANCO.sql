-- ATENÇÃO: APAGA DEFINITIVAMENTE TODOS OS DADOS do schema public.
-- Execute SOMENTE no SQL Editor do Neon, APÓS criar backup/branch.
-- NÃO execute se quiser preservar usuários, notícias ou fotos.
DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;
GRANT USAGE, CREATE ON SCHEMA public TO PUBLIC;
-- Depois execute sql/schema.sql e /setup.php?token=SEU_SETUP_TOKEN.
