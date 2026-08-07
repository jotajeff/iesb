-- Migração: garante colunas de vínculo matrícula/aluno na cursos_inscricao.
-- Idempotente via verificação em INFORMATION_SCHEMA.
SET @coluna_existe = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cursos_inscricao' AND COLUMN_NAME = 'id_aluno'
);

SET @sql = IF(@coluna_existe = 0,
  'ALTER TABLE `cursos_inscricao` ADD COLUMN `id_aluno` int(11) DEFAULT NULL AFTER `telefone`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @coluna_existe = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cursos_inscricao' AND COLUMN_NAME = 'id_matricula'
);

SET @sql = IF(@coluna_existe = 0,
  'ALTER TABLE `cursos_inscricao` ADD COLUMN `id_matricula` int(11) DEFAULT NULL AFTER `id_aluno`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
