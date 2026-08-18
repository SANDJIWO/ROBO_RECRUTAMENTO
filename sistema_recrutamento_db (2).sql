-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05-Ago-2026 às 16:44
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_recrutamento_db`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `agentes_busca`
--

CREATE TABLE `agentes_busca` (
  `id` int(11) NOT NULL,
  `nome_agente` varchar(50) NOT NULL,
  `status` enum('ocioso','executando','falhou') DEFAULT 'ocioso',
  `ultima_atividade` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `agentes_busca`
--

INSERT INTO `agentes_busca` (`id`, `nome_agente`, `status`, `ultima_atividade`) VALUES
(1, 'Agente_Scraper_01', 'ocioso', '2026-08-05 11:09:24');

-- --------------------------------------------------------

--
-- Estrutura da tabela `historico_candidaturas`
--

CREATE TABLE `historico_candidaturas` (
  `id` int(11) NOT NULL,
  `vaga_id` int(11) NOT NULL,
  `url_vaga` varchar(255) NOT NULL,
  `percentual_match` decimal(5,2) NOT NULL,
  `status_submissao` enum('Submetida','Ignorada','Falha') NOT NULL,
  `detalhes_erro` text DEFAULT NULL,
  `executado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `perfil_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `historico_candidaturas`
--

INSERT INTO `historico_candidaturas` (`id`, `vaga_id`, `url_vaga`, `percentual_match`, `status_submissao`, `detalhes_erro`, `executado_em`, `perfil_id`) VALUES
(57, 1, 'http://localhost/robo_recrutamento/ver_vaga.php?id=1', '75.00', 'Submetida', NULL, '2026-08-05 14:29:12', 5),
(58, 2, 'http://localhost/robo_recrutamento/ver_vaga.php?id=2', '100.00', 'Submetida', NULL, '2026-08-05 14:29:39', 5),
(84, 1, 'http://localhost/robo_recrutamento/ver_vaga.php?id=1', '0.00', 'Falha', 'WebDriverException: Conexão perdida.', '2026-08-05 11:24:00', 4),
(86, 2, 'http://localhost/robo_recrutamento/ver_vaga.php?id=2', '0.00', 'Falha', 'WebDriverException: Conexão perdida.', '2026-08-05 11:24:40', 4),
(212, 3, 'http://localhost/robo_recrutamento/ver_vaga.php?id=3&perfil_id=8', '100.00', 'Submetida', NULL, '2026-08-05 13:15:05', 8),
(216, 1, 'http://localhost/robo_recrutamento/ver_vaga.php?id=1&perfil_id=8', '0.00', 'Ignorada', NULL, '2026-08-05 13:14:58', 8),
(217, 2, 'http://localhost/robo_recrutamento/ver_vaga.php?id=2&perfil_id=8', '0.00', 'Ignorada', NULL, '2026-08-05 13:15:02', 8),
(221, 3, 'http://localhost/robo_recrutamento/ver_vaga.php?id=3&perfil_id=5', '0.00', 'Ignorada', NULL, '2026-08-05 14:30:21', 5);

-- --------------------------------------------------------

--
-- Estrutura da tabela `historico_vagas`
--

CREATE TABLE `historico_vagas` (
  `id` int(11) NOT NULL,
  `agente_id` int(11) NOT NULL,
  `empresa` varchar(100) NOT NULL,
  `titulo_vaga` varchar(150) NOT NULL,
  `plataforma_alvo` varchar(100) NOT NULL,
  `url_vaga` text NOT NULL,
  `passo_atual` enum('inicio','payload_preparado','upload_cv','formulario_preenchido','sucesso') DEFAULT 'inicio',
  `status_processo` enum('processando','pausado_falha','concluido','erro_fatal') DEFAULT 'processando',
  `data_candidatura` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `historico_vagas`
--

INSERT INTO `historico_vagas` (`id`, `agente_id`, `empresa`, `titulo_vaga`, `plataforma_alvo`, `url_vaga`, `passo_atual`, `status_processo`, `data_candidatura`) VALUES
(1, 1, 'Tech Angola Solutions', 'Desenvolvedor Fullstack PHP/Python', 'Portal Interno Vagas', 'http://localhost:8080/vaga_exemplo_1.html', 'formulario_preenchido', 'erro_fatal', '2026-07-31 08:14:59'),
(2, 1, 'Google Test', 'Engenheiro de Software', 'Formulário Web', 'https://www.google.com', 'formulario_preenchido', 'erro_fatal', '2026-07-31 15:45:56');

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfil_candidato`
--

CREATE TABLE `perfil_candidato` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `cidade_pais` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(50) NOT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `formacao_academica` text NOT NULL,
  `experiencia_laboral` text NOT NULL,
  `principais_conquistas` text DEFAULT NULL,
  `hard_skills` text NOT NULL,
  `certificacoes` text DEFAULT NULL,
  `soft_skills` text NOT NULL,
  `valores_estilo` text DEFAULT NULL,
  `objetivo_profissional` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `perfil_candidato`
--

INSERT INTO `perfil_candidato` (`id`, `utilizador_id`, `nome_completo`, `cidade_pais`, `email`, `telefone`, `portfolio_url`, `formacao_academica`, `experiencia_laboral`, `principais_conquistas`, `hard_skills`, `certificacoes`, `soft_skills`, `valores_estilo`, `objetivo_profissional`) VALUES
(4, 4, 'Bernardo Kinavuide Paulo Lukoki', 'Luanda', 'lukokipaulo@gmail.com', '929646443', 'github.com/bernardo-dev', 'Engenharia infomática, UNIKIV, 2016', '5 anos', 'riador de sites', 'Word, Excel, PowerBI, PHP, Mysql, Nginx', 'Programação web', 'Autonomia, Resolução de problemas, Trabalho em equipe', 'Competente', 'Ajudar a empresa a crescer'),
(5, 5, 'Celestina Ariete Luwawa Panzo', 'Luanda', 'celestina@gmail.com', '938389866', 'github.com/celestina-dev', 'Enfermagem, IMTS', '5 anos', '1- Trabalhei como enfermeira chefe no hospital Maria Pia durante 4 anos;\r\n2- Estágio profissional realizado no Hospital geral do Uíge', 'Word, Excel, PowerBI, PHP, Mysql, Nginx', 'Informática na ótica de utilizador', 'Autonomia, Resolução de problemas, Trabalho em equipe', 'Trabalho em equipe', 'Ajudar a empresa a crescer'),
(6, 6, 'José Amaral Sandjiwo', 'Luanda', 'jsandjiwo@gmail.com', '989656443', 'github.com/amaral-dev', 'Engenharia Informática, IPIL, 2020', '2 anos', 'Licenciatura', 'Word, Excel, PowerBI', '1-Informática na ótica de utilizador;\r\n2- Analista de Dados com PowerBI', 'Resolução de problemas, Trabalho em equipe', 'Trabalho em grupo', 'Ajudar a empresa a crescer'),
(7, 7, 'Dalmo Chinjenje', 'Luanda, Angola', 'dalmochinjenje@gmail.com', '924959602', 'github.com/dalmo', 'Engenharia Informática, UMA, 2022', '1- Professor de programação;\r\n2- Desenvolvedor web - front-end\r\n3- Desenvolvedor web - back-end', '1- Participou no desenvolvimento da rede social amar Angola', 'CSS, PHP, MySQL, Docker, Nginx, Word, Excel, PowerBI, PowerPoint, C#', 'Certificado internacional em Design Gráfico', 'Resolução de problemas, Trabalho em equipe', 'Proeminente, pontual', 'Ajudar a empresa a crescer'),
(8, 8, 'Luisa Panzo Lukoki', 'Luanda', 'bernadeth@gmail.com', '938389844', 'github.com/bernadeth-dev', 'Medicina Geral, UNIVIV, 2022', 'Trabalhei como Doutor na Clínica Serve Med', 'Medica de Carreira', 'Medicina legal, consultora medicinal', 'Medicina legal', '', 'Trabalho em grupo', 'Ajudar a proteger vidas humanas');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `username`, `email`, `password`, `criado_em`) VALUES
(4, 'bernardo', 'lukokipaulo@gmail.com', '$2y$10$5EiKLuWaTa5F1YyV9pX4Cuj.knhASu5OdoUwqdXGv5HgbJ4ar79s.', '2026-07-31 18:48:24'),
(5, 'celestina', 'celestina@gmail.com', '$2y$10$f5XtHUtF2fPLONSZ9dbZ6uku8xTtUushnTSm44zlAs49XTkwLEwdi', '2026-08-01 08:34:51'),
(6, 'amaral', 'amaral@gmail.com', '$2y$10$U6lgBfYgLoTIzXx51o9oGuETFBo7cj73xirNUbjllAWxduOkPmRJS', '2026-08-01 09:47:34'),
(7, 'dalmo', 'dalmo@gmail.com', '$2y$10$nF3qxORyO88WgdcpfFszVOcNIFfnDyVyI20uFVgsvm566G7QKxR2G', '2026-08-01 10:26:52'),
(8, 'bernadeth', 'bernadeth@gmail.com', '$2y$10$Rrx643Gbuj1BoIEAXA3OXOnVUm5k5JEH/UVgFPPFhZJyDAAnoY8ZK', '2026-08-05 13:01:41');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vagas`
--

CREATE TABLE `vagas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `empresa` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `hard_skills_exigidas` text NOT NULL,
  `soft_skills_exigidas` text NOT NULL,
  `status_vaga` enum('Aberta','Fechada') DEFAULT 'Aberta',
  `criada_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_recrutador` varchar(255) DEFAULT 'recrutamento.empresa.exemplo@gmail.com'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `vagas`
--

INSERT INTO `vagas` (`id`, `titulo`, `empresa`, `descricao`, `hard_skills_exigidas`, `soft_skills_exigidas`, `status_vaga`, `criada_em`, `email_recrutador`) VALUES
(1, 'Engenheiro de Software Backend', 'TechAngola Lda', 'Responsável pelo desenvolvimento de arquiteturas e APIs distribuídas.', 'PHP, MySQL, Docker, Nginx', 'Autonomia, Resolução de problemas', 'Aberta', '2026-07-31 16:35:35', 'jsandjiwo@gmail.com'),
(2, 'Diretor Administrativo', 'BERFELI-TEc', '1- Responsavel pelo sector administrativo;\r\n2- Responsável do patrimonio.', 'Word, Excel, PowerBI', 'Adptabilidade', 'Aberta', '2026-07-31 17:19:35', 'berfeli313@gmail.com'),
(3, 'Medica Geral', 'ServMED', 'Medical Geral', 'Medicina legal, consultora medicinal', 'Autonomia, Adptabilidade', 'Aberta', '2026-08-05 13:09:26', 'berfeli313@gmail.com');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `agentes_busca`
--
ALTER TABLE `agentes_busca`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `historico_candidaturas`
--
ALTER TABLE `historico_candidaturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unica_submissao_por_vaga` (`vaga_id`,`status_submissao`);

--
-- Índices para tabela `historico_vagas`
--
ALTER TABLE `historico_vagas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agente_id` (`agente_id`);

--
-- Índices para tabela `perfil_candidato`
--
ALTER TABLE `perfil_candidato`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `vagas`
--
ALTER TABLE `vagas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agentes_busca`
--
ALTER TABLE `agentes_busca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `historico_candidaturas`
--
ALTER TABLE `historico_candidaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT de tabela `historico_vagas`
--
ALTER TABLE `historico_vagas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `perfil_candidato`
--
ALTER TABLE `perfil_candidato`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `vagas`
--
ALTER TABLE `vagas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `historico_candidaturas`
--
ALTER TABLE `historico_candidaturas`
  ADD CONSTRAINT `historico_candidaturas_ibfk_1` FOREIGN KEY (`vaga_id`) REFERENCES `vagas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `historico_vagas`
--
ALTER TABLE `historico_vagas`
  ADD CONSTRAINT `historico_vagas_ibfk_1` FOREIGN KEY (`agente_id`) REFERENCES `agentes_busca` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
