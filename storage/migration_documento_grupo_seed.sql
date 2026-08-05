-- Garante os grupos de documentos exigidos pela FK de storage_drive/documento
-- (FK: storage_drive.id_grupo -> documento_grupo.id)
INSERT IGNORE INTO `documento_grupo` (`id`, `descricao`, `ativo`, `created_at`, `updated_at`) VALUES
  (1, 'Alunos', 1, NOW(), NOW()),
  (2, 'Professores', 1, NOW(), NOW()),
  (3, 'Materiais', 1, NOW(), NOW()),
  (4, 'Financeiro', 1, NOW(), NOW()),
  (5, 'Contratos', 1, NOW(), NOW()),
  (6, 'Certificados', 1, NOW(), NOW());
