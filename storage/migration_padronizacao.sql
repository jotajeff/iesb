-- ============================================================
-- Migração: Padronização do Banco de Dados
-- Conforme padronizacao.md
-- Data: 2026-07-28
-- ============================================================
-- ATENÇÃO: Execute em ambiente de staging antes de produção.
-- Faz backup dos dados antes de rodar.
-- ============================================================

START TRANSACTION;

-- ============================================================
-- 1. RENOMEAR COLUNAS DE DATA: criado_em → created_at
--    e atualizado_em → updated_at
-- ============================================================

-- alunos
ALTER TABLE `alunos`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CHANGE COLUMN `atualizado_em` `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- carousel
ALTER TABLE `carousel`
  CHANGE COLUMN `criado_em` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CHANGE COLUMN `atualizado_em` `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- carousel_item
ALTER TABLE `carousel_item`
  CHANGE COLUMN `criado_em` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CHANGE COLUMN `atualizado_em` `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- comentarios
ALTER TABLE `comentarios`
  CHANGE COLUMN `criado_em` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- corpo_docente
ALTER TABLE `corpo_docente`
  CHANGE COLUMN `criado_em` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- curriculo
ALTER TABLE `curriculo`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP;

-- cursos
-- created_at já existe, adicionar updated_at
ALTER TABLE `cursos`
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- cursos_pagamento
-- created_at já existe, adicionar updated_at
ALTER TABLE `cursos_pagamento`
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- detalhes
ALTER TABLE `detalhes`
  CHANGE COLUMN `criado_em` `created_at` DATETIME NOT NULL,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- disciplina
ALTER TABLE `disciplina`
  CHANGE COLUMN `criado_em` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- funcoes_docente
ALTER TABLE `funcoes_docente`
  CHANGE COLUMN `criado_em` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- imagem
ALTER TABLE `imagem`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- instituicao
ALTER TABLE `instituicao`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CHANGE COLUMN `atualizado_em` `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- logs_auditoria
-- created_at já existe, adicionar updated_at
ALTER TABLE `logs_auditoria`
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- material
ALTER TABLE `material`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- modalidade
ALTER TABLE `modalidade`
  ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- tipo_curso
ALTER TABLE `tipo_curso`
  ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- noticia
ALTER TABLE `noticia`
  CHANGE COLUMN `criado_em` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CHANGE COLUMN `atualizado_em` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- notificacao_leitura
ALTER TABLE `notificacao_leitura`
  ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- notificacao_leitura_aluno
ALTER TABLE `notificacao_leitura_aluno`
  ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- pre_inscricao
ALTER TABLE `pre_inscricao`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- segmento
ALTER TABLE `segmento`
  ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- sessao
ALTER TABLE `sessao`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- setores
ALTER TABLE `setores`
  ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- social
ALTER TABLE `social`
  ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- tarefas
ALTER TABLE `tarefas`
  CHANGE COLUMN `criado_em` `created_at` DATETIME NOT NULL,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- turmas
ALTER TABLE `turmas`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- turma_troca
ALTER TABLE `turma_troca`
  CHANGE COLUMN `criado_em` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- visitas_paginas
-- created_at já existe, adicionar updated_at
ALTER TABLE `visitas_paginas`
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- ============================================================
-- 2. PADRONIZAR CAMPO ativo: CHAR/ENUM 'S'/'N' → TINYINT(1) DEFAULT 1
-- ============================================================

-- alunos: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `alunos` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `alunos` SET `ativo` = '0' WHERE `ativo` = 'N';
UPDATE `alunos` SET `ativo` = '1' WHERE `ativo` = 's';
UPDATE `alunos` SET `ativo` = '0' WHERE `ativo` = 'n';
ALTER TABLE `alunos`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- carousel: ativo ENUM('S','N') → TINYINT(1) DEFAULT 1
UPDATE `carousel` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `carousel` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `carousel`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- carousel_item: ativo ENUM('S','N') → TINYINT(1) DEFAULT 1
UPDATE `carousel_item` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `carousel_item` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `carousel_item`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- corpo_docente: ativo ENUM('S','N') → TINYINT(1) DEFAULT 1
UPDATE `corpo_docente` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `corpo_docente` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `corpo_docente`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- curriculo: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `curriculo` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `curriculo` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `curriculo`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- cursos: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `cursos` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `cursos` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `cursos`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- cursos_pagamento: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `cursos_pagamento` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `cursos_pagamento` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `cursos_pagamento`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- detalhes: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `detalhes` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `detalhes` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `detalhes`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- disciplina: ativo ENUM('S','N') → TINYINT(1) DEFAULT 1
UPDATE `disciplina` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `disciplina` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `disciplina`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- funcoes_docente: ativo ENUM('S','N') → TINYINT(1) DEFAULT 1
UPDATE `funcoes_docente` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `funcoes_docente` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `funcoes_docente`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- imagem: ativa CHAR(1) → TINYINT(1) DEFAULT 1 (renomear para ativo)
UPDATE `imagem` SET `ativa` = '1' WHERE `ativa` = 'S' OR `ativa` = 's';
UPDATE `imagem` SET `ativa` = '0' WHERE `ativa` = 'N' OR `ativa` = 'n';
UPDATE `imagem` SET `ativa` = '1' WHERE `ativa` = '1';
UPDATE `imagem` SET `ativa` = '0' WHERE `ativa` = '0';
ALTER TABLE `imagem`
  CHANGE COLUMN `ativa` `ativo` TINYINT(1) DEFAULT 1;

-- material: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `material` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `material` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `material`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- modalidade: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `modalidade` SET `ativo` = '1' WHERE `ativo` = 'S' OR `ativo` = 's';
UPDATE `modalidade` SET `ativo` = '0' WHERE `ativo` = 'N' OR `ativo` = 'n';
ALTER TABLE `modalidade`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- tipo_curso: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `tipo_curso` SET `ativo` = '1' WHERE `ativo` = 'S' OR `ativo` = 's';
UPDATE `tipo_curso` SET `ativo` = '0' WHERE `ativo` = 'N' OR `ativo` = 'n';
ALTER TABLE `tipo_curso`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- segmento: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `segmento` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `segmento` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `segmento`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- setores: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `setores` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `setores` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `setores`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- tarefas: ativo CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1
UPDATE `tarefas` SET `ativo` = '1' WHERE `ativo` = 'S';
UPDATE `tarefas` SET `ativo` = '0' WHERE `ativo` = 'N';
ALTER TABLE `tarefas`
  CHANGE COLUMN `ativo` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- turmas: ativa CHAR(1) 'S'/'N' → TINYINT(1) DEFAULT 1 (renomear para ativo)
UPDATE `turmas` SET `ativa` = '1' WHERE `ativa` = 'S';
UPDATE `turmas` SET `ativa` = '0' WHERE `ativa` = 'N';
ALTER TABLE `turmas`
  CHANGE COLUMN `ativa` `ativo` TINYINT(1) DEFAULT 1;

-- paginas: ativa TINYINT(1) → já está ok, renomear para ativo
ALTER TABLE `paginas`
  CHANGE COLUMN `ativa` `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- ============================================================
-- 3. ADICIONAR ativo ONDE FALTA (tabelas críticas)
-- ============================================================

-- categoria_noticia: já tem ativo TINYINT(1), OK
-- cursos: já tem ativo TINYINT(1), OK
-- logs_auditoria: tabela de auditoria, não precisa de ativo
-- matriculas: adicionar ativo
ALTER TABLE `matriculas`
  ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`,
  ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `ativo`,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- notificacao: já tem ativo TINYINT(1), OK. Adicionar updated_at
ALTER TABLE `notificacao`
  ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- cursos_inscricao: adicionar ativo
ALTER TABLE `cursos_inscricao`
  ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1 AFTER `valor`;

-- ============================================================
-- COMMIT
-- ============================================================

COMMIT;
