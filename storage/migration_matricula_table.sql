-- Garante a tabela matricula (nucleo do ERP). Idempotente.
CREATE TABLE IF NOT EXISTS `matricula` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `id_aluno` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `id_pagamento` int(11) NOT NULL,
  `data_matricula` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ATIVA',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `origem` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SITE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
