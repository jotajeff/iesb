-- ============================================================
-- Estrutura do Banco de Dados - IESB
-- Padronizado conforme padronizacao.md
-- Atualizado: 2026-07-28
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senha` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `reset_token` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `foto` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `carousel` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `link` varchar(256) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `criado_por` int(10) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE `carousel_item` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_carousel` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `imagem` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `target` varchar(20) NOT NULL DEFAULT '_self',
  `texto_botao` varchar(50) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `criado_por` int(10) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_carousel` (`id_carousel`),
  KEY `idx_ordem` (`ordem`),
  KEY `idx_ativo` (`ativo`),
  CONSTRAINT `fk_carousel_item_carousel` FOREIGN KEY (`id_carousel`) REFERENCES `carousel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE `categoria_noticia` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tabela_fg` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `id_fg` int(5) NOT NULL,
  `comentario` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `corpo_docente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_funcao` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_curso` (`id_curso`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_funcao` (`id_funcao`),
  CONSTRAINT `fk_corpo_docente_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos_iesb` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_corpo_docente_funcao` FOREIGN KEY (`id_funcao`) REFERENCES `funcoes_docente` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_corpo_docente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `curriculo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk` int(11) NOT NULL,
  `tipo` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `resumo` text COLLATE utf8_unicode_ci NOT NULL,
  `conteudo` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_curriculo_usuario` (`id_fk`),
  CONSTRAINT `fk_curriculo_usuario` FOREIGN KEY (`id_fk`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `cursos` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `carga_horaria` int(10) UNSIGNED DEFAULT 0,
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `preco_promocional` decimal(10,2) DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `conteudo` text COLLATE utf8mb4_unicode_ci,
  `modulos` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_cursos_categoria` (`categoria`),
  KEY `idx_cursos_titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `cursos_iesb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `resumo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_curso` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `horario` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `local_curso` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modalidade` int(1) DEFAULT NULL,
  `nivel` int(1) DEFAULT NULL,
  `segmento` int(3) NOT NULL,
  `publico_alvo` text COLLATE utf8_unicode_ci NOT NULL,
  `carga_horaria` int(5) NOT NULL,
  `imagem_card` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagem_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_ingresso` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `curso_calendario` date NOT NULL,
  `exibir_home` tinyint(1) NOT NULL DEFAULT 0,
  `confirmado` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `vagas` int(11) DEFAULT 0,
  `inscricoes_abertas` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `cursos_iesb_inscricao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `id_pagamento` int(11) NOT NULL,
  `descricao_pagamento` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpf` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asaas_customer` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asaas_payment` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invoice_url` text COLLATE utf8_unicode_ci,
  `status` enum('PENDENTE','AGUARDANDO','RECEBIDO','CONFIRMADO','CANCELADO','ESTORNADO') COLLATE utf8_unicode_ci DEFAULT 'PENDENTE',
  `valor` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_curso` (`id_curso`),
  KEY `id_pagamento` (`id_pagamento`),
  KEY `asaas_payment` (`asaas_payment`),
  CONSTRAINT `cursos_iesb_inscricao_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos_iesb` (`id`),
  CONSTRAINT `cursos_iesb_inscricao_ibfk_2` FOREIGN KEY (`id_pagamento`) REFERENCES `cursos_iesb_pagamento` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `cursos_iesb_pagamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `descricao` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo` enum('PIX','BOLETO','CARTAO') COLLATE utf8_unicode_ci DEFAULT NULL,
  `parcelas` int(11) DEFAULT 1,
  `valor` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_curso` (`id_curso`),
  CONSTRAINT `cursos_iesb_pagamento_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos_iesb` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `cursos_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `detalhes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `detalhe` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `disciplina` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `carga_horaria` smallint(6) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_id_curso` (`id_curso`),
  CONSTRAINT `fk_disciplinas_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos_iesb` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `endereco` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_fk` int(11) NOT NULL,
  `cep` varchar(9) COLLATE utf8_unicode_ci NOT NULL,
  `logradouro` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numero` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `uf` char(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `funcoes_docente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nome` (`nome`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `imagem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `legenda` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_fk` int(11) NOT NULL,
  `tabela_fk` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `instituicao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razao_social` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `nome_fantasia` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `dominio` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `documento` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `inscricao_estadual` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `senha` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `responsavel_nome` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo_cliente` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'PJ',
  `status` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'Ativo',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_instituicao_documento` (`documento`),
  UNIQUE KEY `uc_instituicao_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `logs_auditoria` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `perfil` enum('admin','aluno','professor','operador','sistema') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sistema',
  `acao` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidade` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entidade_id` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sucesso` tinyint(1) NOT NULL DEFAULT 1,
  `dados_antes` json DEFAULT NULL,
  `dados_depois` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_usuario_data` (`usuario_id`,`created_at`),
  KEY `idx_logs_perfil_data` (`perfil`,`created_at`),
  KEY `idx_logs_acao_data` (`acao`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `material` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `titulo` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `link` text COLLATE utf8_unicode_ci NOT NULL,
  `id_fk` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_aluno` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `data_matricula` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('inscrito','matriculado','ativo','concluido','cancelado','inadimplente') COLLATE utf8_unicode_ci DEFAULT 'matriculado',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_aluno` (`id_aluno`),
  KEY `id_turma` (`id_turma`),
  CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`id_aluno`) REFERENCES `alunos` (`id`),
  CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`id_turma`) REFERENCES `turmas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `modalidade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `nivel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `apresentacao` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `noticia` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `resumo` text,
  `conteudo` longtext NOT NULL,
  `imagem_capa` varchar(255) DEFAULT NULL,
  `legenda_imagem` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `autor` varchar(150) DEFAULT NULL,
  `data_publicacao` datetime NOT NULL,
  `data_evento` datetime DEFAULT NULL,
  `destaque` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('rascunho','publicado','arquivado') NOT NULL DEFAULT 'rascunho',
  `visualizacoes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_categoria` int(10) UNSIGNED DEFAULT NULL,
  `midia` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_data_publicacao` (`data_publicacao`),
  KEY `idx_destaque` (`destaque`),
  KEY `fk_noticia_categoria` (`id_categoria`),
  CONSTRAINT `fk_noticia_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_noticia` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE `notificacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario_origem` int(11) NOT NULL,
  `tipo_destino` enum('usuario','aluno','turma','curso') COLLATE utf8_unicode_ci NOT NULL,
  `id_destino` int(11) NOT NULL,
  `titulo` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `mensagem` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario_origem` (`id_usuario_origem`),
  CONSTRAINT `notificacao_ibfk_1` FOREIGN KEY (`id_usuario_origem`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `notificacao_leitura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_notificacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `lida_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_notificacao_usuario` (`id_notificacao`,`id_usuario`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `notificacao_leitura_ibfk_1` FOREIGN KEY (`id_notificacao`) REFERENCES `notificacao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notificacao_leitura_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `notificacao_leitura_aluno` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_notificacao` int(11) NOT NULL,
  `id_aluno` int(11) NOT NULL,
  `lida_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_notificacao` (`id_notificacao`),
  KEY `id_aluno` (`id_aluno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `paginas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_paginas_slug` (`slug`),
  KEY `idx_paginas_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `pre_inscricao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `situacao` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'recebido',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `segmento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `sessao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `badge` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apresenta` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titulo` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `texto` text COLLATE utf8_unicode_ci,
  `midia` tinyint(4) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `setores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `social` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk` int(11) NOT NULL,
  `tipo` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `rede` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `link_perfil` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setor` int(11) NOT NULL,
  `tarefa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `finalizada_em` datetime DEFAULT NULL,
  `situacao` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'criada',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_por` int(11) NOT NULL DEFAULT 1,
  `responsavel` int(11) NOT NULL DEFAULT 1,
  `prioridade` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_tarefas_setor` (`setor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `turmas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_curso` (`id_curso`),
  CONSTRAINT `turmas_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos_iesb` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `turma_professor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_turma` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_vinculo` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'A',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_vinculo_professor` (`id_usuario`),
  CONSTRAINT `fk_vinculo_professor` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vinculo_turma` FOREIGN KEY (`id_turma`) REFERENCES `turmas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `turma_troca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origem` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `id_aluno` int(11) NOT NULL,
  `motivo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_troca_turma_origem` (`id_origem`),
  KEY `fk_troca_turma_destino` (`id_destino`),
  KEY `fk_troca_aluno` (`id_aluno`),
  CONSTRAINT `fk_troca_aluno` FOREIGN KEY (`id_aluno`) REFERENCES `alunos` (`id`),
  CONSTRAINT `fk_troca_turma_destino` FOREIGN KEY (`id_destino`) REFERENCES `turmas` (`id`),
  CONSTRAINT `fk_troca_turma_origem` FOREIGN KEY (`id_origem`) REFERENCES `turmas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('admin','aluno','operador','professor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `visitas_paginas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pagina_id` bigint(20) UNSIGNED NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `endereco_pagina` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_visita` date NOT NULL,
  `hora_visita` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_visita_ip_pagina_data` (`ip`,`pagina_id`,`data_visita`),
  KEY `idx_visitas_pagina_data` (`pagina_id`,`data_visita`),
  KEY `idx_visitas_data_hora` (`data_visita`,`hora_visita`),
  KEY `idx_visitas_ip` (`ip`),
  CONSTRAINT `fk_visitas_pagina` FOREIGN KEY (`pagina_id`) REFERENCES `paginas` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
