-- Sensoriamento Inteligente para Estufas
-- Estrutura atual do banco: uma estufa para vários usuários (1:N).
--
-- USO: banco novo de desenvolvimento ou produção inicial.
-- NÃO execute este arquivo sobre um banco que já possui tabelas.
-- Para bancos existentes, faça backup e use a migração correspondente.
-- Este arquivo não cria usuários, senhas, códigos de convite ou segredos.
-- Também não contém recursos de SMTP, confirmação de e-mail ou recuperação por e-mail.

CREATE TABLE estufas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  localizacao VARCHAR(150) DEFAULT NULL,
  area VARCHAR(50) DEFAULT NULL,
  cultivo_principal VARCHAR(100) DEFAULT NULL,
  setores TEXT DEFAULT NULL,
  observacoes TEXT DEFAULT NULL,
  thingspeak_canal_id VARCHAR(30) DEFAULT NULL,
  perfil_alerta VARCHAR(50) NOT NULL DEFAULT 'personalizado',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- A aplicação atual trabalha com uma estufa configurada e vários usuários vinculados.
INSERT INTO estufas (nome) VALUES ('Estufa principal');

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estufa_id INT UNSIGNED NOT NULL,
  nome VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  foto_perfil VARCHAR(255) DEFAULT NULL,
  termos_aceitos_em DATETIME NOT NULL,
  versao_sessao INT UNSIGNED NOT NULL DEFAULT 1,
  ultimo_login_em DATETIME DEFAULT NULL,
  senha_alterada_em DATETIME DEFAULT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_estufa FOREIGN KEY (estufa_id) REFERENCES estufas(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE configuracoes_alerta (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estufa_id INT UNSIGNED NOT NULL UNIQUE,
  temperatura_min DECIMAL(6,2) DEFAULT 20.00,
  temperatura_max DECIMAL(6,2) DEFAULT 28.00,
  umidade_min DECIMAL(6,2) DEFAULT 55.00,
  umidade_max DECIMAL(6,2) DEFAULT 75.00,
  pressao_min DECIMAL(7,2) DEFAULT 950.00,
  pressao_max DECIMAL(7,2) DEFAULT 1050.00,
  uv_max DECIMAL(8,2) DEFAULT 800.00,
  vento_max DECIMAL(8,2) DEFAULT 10.00,
  wifi_min DECIMAL(6,2) DEFAULT -80.00,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_configuracoes_estufa FOREIGN KEY (estufa_id) REFERENCES estufas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cultivos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estufa_id INT UNSIGNED NOT NULL,
  cultura VARCHAR(100) DEFAULT NULL,
  variedade VARCHAR(100) DEFAULT NULL,
  setor VARCHAR(100) DEFAULT NULL,
  fase VARCHAR(100) DEFAULT NULL,
  quantidade_plantas INT UNSIGNED DEFAULT NULL,
  data_plantio DATE DEFAULT NULL,
  previsao_colheita DATE DEFAULT NULL,
  observacoes TEXT DEFAULT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cultivos_estufa FOREIGN KEY (estufa_id) REFERENCES estufas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE diario_cultivo (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cultivo_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  descricao VARCHAR(500) NOT NULL,
  registrado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_diario_cultivo FOREIGN KEY (cultivo_id) REFERENCES cultivos(id) ON DELETE CASCADE,
  CONSTRAINT fk_diario_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE componentes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estufa_id INT UNSIGNED NOT NULL,
  nome VARCHAR(100) NOT NULL,
  modelo VARCHAR(100) DEFAULT NULL,
  field_thingspeak TINYINT UNSIGNED DEFAULT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'aguardando',
  observacoes TEXT DEFAULT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_componentes_estufa FOREIGN KEY (estufa_id) REFERENCES estufas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE alertas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estufa_id INT UNSIGNED NOT NULL,
  componente_id INT UNSIGNED DEFAULT NULL,
  tipo VARCHAR(50) NOT NULL,
  mensagem VARCHAR(255) NOT NULL,
  valor DECIMAL(10,2) DEFAULT NULL,
  limite_referencia DECIMAL(10,2) DEFAULT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'ativo',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolvido_em DATETIME DEFAULT NULL,
  CONSTRAINT fk_alertas_estufa FOREIGN KEY (estufa_id) REFERENCES estufas(id) ON DELETE CASCADE,
  CONSTRAINT fk_alertas_componente FOREIGN KEY (componente_id) REFERENCES componentes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tentativas_seguranca (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  acao VARCHAR(40) NOT NULL,
  chave_hash CHAR(64) NOT NULL,
  sucesso TINYINT(1) NOT NULL DEFAULT 0,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tentativas_consulta (acao, chave_hash, sucesso, criado_em)
) ENGINE=InnoDB;

CREATE TABLE auditoria (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED DEFAULT NULL,
  estufa_id INT UNSIGNED DEFAULT NULL,
  evento VARCHAR(80) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  user_agent_hash CHAR(64) NOT NULL,
  detalhes TEXT DEFAULT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_auditoria_usuario (usuario_id, criado_em),
  INDEX idx_auditoria_estufa (estufa_id, criado_em),
  CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_auditoria_estufa FOREIGN KEY (estufa_id) REFERENCES estufas(id) ON DELETE SET NULL
) ENGINE=InnoDB;
