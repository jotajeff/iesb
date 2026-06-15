ALTER TABLE `alunos`
  ADD COLUMN `reset_token` VARCHAR(64) DEFAULT NULL AFTER `senha`,
  ADD COLUMN `reset_token_expires` DATETIME DEFAULT NULL AFTER `reset_token`;
