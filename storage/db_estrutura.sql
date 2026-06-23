-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 23/06/2026 às 16:33
-- Versão do servidor: 5.7.44-48
-- Versão do PHP: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `vanes415_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senha` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `reset_token` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `foto` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` char(1) COLLATE utf8_unicode_ci DEFAULT 'S',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `carousel`
--

CREATE TABLE `carousel` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `link` varchar(256) NOT NULL,
  `ativo` enum('S','N') NOT NULL DEFAULT 'S',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `criado_por` int(10) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `carousel_item`
--

CREATE TABLE `carousel_item` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_carousel` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `imagem` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `target` varchar(20) NOT NULL DEFAULT '_self',
  `texto_botao` varchar(50) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT '0',
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `ativo` enum('S','N') NOT NULL DEFAULT 'S',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `criado_por` int(10) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria_noticia`
--

CREATE TABLE `categoria_noticia` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `tabela_fg` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `id_fg` int(5) NOT NULL,
  `comentario` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `curriculo`
--

CREATE TABLE `curriculo` (
  `id` int(11) NOT NULL,
  `id_fk` int(11) NOT NULL,
  `tipo` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `conteudo` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` char(1) COLLATE utf8_unicode_ci DEFAULT 'S',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `carga_horaria` int(10) UNSIGNED DEFAULT '0',
  `preco` decimal(10,2) NOT NULL DEFAULT '0.00',
  `preco_promocional` decimal(10,2) DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `conteudo` text COLLATE utf8mb4_unicode_ci,
  `modulos` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos_iesb`
--

CREATE TABLE `cursos_iesb` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_curso` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `horario` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `local_curso` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modalidade` int(1) DEFAULT NULL,
  `nivel` int(1) DEFAULT NULL,
  `segmento` int(3) NOT NULL,
  `carga_horaria` int(5) NOT NULL,
  `imagem_card` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_ingresso` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `curso_calendario` date NOT NULL,
  `exibir_home` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `confirmado` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `ativo` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'S'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos_tipo`
--

CREATE TABLE `cursos_tipo` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `detalhes`
--

CREATE TABLE `detalhes` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `detalhe` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'S',
  `criado_em` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `endereco`
--

CREATE TABLE `endereco` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_fk` int(11) NOT NULL,
  `cep` varchar(9) COLLATE utf8_unicode_ci NOT NULL,
  `logradouro` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numero` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `uf` char(2) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `instituicao`
--

CREATE TABLE `instituicao` (
  `id` int(11) NOT NULL,
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
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_auditoria`
--

CREATE TABLE `logs_auditoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `perfil` enum('admin','aluno','professor','operador','sistema') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sistema',
  `acao` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidade` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entidade_id` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sucesso` tinyint(1) NOT NULL DEFAULT '1',
  `dados_antes` json DEFAULT NULL,
  `dados_depois` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `material`
--

CREATE TABLE `material` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `titulo` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `link` text COLLATE utf8_unicode_ci NOT NULL,
  `id_fk` int(11) NOT NULL,
  `ativo` char(1) COLLATE utf8_unicode_ci DEFAULT 'S',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `id_aluno` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `data_matricula` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('inscrito','matriculado','ativo','concluido','cancelado','inadimplente') COLLATE utf8_unicode_ci DEFAULT 'matriculado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `modalidade`
--

CREATE TABLE `modalidade` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 's'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `nivel`
--

CREATE TABLE `nivel` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `apresentacao` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 's'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `noticia`
--

CREATE TABLE `noticia` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `destaque` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('rascunho','publicado','arquivado') NOT NULL DEFAULT 'rascunho',
  `visualizacoes` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_categoria` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `paginas`
--

CREATE TABLE `paginas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `segmento`
--

CREATE TABLE `segmento` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'S'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id` int(11) NOT NULL,
  `setor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `social`
--

CREATE TABLE `social` (
  `id` int(11) NOT NULL,
  `id_fk` int(11) NOT NULL,
  `tipo` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `rede` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `link_perfil` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `setor` int(11) NOT NULL,
  `tarefa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `criado_em` datetime NOT NULL,
  `finalizada_em` datetime DEFAULT NULL,
  `situacao` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'criada',
  `ativo` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `criado_por` int(11) NOT NULL DEFAULT '1',
  `responsavel` int(11) NOT NULL DEFAULT '1',
  `prioridade` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turmas`
--

CREATE TABLE `turmas` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `ativa` char(1) COLLATE utf8_unicode_ci DEFAULT 'S',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma_professor`
--

CREATE TABLE `turma_professor` (
  `id_turma` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_vinculo` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma_troca`
--

CREATE TABLE `turma_troca` (
  `id` int(11) NOT NULL,
  `id_origem` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `id_aluno` int(11) NOT NULL,
  `motivo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('admin','aluno','operador','professor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `visitas_paginas`
--

CREATE TABLE `visitas_paginas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pagina_id` bigint(20) UNSIGNED NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `endereco_pagina` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_visita` date NOT NULL,
  `hora_visita` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `carousel`
--
ALTER TABLE `carousel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ativo` (`ativo`),
  ADD KEY `idx_slug` (`slug`);

--
-- Índices de tabela `carousel_item`
--
ALTER TABLE `carousel_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_carousel` (`id_carousel`),
  ADD KEY `idx_ordem` (`ordem`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `categoria_noticia`
--
ALTER TABLE `categoria_noticia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `curriculo`
--
ALTER TABLE `curriculo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_curriculo_usuario` (`id_fk`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_cursos_categoria` (`categoria`),
  ADD KEY `idx_cursos_titulo` (`titulo`);

--
-- Índices de tabela `cursos_iesb`
--
ALTER TABLE `cursos_iesb`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cursos_tipo`
--
ALTER TABLE `cursos_tipo`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `detalhes`
--
ALTER TABLE `detalhes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `endereco`
--
ALTER TABLE `endereco`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `instituicao`
--
ALTER TABLE `instituicao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_instituicao_documento` (`documento`),
  ADD UNIQUE KEY `uc_instituicao_email` (`email`);

--
-- Índices de tabela `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_usuario_data` (`usuario_id`,`created_at`),
  ADD KEY `idx_logs_perfil_data` (`perfil`,`created_at`),
  ADD KEY `idx_logs_acao_data` (`acao`,`created_at`);

--
-- Índices de tabela `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_aluno` (`id_aluno`),
  ADD KEY `id_turma` (`id_turma`);

--
-- Índices de tabela `modalidade`
--
ALTER TABLE `modalidade`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `noticia`
--
ALTER TABLE `noticia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_publicacao` (`data_publicacao`),
  ADD KEY `idx_destaque` (`destaque`),
  ADD KEY `fk_noticia_categoria` (`id_categoria`);

--
-- Índices de tabela `paginas`
--
ALTER TABLE `paginas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_paginas_slug` (`slug`),
  ADD KEY `idx_paginas_nome` (`nome`);

--
-- Índices de tabela `segmento`
--
ALTER TABLE `segmento`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `setores`
--
ALTER TABLE `setores`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `social`
--
ALTER TABLE `social`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tarefas_setor` (`setor`);

--
-- Índices de tabela `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Índices de tabela `turma_professor`
--
ALTER TABLE `turma_professor`
  ADD PRIMARY KEY (`id_turma`,`id_usuario`),
  ADD KEY `fk_vinculo_professor` (`id_usuario`);

--
-- Índices de tabela `turma_troca`
--
ALTER TABLE `turma_troca`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_troca_turma_origem` (`id_origem`),
  ADD KEY `fk_troca_turma_destino` (`id_destino`),
  ADD KEY `fk_troca_aluno` (`id_aluno`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `visitas_paginas`
--
ALTER TABLE `visitas_paginas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_visita_ip_pagina_data` (`ip`,`pagina_id`,`data_visita`),
  ADD KEY `idx_visitas_pagina_data` (`pagina_id`,`data_visita`),
  ADD KEY `idx_visitas_data_hora` (`data_visita`,`hora_visita`),
  ADD KEY `idx_visitas_ip` (`ip`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `carousel`
--
ALTER TABLE `carousel`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `carousel_item`
--
ALTER TABLE `carousel_item`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categoria_noticia`
--
ALTER TABLE `categoria_noticia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `curriculo`
--
ALTER TABLE `curriculo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos_iesb`
--
ALTER TABLE `cursos_iesb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos_tipo`
--
ALTER TABLE `cursos_tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `detalhes`
--
ALTER TABLE `detalhes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `endereco`
--
ALTER TABLE `endereco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `instituicao`
--
ALTER TABLE `instituicao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `material`
--
ALTER TABLE `material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `modalidade`
--
ALTER TABLE `modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `nivel`
--
ALTER TABLE `nivel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `noticia`
--
ALTER TABLE `noticia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `paginas`
--
ALTER TABLE `paginas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `segmento`
--
ALTER TABLE `segmento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `social`
--
ALTER TABLE `social`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `turma_troca`
--
ALTER TABLE `turma_troca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `visitas_paginas`
--
ALTER TABLE `visitas_paginas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `carousel_item`
--
ALTER TABLE `carousel_item`
  ADD CONSTRAINT `fk_carousel_item_carousel` FOREIGN KEY (`id_carousel`) REFERENCES `carousel` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `curriculo`
--
ALTER TABLE `curriculo`
  ADD CONSTRAINT `fk_curriculo_usuario` FOREIGN KEY (`id_fk`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`id_aluno`) REFERENCES `alunos` (`id`),
  ADD CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`id_turma`) REFERENCES `turmas` (`id`);

--
-- Restrições para tabelas `noticia`
--
ALTER TABLE `noticia`
  ADD CONSTRAINT `fk_noticia_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_noticia` (`id`);

--
-- Restrições para tabelas `turmas`
--
ALTER TABLE `turmas`
  ADD CONSTRAINT `turmas_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos_iesb` (`id`);

--
-- Restrições para tabelas `turma_professor`
--
ALTER TABLE `turma_professor`
  ADD CONSTRAINT `fk_vinculo_professor` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vinculo_turma` FOREIGN KEY (`id_turma`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `turma_troca`
--
ALTER TABLE `turma_troca`
  ADD CONSTRAINT `fk_troca_aluno` FOREIGN KEY (`id_aluno`) REFERENCES `alunos` (`id`),
  ADD CONSTRAINT `fk_troca_turma_destino` FOREIGN KEY (`id_destino`) REFERENCES `turmas` (`id`),
  ADD CONSTRAINT `fk_troca_turma_origem` FOREIGN KEY (`id_origem`) REFERENCES `turmas` (`id`);

--
-- Restrições para tabelas `visitas_paginas`
--
ALTER TABLE `visitas_paginas`
  ADD CONSTRAINT `fk_visitas_pagina` FOREIGN KEY (`pagina_id`) REFERENCES `paginas` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
