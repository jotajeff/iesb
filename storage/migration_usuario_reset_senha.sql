-- Migração: adiciona campos de redefinição de senha na tabela usuarios (staff).
ALTER TABLE `usuarios`
  ADD COLUMN `reset_token` varchar(64) DEFAULT NULL AFTER `foto`,
  ADD COLUMN `reset_token_expires` datetime DEFAULT NULL AFTER `reset_token`;
