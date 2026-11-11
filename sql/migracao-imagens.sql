-- Correção de imagens: vereadores (foto_id) e notícias (imagem_id). Idempotente, pode rodar mais de uma vez.
-- Rode no Neon: SQL Editor > colar tudo > Run.

-- 0) Acerta a sequência de ids (registros inseridos manualmente podem deixá-la atrasada)
DO $$ BEGIN PERFORM setval(pg_get_serial_sequence('imagens','id'), (SELECT COALESCE(MAX(id),1) FROM imagens)); EXCEPTION WHEN OTHERS THEN NULL; END $$;

-- 1) Registrar as imagens na tabela imagens (só se ainda não existirem)
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 292019, '/assets/imagens/vereadores/elias-fernandes.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/elias-fernandes.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 516486, '/assets/imagens/vereadores/alexandre-espeto.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/alexandre-espeto.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/png', 312916, '/assets/imagens/vereadores/Rosseto.png'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/Rosseto.png');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 731770, '/assets/imagens/vereadores/leandro-tur.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/leandro-tur.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 701146, '/assets/imagens/vereadores/oliane-santos.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/oliane-santos.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 303354, '/assets/imagens/vereadores/preto.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/preto.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 44581, '/assets/imagens/vereadores/professor-kleiton.jpeg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/professor-kleiton.jpeg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/png', 1554492, '/assets/imagens/vereadores/william-schumacher.png'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/william-schumacher.png');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 396669, '/assets/imagens/vereadores/beto-goleiro.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/beto-goleiro.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 320706, '/assets/imagens/vereadores/celmir-martello.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/celmir-martello.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 423367, '/assets/imagens/vereadores/daniel-bordim.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/daniel-bordim.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 61283, '/assets/imagens/vereadores/eder-fraga.jpeg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/eder-fraga.jpeg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 380262, '/assets/imagens/vereadores/filipe-gil.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/filipe-gil.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 520296, '/assets/imagens/vereadores/marquinhos.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/marquinhos.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 367102, '/assets/imagens/vereadores/neuza-do-salgado.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/neuza-do-salgado.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 343461, '/assets/imagens/vereadores/rodrigo-mendes.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/rodrigo-mendes.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 391502, '/assets/imagens/vereadores/zezo.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/zezo.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 140836, '/assets/imagens/noticias/camara-aprova-projeto-sobre-instalacao-de-gps-nos-veiculos-locados-pelo-municipio.jpeg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-aprova-projeto-sobre-instalacao-de-gps-nos-veiculos-locados-pelo-municipio.jpeg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 276884, '/assets/imagens/noticias/camara-aprova-projeto-que-cria-o-dia-municipal-do-protetor-de-animais.jpeg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-aprova-projeto-que-cria-o-dia-municipal-do-protetor-de-animais.jpeg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 148188, '/assets/imagens/noticias/camara-aprova-projeto-que-cria-estacionamentos-exclusivos-para-motoboys-nas-vias-publ.jpeg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-aprova-projeto-que-cria-estacionamentos-exclusivos-para-motoboys-nas-vias-publ.jpeg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 134353, '/assets/imagens/noticias/camara-realiza-audiencia-publica-com-autoridades-contra-o-pedagio-na-rs-118.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-realiza-audiencia-publica-com-autoridades-contra-o-pedagio-na-rs-118.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 345564, '/assets/imagens/noticias/vereadores-conquistam-1-6-milhoes-de-reais-para-hospital-de-alvorada.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/vereadores-conquistam-1-6-milhoes-de-reais-para-hospital-de-alvorada.jpg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 207688, '/assets/imagens/noticias/nota-oficial-da-camara-municipal-de-alvorada-sobre-o-transporte-coletivo-no-municipio.jpeg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/nota-oficial-da-camara-municipal-de-alvorada-sobre-o-transporte-coletivo-no-municipio.jpeg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/webp', 121762, '/assets/imagens/noticias/comissao-permanente-de-legislacao-justica-e-redacao-final-debate-temas-em-reuniao.webp'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/comissao-permanente-de-legislacao-justica-e-redacao-final-debate-temas-em-reuniao.webp');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 155420, '/assets/imagens/noticias/legislativo-aprova-projeto-que-cria-a-procuradoria-especial-em-defesa-dos-direitos-da.jpeg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/legislativo-aprova-projeto-que-cria-a-procuradoria-especial-em-defesa-dos-direitos-da.jpeg');
INSERT INTO imagens (mime, tamanho, arquivo_url)
SELECT 'image/jpeg', 235745, '/assets/imagens/noticias/camara-aprova-projeto-que-cria-a-frente-parlamentar-em-apoio-a-micro-e-pequena-empres.jpg'
WHERE NOT EXISTS (SELECT 1 FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-aprova-projeto-que-cria-a-frente-parlamentar-em-apoio-a-micro-e-pequena-empres.jpg');

-- 2) Ligar cada vereador à sua imagem (vereadores.foto_id)
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/elias-fernandes.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Elias Fernandes';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/alexandre-espeto.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Alexandre Espeto';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/Rosseto.png' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Rodinei Rosseto';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/leandro-tur.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Leandro Tur';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/oliane-santos.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Oliane Santos';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/preto.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Preto';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/professor-kleiton.jpeg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Professor Kleiton';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/william-schumacher.png' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'William Schumacher';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/beto-goleiro.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Beto Goleiro';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/celmir-martello.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Celmir Martello';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/daniel-bordim.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Daniel Bordim';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/eder-fraga.jpeg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Eder Fraga';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/filipe-gil.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Filipe Gil';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/marquinhos.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Marquinhos';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/neuza-do-salgado.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Neuza do Salgado';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/rodrigo-mendes.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Rodrigo Mendes';
UPDATE vereadores SET foto_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/vereadores/zezo.jpg' ORDER BY id LIMIT 1), foto_url = '', atualizado_em = now()
WHERE nome ILIKE 'Z_zo';

-- 3) Ligar cada notícia à sua imagem (noticias.imagem_id) e limpar imagem_url externa
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-aprova-projeto-sobre-instalacao-de-gps-nos-veiculos-locados-pelo-municipio.jpeg' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%gps%';
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-aprova-projeto-que-cria-o-dia-municipal-do-protetor-de-animais.jpeg' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%protetor de animais%';
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-aprova-projeto-que-cria-estacionamentos-exclusivos-para-motoboys-nas-vias-publ.jpeg' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%motoboys%';
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-realiza-audiencia-publica-com-autoridades-contra-o-pedagio-na-rs-118.jpg' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%RS-118%';
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/vereadores-conquistam-1-6-milhoes-de-reais-para-hospital-de-alvorada.jpg' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%hospital de alvorada%';
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/nota-oficial-da-camara-municipal-de-alvorada-sobre-o-transporte-coletivo-no-municipio.jpeg' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%transporte coletivo%';
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/comissao-permanente-de-legislacao-justica-e-redacao-final-debate-temas-em-reuniao.webp' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%Comiss%Permanente%';
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/legislativo-aprova-projeto-que-cria-a-procuradoria-especial-em-defesa-dos-direitos-da.jpeg' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%Procuradoria Especial%';
UPDATE noticias SET imagem_id = (SELECT id FROM imagens WHERE arquivo_url = '/assets/imagens/noticias/camara-aprova-projeto-que-cria-a-frente-parlamentar-em-apoio-a-micro-e-pequena-empres.jpg' ORDER BY id LIMIT 1), imagem_url = '', atualizado_em = now()
WHERE titulo ILIKE '%Frente Parlamentar%';

-- 4) Conferência (deve mostrar foto_id e imagem_id preenchidos)

