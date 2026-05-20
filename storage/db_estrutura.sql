-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 14/05/2026 às 15:35
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
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `matricula` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `curso_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('ativo','concluido','inativo','pendente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `data_matricula` date NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(90) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(90) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`id`, `usuario_id`, `matricula`, `curso_id`, `status`, `data_matricula`, `data_nascimento`, `telefone`, `endereco`, `cidade`, `estado`, `pais`, `observacoes`, `created_at`, `updated_at`) VALUES
(1, 3, 'IESB-000003', 2, 'ativo', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-14 10:25:01', '2026-05-14 10:25:01');

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

--
-- Despejando dados para a tabela `cursos`
--

INSERT INTO `cursos` (`id`, `titulo`, `slug`, `categoria`, `carga_horaria`, `preco`, `preco_promocional`, `descricao`, `conteudo`, `modulos`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Gestão Empresarial e Finanças', 'gestao-empresarial-financas', 'Gestão', 480, 2399.00, 1899.00, 'Formação completa em gestão com foco em finanças corporativas, indicadores e estratégia.', 'Módulo 1: Estratégia de Negócios\nMódulo 2: Análise Financeira\nMódulo 3: Jurisprudência Empresarial\nMódulo 4: TCC', 'Plano de negócios + Laboratórios práticos + Mentorias', 1, '2026-05-14 10:25:01', '2026-05-14 10:25:01'),
(2, 'Inteligência Artificial Aplicada', 'inteligencia-artificial-aplicada', 'Tecnologia', 240, 1599.00, 1299.00, 'Aplicação prática de IA nos setores de marketing, vendas e operações com Python + API.', 'Módulo 1: Introdução à IA\nMódulo 2: Machine Learning\nMódulo 3: MLOps\nMódulo 4: Projeto integrador', 'Hands-on em Python + Deploy na nuvem', 1, '2026-05-14 10:25:01', '2026-05-14 10:25:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `matriculas`
--

CREATE TABLE `matriculas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `curso_id` int(10) UNSIGNED NOT NULL,
  `status` enum('ativa','concluida','trancada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativa',
  `progresso` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `data_matricula` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_auditoria`
--

CREATE TABLE `logs_auditoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `perfil` enum('admin','aluno','sistema') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sistema',
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

--
-- Despejando dados para a tabela `logs_auditoria`
--

INSERT INTO `logs_auditoria` (`id`, `usuario_id`, `perfil`, `acao`, `entidade`, `entidade_id`, `descricao`, `ip`, `user_agent`, `sucesso`, `dados_antes`, `dados_depois`, `created_at`) VALUES
(1, 1, 'admin', 'login_sucesso', 'usuarios', '1', 'Login efetuado com sucesso.', '143.255.118.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, NULL, '{\"email\": \"admin@iesb.com.br\"}', '2026-05-14 09:40:56'),
(2, 1, 'admin', 'logout', 'usuarios', '1', 'Logout realizado.', '143.255.118.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, NULL, '{\"email\": \"admin@iesb.com.br\"}', '2026-05-14 09:56:05'),
(3, 1, 'admin', 'login_sucesso', 'usuarios', '1', 'Login efetuado com sucesso.', '143.255.118.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, NULL, '{\"email\": \"admin@iesb.com.br\"}', '2026-05-14 09:56:45'),
(4, 1, 'admin', 'logout', 'usuarios', '1', 'Logout realizado.', '143.255.118.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, NULL, '{\"email\": \"admin@iesb.com.br\"}', '2026-05-14 10:51:23'),
(5, 1, 'admin', 'login_sucesso', 'usuarios', '1', 'Login efetuado com sucesso.', '143.255.118.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, NULL, '{\"email\": \"admin@iesb.com.br\"}', '2026-05-14 15:04:30');

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

--
-- Despejando dados para a tabela `paginas`
--

INSERT INTO `paginas` (`id`, `slug`, `nome`, `ativa`, `created_at`, `updated_at`) VALUES
(1, 'home', 'IESB - Cursos Técnicos em Administração e Contabilidade', 1, '2026-05-13 12:34:47', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('admin','aluno') COLLATE utf8mb4_unicode_ci DEFAULT 'aluno',
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `telefone`, `foto`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'admin@iesb.com.br', '$2y$10$ROFAEwzRjML/BbvFyMkwfe.CEalau4HJ20Mv3LIl.ZFFwpRUltUhu', 'admin', NULL, NULL, 1, '2026-05-13 13:39:40', '2026-05-13 13:39:40'),
(2, 'Aluno Teste', 'aluno@iesb.com.br', '$2y$10$lAklHZjFf8eBdequmPMnje1S5AUJYJhUFKRmjKX/shac.TGIDvOM.', 'aluno', NULL, NULL, 1, '2026-05-13 13:39:40', '2026-05-13 13:39:40'),
(3, 'Aluno Extras', 'aluno.extra@iesb.com.br', '$2y$10$lAklHZjFf8eBdequmPMnje1S5AUJYJhUFKRmjKX/shac.TGIDvOM.', 'aluno', NULL, NULL, 1, '2026-05-14 10:25:01', '2026-05-14 10:25:01');

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
-- Despejando dados para a tabela `visitas_paginas`
--

INSERT INTO `visitas_paginas` (`id`, `pagina_id`, `ip`, `user_agent`, `endereco_pagina`, `data_visita`, `hora_visita`, `created_at`) VALUES
(6, 1, '143.255.118.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'https://inteligenciaeducacionalsouzabrazil.com/', '2026-05-14', '15:30:11', '2026-05-14 18:30:11');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricula` (`matricula`),
  ADD UNIQUE KEY `uk_alunos_usuario` (`usuario_id`),
  ADD KEY `idx_alunos_status` (`status`),
  ADD KEY `idx_alunos_curso` (`curso_id`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_cursos_categoria` (`categoria`),
  ADD KEY `idx_cursos_titulo` (`titulo`);

--
-- Índices de tabela `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_usuario_data` (`usuario_id`,`created_at`),
  ADD KEY `idx_logs_perfil_data` (`perfil`,`created_at`),
  ADD KEY `idx_logs_acao_data` (`acao`,`created_at`);

--
-- Índices de tabela `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_matricula_aluno_curso` (`aluno_id`,`curso_id`),
  ADD KEY `idx_matriculas_curso` (`curso_id`),
  ADD KEY `idx_matriculas_status` (`status`);

--
-- Índices de tabela `paginas`
--
ALTER TABLE `paginas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_paginas_slug` (`slug`),
  ADD KEY `idx_paginas_nome` (`nome`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `paginas`
--
ALTER TABLE `paginas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `visitas_paginas`
--
ALTER TABLE `visitas_paginas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alunos`
--
ALTER TABLE `alunos`
  ADD CONSTRAINT `alunos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alunos_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `fk_matriculas_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_matriculas_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `visitas_paginas`
--
ALTER TABLE `visitas_paginas`
  ADD CONSTRAINT `fk_visitas_pagina` FOREIGN KEY (`pagina_id`) REFERENCES `paginas` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
