CREATE DATABASE IF NOT EXISTS sistema_recrutamento_db;
USE sistema_recrutamento_db;

-- 1. Perfil do Candidato (Você)
CREATE TABLE IF NOT EXISTS perfil_candidato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    competencias TEXT, -- Armazena JSON ou texto corrido das suas habilidades (ex: PHP, Python)
    arquivo_cv VARCHAR(255) NOT NULL, -- Caminho do ficheiro PDF/Docx
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Agentes/Workers de Automação
CREATE TABLE IF NOT EXISTS agentes_busca (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_agente VARCHAR(50) NOT NULL,
    status ENUM('ocioso', 'executando', 'falhou') DEFAULT 'ocioso',
    ultima_atividade TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Histórico de Vagas Monitoradas e Candidaturas Realizadas
CREATE TABLE IF NOT EXISTS historico_vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agente_id INT NOT NULL,
    empresa VARCHAR(100) NOT NULL,
    titulo_vaga VARCHAR(150) NOT NULL,
    plataforma_alvo VARCHAR(100) NOT NULL, -- ex: LinkedIn, Portal Interno
    url_vaga TEXT NOT NULL,
    passo_atual ENUM('inicio', 'payload_preparado', 'upload_cv', 'formulario_preenchido', 'sucesso') DEFAULT 'inicio',
    status_processo ENUM('processando', 'pausado_falha', 'concluido', 'erro_fatal') DEFAULT 'processando',
    data_candidatura TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (agente_id) REFERENCES agentes_busca(id) ON DELETE CASCADE
);

-- Inserção de dados iniciais para testes
INSERT INTO agentes_busca (nome_agente, status) VALUES ('Agente_Scraper_01', 'ocioso');

DROP TABLE IF EXISTS perfil_candidato;

CREATE TABLE perfil_candidato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- 1. Dados Pessoais e Contato
    nome_completo VARCHAR(255) NOT NULL,
    cidade_pais VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(50) NOT NULL,
    portfolio_url VARCHAR(255),
    
    -- 2. Resumo da Experiência e Carreira
    formacao_academica TEXT NOT NULL,
    experiencia_laboral TEXT NOT NULL,
    principais_conquistas TEXT,
    
    -- 3 & 4. Competências (Hard e Soft Skills)
    hard_skills TEXT NOT NULL, -- Ex: PHP, MySQL, Docker, FastAPI
    certificacoes TEXT,
    soft_skills TEXT NOT NULL, -- Ex: Liderança, Autonomia, Adaptabilidade
    
    -- 5 & 6. Cultura e Objetivo
    valores_estilo TEXT,
    objetivo_profissional VARCHAR(255) NOT NULL
);

-- Inserção inicial com o perfil base estruturado
INSERT INTO perfil_candidato (
    nome_completo, cidade_pais, email, telefone, portfolio_url,
    formacao_academica, experiencia_laboral, principais_conquistas,
    hard_skills, certificacoes, soft_skills, valores_estilo, objetivo_profissional
) VALUES (
    'Augusto Silva', 'M\'banza-Kongo, Angola', 'augusto.silva@email.com', '+244 923 000 000', 'github.com/augusto-dev',
    'Mestrado em Engenharia Informática', 'Desenvolvedor Backend na TechAngola (2024-2026)', 'Engenharia do sistema SIGE_VOTO com balanço de carga Nginx e replicação Docker.',
    'PHP, MySQL, Docker, Python, FastAPI, Nginx', 'Certificado Avançado de Arquitetura de Sistemas', 'Autonomia, Resolução de problemas, Trabalho em equipe',
    'Foco em arquiteturas distribuídas, escalabilidade e metodologias ágeis.', 'Engenheiro de Software Backend / Engenheiro de Dados'
);