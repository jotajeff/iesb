-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 04/09/2026 às 15:15
-- Versão do servidor: 5.7.44-48
-- Versão do PHP: 8.4.24

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
-- Estrutura para tabela `acordo_pagamento`
--

CREATE TABLE `acordo_pagamento` (
  `id` int(11) NOT NULL,
  `tipo` tinyint(4) NOT NULL DEFAULT '1',
  `id_pre_inscricao` int(11) DEFAULT NULL,
  `id_curso_pagamento` int(11) NOT NULL,
  `id_curso_parcela_origem` int(11) DEFAULT NULL,
  `id_usuario_autorizacao` int(11) NOT NULL,
  `cpf` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_entrada` decimal(10,2) NOT NULL DEFAULT '0.00',
  `data_vencimento_entrada` date DEFAULT NULL,
  `total_parcelas` int(11) NOT NULL DEFAULT '1',
  `valor_demais_parcelas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tipo_desconto` enum('ALUNO','CONVENIO','BOLSA','CAMPANHA','NEGOCIACAO','OUTRO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NEGOCIACAO',
  `motivo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utilizado` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `recorrencia_cartao` tinyint(1) NOT NULL DEFAULT '0',
  `asaas_subscription` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio_recorrencia` date DEFAULT NULL,
  `data_fim_recorrencia` date DEFAULT NULL,
  `status_recorrencia` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `banner_aluno`
--

CREATE TABLE `banner_aluno` (
  `id` int(11) NOT NULL,
  `banner` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `texto` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `id_curso` int(11) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
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
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
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
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
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
-- Estrutura para tabela `chamada`
--

CREATE TABLE `chamada` (
  `id` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `id_turma_disciplina` int(11) NOT NULL,
  `id_usuario_professor` int(11) DEFAULT NULL,
  `data_aula` date NOT NULL,
  `numero_aula` int(11) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fim` time DEFAULT NULL,
  `conteudo` text COLLATE utf8_unicode_ci,
  `observacao` text COLLATE utf8_unicode_ci,
  `status` enum('ABERTA','FECHADA','CANCELADA') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ABERTA',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamada_presenca`
--

CREATE TABLE `chamada_presenca` (
  `id` int(11) NOT NULL,
  `id_chamada` int(11) NOT NULL,
  `id_matricula` int(11) NOT NULL,
  `presenca` enum('PRESENTE','AUSENTE','JUSTIFICADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PRESENTE',
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `tabela_fg` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `id_fg` int(5) NOT NULL,
  `comentario` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `corpo_docente`
--

CREATE TABLE `corpo_docente` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_funcao` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `curriculo`
--

CREATE TABLE `curriculo` (
  `id` int(11) NOT NULL,
  `id_fk` int(11) NOT NULL,
  `tipo` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `resumo` text COLLATE utf8_unicode_ci NOT NULL,
  `conteudo` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `resumo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_curso` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `horario` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `local_curso` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modalidade` int(1) DEFAULT NULL,
  `tipo_curso` int(1) DEFAULT NULL,
  `segmento` int(3) NOT NULL,
  `publico_alvo` text COLLATE utf8_unicode_ci NOT NULL,
  `carga_horaria` int(5) NOT NULL,
  `imagem_card` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagem_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_ingresso` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `curso_calendario` date NOT NULL,
  `exibir_home` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `confirmado` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `vagas` int(11) DEFAULT '0',
  `inscricoes_abertas` char(1) COLLATE utf8_unicode_ci DEFAULT 'S'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos_pagamento`
--

CREATE TABLE `cursos_pagamento` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `descricao` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo` enum('PIX','BOLETO','CARTAO','TODOS') COLLATE utf8_unicode_ci DEFAULT 'TODOS',
  `parcelas` int(11) DEFAULT '1',
  `valor` decimal(10,2) DEFAULT NULL,
  `desconto_percentual` decimal(5,2) NOT NULL DEFAULT '0.00',
  `desconto_data_limite` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
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
-- Estrutura para tabela `curso_parcela`
--

CREATE TABLE `curso_parcela` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_pagamento` int(11) NOT NULL,
  `id_turma` int(11) DEFAULT NULL,
  `id_pre_inscricao` int(11) DEFAULT NULL,
  `id_acordo_pagamento` int(11) DEFAULT NULL,
  `numero_parcela` int(11) DEFAULT NULL,
  `total_parcelas` int(11) DEFAULT NULL,
  `descricao_pagamento` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpf` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_aluno` int(11) DEFAULT NULL,
  `id_matricula` int(11) DEFAULT NULL,
  `asaas_customer` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asaas_payment` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invoice_url` text COLLATE utf8_unicode_ci,
  `bank_slip_url` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('PENDENTE','AGUARDANDO','RECEBIDO','CONFIRMADO','CANCELADO','ESTORNADO') COLLATE utf8_unicode_ci DEFAULT 'PENDENTE',
  `valor` decimal(10,2) DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `recorrencia_cartao` tinyint(1) NOT NULL DEFAULT '0',
  `asaas_subscription` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status_recorrencia` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_inicio_recorrencia` date DEFAULT NULL,
  `data_fim_recorrencia` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `detalhes`
--

CREATE TABLE `detalhes` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `detalhe` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `disciplina`
--

CREATE TABLE `disciplina` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `carga_horaria` smallint(6) NOT NULL,
  `ordem` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `documento`
--

CREATE TABLE `documento` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_grupo` int(10) UNSIGNED NOT NULL,
  `id_registro` int(10) UNSIGNED NOT NULL,
  `id_tipo` int(10) UNSIGNED NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `nome_drive` varchar(255) NOT NULL,
  `folder_id` varchar(120) DEFAULT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `tamanho` bigint(20) DEFAULT NULL,
  `file_id` varchar(120) NOT NULL,
  `versao` int(11) NOT NULL DEFAULT '1',
  `status` enum('nao_enviado','enviado','em_analise','aprovado','rejeitado','substituido') NOT NULL DEFAULT 'nao_enviado',
  `observacao` text,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `documento_grupo`
--

CREATE TABLE `documento_grupo` (
  `id` int(10) UNSIGNED NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `documento_tipo`
--

CREATE TABLE `documento_tipo` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_grupo` int(10) UNSIGNED NOT NULL,
  `descricao` varchar(120) NOT NULL,
  `obrigatorio` tinyint(1) NOT NULL DEFAULT '0',
  `ordem` int(11) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ementa`
--

CREATE TABLE `ementa` (
  `id` int(11) NOT NULL,
  `ementa` text COLLATE utf8_unicode_ci NOT NULL,
  `id_disciplina` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1'
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
-- Estrutura para tabela `estrutura_curricular`
--

CREATE TABLE `estrutura_curricular` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `carga_horaria` smallint(6) DEFAULT NULL,
  `versao` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1.0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estrutura_disciplina`
--

CREATE TABLE `estrutura_disciplina` (
  `id` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `id_disciplina` int(11) NOT NULL,
  `ordem` int(11) NOT NULL DEFAULT '0',
  `obrigatoria` tinyint(1) NOT NULL DEFAULT '1',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estrutura_modulo`
--

CREATE TABLE `estrutura_modulo` (
  `id` int(11) NOT NULL,
  `id_estrutura` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `ordem` int(11) NOT NULL DEFAULT '0',
  `carga_horaria` smallint(6) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcoes_docente`
--

CREATE TABLE `funcoes_docente` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagem`
--

CREATE TABLE `imagem` (
  `id` int(11) NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `legenda` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_fk` int(11) NOT NULL,
  `tabela_fk` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
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
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `integracao_google`
--

CREATE TABLE `integracao_google` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `refresh_token` text,
  `root_folder_id` varchar(255) DEFAULT NULL,
  `root_folder_nome` varchar(255) DEFAULT NULL,
  `email_workspace` varchar(255) DEFAULT NULL,
  `conectado` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `provedor` enum('google_drive') NOT NULL DEFAULT 'google_drive',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
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
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `matricula`
--

CREATE TABLE `matricula` (
  `id` int(11) NOT NULL,
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
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `matricula_disciplina`
--

CREATE TABLE `matricula_disciplina` (
  `id` int(11) NOT NULL,
  `id_matricula` int(11) NOT NULL,
  `id_turma_disciplina` int(11) NOT NULL,
  `situacao` enum('MATRICULADO','CURSANDO','APROVADO','REPROVADO','DISPENSADO','TRANCADO','CANCELADO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'MATRICULADO',
  `nota` decimal(5,2) DEFAULT NULL,
  `frequencia` decimal(5,2) DEFAULT NULL,
  `data_conclusao` date DEFAULT NULL,
  `observacao` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `modalidade`
--

CREATE TABLE `modalidade` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `modulo`
--

CREATE TABLE `modulo` (
  `id` int(11) NOT NULL,
  `nome` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `rota` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icone` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_categoria` int(10) UNSIGNED DEFAULT NULL,
  `midia` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacao`
--

CREATE TABLE `notificacao` (
  `id` int(11) NOT NULL,
  `id_usuario_origem` int(11) NOT NULL,
  `tipo_destino` enum('usuario','aluno','turma','curso') COLLATE utf8_unicode_ci NOT NULL,
  `id_destino` int(11) NOT NULL,
  `titulo` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `mensagem` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacao_email`
--

CREATE TABLE `notificacao_email` (
  `id` int(11) NOT NULL,
  `tipo_origem` enum('ACORDO','RENEGOCIACAO','OUTRO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ACORDO',
  `id_origem` int(11) NOT NULL,
  `id_pre_inscricao` int(11) DEFAULT NULL,
  `id_acordo_pagamento` int(11) DEFAULT NULL,
  `id_aluno` int(11) DEFAULT NULL,
  `nome_destinatario` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_destinatario` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `assunto` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mensagem` text COLLATE utf8_unicode_ci NOT NULL,
  `link` text COLLATE utf8_unicode_ci,
  `status` enum('PENDENTE','ENVIADO','ERRO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'PENDENTE',
  `data_envio` datetime DEFAULT NULL,
  `erro` text COLLATE utf8_unicode_ci,
  `id_usuario_envio` int(11) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacao_leitura`
--

CREATE TABLE `notificacao_leitura` (
  `id` int(11) NOT NULL,
  `id_notificacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `lida_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacao_leitura_aluno`
--

CREATE TABLE `notificacao_leitura_aluno` (
  `id` int(11) NOT NULL,
  `id_notificacao` int(11) NOT NULL,
  `id_aluno` int(11) NOT NULL,
  `lida_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacao_matricula`
--

CREATE TABLE `notificacao_matricula` (
  `id` int(11) NOT NULL,
  `id_aluno` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `paginas`
--

CREATE TABLE `paginas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pre_inscricao`
--

CREATE TABLE `pre_inscricao` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `situacao` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'recebido',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `segmento`
--

CREATE TABLE `segmento` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessao`
--

CREATE TABLE `sessao` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `badge` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apresenta` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titulo` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `texto` text COLLATE utf8_unicode_ci,
  `midia` tinyint(4) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id` int(11) NOT NULL,
  `setor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
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
  `link_perfil` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `storage_drive`
--

CREATE TABLE `storage_drive` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_grupo` int(10) UNSIGNED NOT NULL,
  `id_registro` int(10) UNSIGNED NOT NULL,
  `folder_id` varchar(120) NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `folder_link` varchar(512) DEFAULT NULL,
  `parent_folder_id` varchar(120) DEFAULT NULL,
  `nivel` tinyint(1) NOT NULL DEFAULT '1',
  `tipo` enum('root','grupo','registro','','','') NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `setor` int(11) NOT NULL,
  `tarefa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `finalizada_em` datetime DEFAULT NULL,
  `situacao` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'criada',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_por` int(11) NOT NULL DEFAULT '1',
  `responsavel` int(11) NOT NULL DEFAULT '1',
  `prioridade` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_curso`
--

CREATE TABLE `tipo_curso` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `apresentacao` text COLLATE utf8_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turmas`
--

CREATE TABLE `turmas` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_estrutura` int(11) DEFAULT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma_disciplina`
--

CREATE TABLE `turma_disciplina` (
  `id` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `id_disciplina` int(11) NOT NULL,
  `id_usuario_professor` int(11) DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `status` enum('PLANEJADA','EM_ANDAMENTO','CONCLUIDA','CANCELADA') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'PLANEJADA',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma_disciplina_professor`
--

CREATE TABLE `turma_disciplina_professor` (
  `id` int(11) NOT NULL,
  `id_turma_disciplina` int(11) NOT NULL,
  `id_usuario_professor` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
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
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `titulacao` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_funcao` int(11) NOT NULL,
  `tipo` enum('admin','aluno','operador','professor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_funcao`
--

CREATE TABLE `usuarios_funcao` (
  `id` int(11) NOT NULL,
  `nome` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_funcao_permissao`
--

CREATE TABLE `usuarios_funcao_permissao` (
  `id` int(11) NOT NULL,
  `id_funcao` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `consultar` tinyint(1) DEFAULT '0',
  `inserir` tinyint(1) DEFAULT '0',
  `editar` tinyint(1) DEFAULT '0',
  `excluir` tinyint(1) DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `visitas_paginas`
--

CREATE TABLE `visitas_paginas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pagina_id` bigint(20) UNSIGNED NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `referer` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utm_source` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utm_medium` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utm_campaign` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utm_term` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utm_content` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endereco_pagina` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_visita` date NOT NULL,
  `hora_visita` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `acordo_pagamento`
--
ALTER TABLE `acordo_pagamento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_acordo_pagamento_token` (`token`),
  ADD KEY `idx_pre_inscricao` (`id_pre_inscricao`),
  ADD KEY `idx_curso_pagamento` (`id_curso_pagamento`),
  ADD KEY `idx_usuario` (`id_usuario_autorizacao`);

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `banner_aluno`
--
ALTER TABLE `banner_aluno`
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
-- Índices de tabela `chamada`
--
ALTER TABLE `chamada`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_chamada_turma_disciplina_data` (`id_turma_disciplina`,`data_aula`),
  ADD KEY `idx_chamada_turma_disciplina` (`id_turma_disciplina`),
  ADD KEY `idx_chamada_professor` (`id_usuario_professor`),
  ADD KEY `idx_chamada_data` (`data_aula`),
  ADD KEY `idx_chamada_status` (`status`);

--
-- Índices de tabela `chamada_presenca`
--
ALTER TABLE `chamada_presenca`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_chamada_matricula` (`id_chamada`,`id_matricula`),
  ADD KEY `idx_chamada_presenca_chamada` (`id_chamada`),
  ADD KEY `idx_chamada_presenca_matricula` (`id_matricula`);

--
-- Índices de tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `corpo_docente`
--
ALTER TABLE `corpo_docente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curso` (`id_curso`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_funcao` (`id_funcao`);

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
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cursos_pagamento`
--
ALTER TABLE `cursos_pagamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Índices de tabela `cursos_tipo`
--
ALTER TABLE `cursos_tipo`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `curso_parcela`
--
ALTER TABLE `curso_parcela`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `id_pagamento` (`id_pagamento`),
  ADD KEY `asaas_payment` (`asaas_payment`);

--
-- Índices de tabela `detalhes`
--
ALTER TABLE `detalhes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `disciplina`
--
ALTER TABLE `disciplina`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_curso` (`id_curso`);

--
-- Índices de tabela `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_documento_tipo` (`id_tipo`),
  ADD KEY `idx_documento_grupo` (`id_grupo`,`id_registro`),
  ADD KEY `idx_documento_fileid` (`file_id`);

--
-- Índices de tabela `documento_grupo`
--
ALTER TABLE `documento_grupo`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `documento_tipo`
--
ALTER TABLE `documento_tipo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_documento_tipo_grupo` (`id_grupo`);

--
-- Índices de tabela `ementa`
--
ALTER TABLE `ementa`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `endereco`
--
ALTER TABLE `endereco`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `estrutura_curricular`
--
ALTER TABLE `estrutura_curricular`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estrutura_curricular_curso` (`id_curso`);

--
-- Índices de tabela `estrutura_disciplina`
--
ALTER TABLE `estrutura_disciplina`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_estrutura_disciplina` (`id_modulo`,`id_disciplina`),
  ADD KEY `idx_estrutura_disciplina_disciplina` (`id_disciplina`);

--
-- Índices de tabela `estrutura_modulo`
--
ALTER TABLE `estrutura_modulo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estrutura_modulo_estrutura` (`id_estrutura`);

--
-- Índices de tabela `funcoes_docente`
--
ALTER TABLE `funcoes_docente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nome` (`nome`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `imagem`
--
ALTER TABLE `imagem`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `instituicao`
--
ALTER TABLE `instituicao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_instituicao_documento` (`documento`),
  ADD UNIQUE KEY `uc_instituicao_email` (`email`);

--
-- Índices de tabela `integracao_google`
--
ALTER TABLE `integracao_google`
  ADD PRIMARY KEY (`id`);

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
-- Índices de tabela `matricula`
--
ALTER TABLE `matricula`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `matricula_disciplina`
--
ALTER TABLE `matricula_disciplina`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_matricula_turma_disciplina` (`id_matricula`,`id_turma_disciplina`),
  ADD KEY `idx_matricula_disciplina_matricula` (`id_matricula`),
  ADD KEY `idx_matricula_disciplina_turma` (`id_turma_disciplina`);

--
-- Índices de tabela `modalidade`
--
ALTER TABLE `modalidade`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `modulo`
--
ALTER TABLE `modulo`
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
-- Índices de tabela `notificacao`
--
ALTER TABLE `notificacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario_origem` (`id_usuario_origem`);

--
-- Índices de tabela `notificacao_email`
--
ALTER TABLE `notificacao_email`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notificacao_email_origem` (`tipo_origem`,`id_origem`),
  ADD KEY `idx_notificacao_email_acordo` (`id_acordo_pagamento`),
  ADD KEY `idx_notificacao_email_pre_inscricao` (`id_pre_inscricao`),
  ADD KEY `idx_notificacao_email_aluno` (`id_aluno`),
  ADD KEY `idx_notificacao_email_status` (`status`),
  ADD KEY `idx_notificacao_email_destinatario` (`email_destinatario`),
  ADD KEY `fk_notificacao_email_usuario` (`id_usuario_envio`);

--
-- Índices de tabela `notificacao_leitura`
--
ALTER TABLE `notificacao_leitura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_notificacao_usuario` (`id_notificacao`,`id_usuario`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `notificacao_leitura_aluno`
--
ALTER TABLE `notificacao_leitura_aluno`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_notificacao` (`id_notificacao`),
  ADD KEY `id_aluno` (`id_aluno`);

--
-- Índices de tabela `notificacao_matricula`
--
ALTER TABLE `notificacao_matricula`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `paginas`
--
ALTER TABLE `paginas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_paginas_slug` (`slug`),
  ADD KEY `idx_paginas_nome` (`nome`);

--
-- Índices de tabela `pre_inscricao`
--
ALTER TABLE `pre_inscricao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `segmento`
--
ALTER TABLE `segmento`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `sessao`
--
ALTER TABLE `sessao`
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
-- Índices de tabela `storage_drive`
--
ALTER TABLE `storage_drive`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_google_drive` (`id_grupo`,`id_registro`);

--
-- Índices de tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tarefas_setor` (`setor`);

--
-- Índices de tabela `tipo_curso`
--
ALTER TABLE `tipo_curso`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `idx_turmas_estrutura` (`id_estrutura`);

--
-- Índices de tabela `turma_disciplina`
--
ALTER TABLE `turma_disciplina`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_turma_disciplina` (`id_turma`,`id_disciplina`),
  ADD KEY `idx_turma_disciplina_professor` (`id_usuario_professor`),
  ADD KEY `fk_turma_disciplina_disciplina` (`id_disciplina`);

--
-- Índices de tabela `turma_disciplina_professor`
--
ALTER TABLE `turma_disciplina_professor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tdp_turma_disciplina_professor` (`id_turma_disciplina`,`id_usuario_professor`),
  ADD KEY `idx_tdp_turma_disciplina` (`id_turma_disciplina`),
  ADD KEY `idx_tdp_professor` (`id_usuario_professor`);

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
-- Índices de tabela `usuarios_funcao`
--
ALTER TABLE `usuarios_funcao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios_funcao_permissao`
--
ALTER TABLE `usuarios_funcao_permissao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_funcao_modulo` (`id_funcao`,`id_modulo`),
  ADD KEY `fk_fp_modulo` (`id_modulo`);

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
-- AUTO_INCREMENT de tabela `acordo_pagamento`
--
ALTER TABLE `acordo_pagamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `banner_aluno`
--
ALTER TABLE `banner_aluno`
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
-- AUTO_INCREMENT de tabela `chamada`
--
ALTER TABLE `chamada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `chamada_presenca`
--
ALTER TABLE `chamada_presenca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `corpo_docente`
--
ALTER TABLE `corpo_docente`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos_pagamento`
--
ALTER TABLE `cursos_pagamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos_tipo`
--
ALTER TABLE `cursos_tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `curso_parcela`
--
ALTER TABLE `curso_parcela`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `detalhes`
--
ALTER TABLE `detalhes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `disciplina`
--
ALTER TABLE `disciplina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `documento`
--
ALTER TABLE `documento`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `documento_grupo`
--
ALTER TABLE `documento_grupo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `documento_tipo`
--
ALTER TABLE `documento_tipo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ementa`
--
ALTER TABLE `ementa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `endereco`
--
ALTER TABLE `endereco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estrutura_curricular`
--
ALTER TABLE `estrutura_curricular`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estrutura_disciplina`
--
ALTER TABLE `estrutura_disciplina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estrutura_modulo`
--
ALTER TABLE `estrutura_modulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funcoes_docente`
--
ALTER TABLE `funcoes_docente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `imagem`
--
ALTER TABLE `imagem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `instituicao`
--
ALTER TABLE `instituicao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `integracao_google`
--
ALTER TABLE `integracao_google`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de tabela `matricula`
--
ALTER TABLE `matricula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `matricula_disciplina`
--
ALTER TABLE `matricula_disciplina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `modalidade`
--
ALTER TABLE `modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `modulo`
--
ALTER TABLE `modulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `noticia`
--
ALTER TABLE `noticia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacao`
--
ALTER TABLE `notificacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacao_email`
--
ALTER TABLE `notificacao_email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacao_leitura`
--
ALTER TABLE `notificacao_leitura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacao_leitura_aluno`
--
ALTER TABLE `notificacao_leitura_aluno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacao_matricula`
--
ALTER TABLE `notificacao_matricula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `paginas`
--
ALTER TABLE `paginas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pre_inscricao`
--
ALTER TABLE `pre_inscricao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `segmento`
--
ALTER TABLE `segmento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sessao`
--
ALTER TABLE `sessao`
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
-- AUTO_INCREMENT de tabela `storage_drive`
--
ALTER TABLE `storage_drive`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tipo_curso`
--
ALTER TABLE `tipo_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `turma_disciplina`
--
ALTER TABLE `turma_disciplina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `turma_disciplina_professor`
--
ALTER TABLE `turma_disciplina_professor`
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
-- AUTO_INCREMENT de tabela `usuarios_funcao`
--
ALTER TABLE `usuarios_funcao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios_funcao_permissao`
--
ALTER TABLE `usuarios_funcao_permissao`
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
-- Restrições para tabelas `chamada`
--
ALTER TABLE `chamada`
  ADD CONSTRAINT `fk_chamada_professor` FOREIGN KEY (`id_usuario_professor`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chamada_turma_disciplina` FOREIGN KEY (`id_turma_disciplina`) REFERENCES `turma_disciplina` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `chamada_presenca`
--
ALTER TABLE `chamada_presenca`
  ADD CONSTRAINT `fk_chamada_presenca_chamada` FOREIGN KEY (`id_chamada`) REFERENCES `chamada` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chamada_presenca_matricula` FOREIGN KEY (`id_matricula`) REFERENCES `matricula` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `corpo_docente`
--
ALTER TABLE `corpo_docente`
  ADD CONSTRAINT `fk_corpo_docente_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_corpo_docente_funcao` FOREIGN KEY (`id_funcao`) REFERENCES `funcoes_docente` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_corpo_docente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `curriculo`
--
ALTER TABLE `curriculo`
  ADD CONSTRAINT `fk_curriculo_usuario` FOREIGN KEY (`id_fk`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cursos_pagamento`
--
ALTER TABLE `cursos_pagamento`
  ADD CONSTRAINT `cursos_pagamento_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`);

--
-- Restrições para tabelas `curso_parcela`
--
ALTER TABLE `curso_parcela`
  ADD CONSTRAINT `curso_parcela_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `curso_parcela_ibfk_2` FOREIGN KEY (`id_pagamento`) REFERENCES `cursos_pagamento` (`id`);

--
-- Restrições para tabelas `disciplina`
--
ALTER TABLE `disciplina`
  ADD CONSTRAINT `fk_disciplinas_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `documento`
--
ALTER TABLE `documento`
  ADD CONSTRAINT `fk_documento_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `documento_grupo` (`id`),
  ADD CONSTRAINT `fk_documento_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `documento_tipo` (`id`);

--
-- Restrições para tabelas `documento_tipo`
--
ALTER TABLE `documento_tipo`
  ADD CONSTRAINT `fk_documento_tipo_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `documento_grupo` (`id`);

--
-- Restrições para tabelas `estrutura_curricular`
--
ALTER TABLE `estrutura_curricular`
  ADD CONSTRAINT `fk_estrutura_curricular_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `estrutura_disciplina`
--
ALTER TABLE `estrutura_disciplina`
  ADD CONSTRAINT `fk_estrutura_disciplina_disciplina` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estrutura_disciplina_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `estrutura_modulo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `estrutura_modulo`
--
ALTER TABLE `estrutura_modulo`
  ADD CONSTRAINT `fk_estrutura_modulo_estrutura` FOREIGN KEY (`id_estrutura`) REFERENCES `estrutura_curricular` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `matricula_disciplina`
--
ALTER TABLE `matricula_disciplina`
  ADD CONSTRAINT `fk_matricula_disciplina_matricula` FOREIGN KEY (`id_matricula`) REFERENCES `matricula` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_matricula_disciplina_turma_disciplina` FOREIGN KEY (`id_turma_disciplina`) REFERENCES `turma_disciplina` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `noticia`
--
ALTER TABLE `noticia`
  ADD CONSTRAINT `fk_noticia_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_noticia` (`id`);

--
-- Restrições para tabelas `notificacao`
--
ALTER TABLE `notificacao`
  ADD CONSTRAINT `notificacao_ibfk_1` FOREIGN KEY (`id_usuario_origem`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `notificacao_email`
--
ALTER TABLE `notificacao_email`
  ADD CONSTRAINT `fk_notificacao_email_acordo` FOREIGN KEY (`id_acordo_pagamento`) REFERENCES `acordo_pagamento` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notificacao_email_usuario` FOREIGN KEY (`id_usuario_envio`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `notificacao_leitura`
--
ALTER TABLE `notificacao_leitura`
  ADD CONSTRAINT `notificacao_leitura_ibfk_1` FOREIGN KEY (`id_notificacao`) REFERENCES `notificacao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notificacao_leitura_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `storage_drive`
--
ALTER TABLE `storage_drive`
  ADD CONSTRAINT `fk_google_drive_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `documento_grupo` (`id`);

--
-- Restrições para tabelas `turmas`
--
ALTER TABLE `turmas`
  ADD CONSTRAINT `fk_turmas_estrutura` FOREIGN KEY (`id_estrutura`) REFERENCES `estrutura_curricular` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `turmas_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`);

--
-- Restrições para tabelas `turma_disciplina`
--
ALTER TABLE `turma_disciplina`
  ADD CONSTRAINT `fk_turma_disciplina_disciplina` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turma_disciplina_professor` FOREIGN KEY (`id_usuario_professor`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turma_disciplina_turma` FOREIGN KEY (`id_turma`) REFERENCES `turmas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `turma_disciplina_professor`
--
ALTER TABLE `turma_disciplina_professor`
  ADD CONSTRAINT `fk_tdp_professor` FOREIGN KEY (`id_usuario_professor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tdp_turma_disciplina` FOREIGN KEY (`id_turma_disciplina`) REFERENCES `turma_disciplina` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Restrições para tabelas `usuarios_funcao_permissao`
--
ALTER TABLE `usuarios_funcao_permissao`
  ADD CONSTRAINT `fk_fp_funcao` FOREIGN KEY (`id_funcao`) REFERENCES `usuarios_funcao` (`id`),
  ADD CONSTRAINT `fk_fp_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id`);

--
-- Restrições para tabelas `visitas_paginas`
--
ALTER TABLE `visitas_paginas`
  ADD CONSTRAINT `fk_visitas_pagina` FOREIGN KEY (`pagina_id`) REFERENCES `paginas` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
