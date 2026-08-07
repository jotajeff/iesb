-- Migração: adiciona id_turma na tabela cursos_inscricao para matricular
-- o aluno na turma correta após o pagamento.
ALTER TABLE `cursos_inscricao`
  ADD COLUMN `id_turma` int(11) DEFAULT NULL AFTER `id_pagamento`;
