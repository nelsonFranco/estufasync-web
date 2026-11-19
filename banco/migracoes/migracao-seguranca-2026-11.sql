-- Execute uma única vez no banco que já recebeu a estrutura anterior.
-- Faça um backup antes de importar.

ALTER TABLE usuarios
  ADD COLUMN estufa_id INT UNSIGNED DEFAULT NULL AFTER id,
  ADD COLUMN termos_aceitos_em DATETIME DEFAULT NULL AFTER foto_perfil,
  ADD COLUMN versao_sessao INT UNSIGNED NOT NULL DEFAULT 1 AFTER termos_aceitos_em,
  ADD COLUMN ultimo_login_em DATETIME DEFAULT NULL AFTER versao_sessao,
  ADD COLUMN senha_alterada_em DATETIME DEFAULT NULL AFTER ultimo_login_em,
  ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1 AFTER senha_alterada_em;

UPDATE usuarios u JOIN estufas e ON e.usuario_id = u.id
SET u.estufa_id = e.id WHERE u.estufa_id IS NULL;

ALTER TABLE estufas DROP FOREIGN KEY fk_estufas_usuario;
ALTER TABLE estufas
  DROP COLUMN usuario_id,
  ADD COLUMN cultivo_principal VARCHAR(100) DEFAULT NULL AFTER area,
  ADD COLUMN setores TEXT DEFAULT NULL AFTER cultivo_principal;

INSERT INTO estufas (nome)
SELECT 'Estufa principal' WHERE NOT EXISTS (SELECT 1 FROM estufas);

UPDATE usuarios SET estufa_id = (SELECT MIN(id) FROM estufas) WHERE estufa_id IS NULL;
UPDATE usuarios SET termos_aceitos_em = COALESCE(termos_aceitos_em, NOW());

ALTER TABLE usuarios
  MODIFY estufa_id INT UNSIGNED NOT NULL,
  MODIFY termos_aceitos_em DATETIME NOT NULL,
  ADD CONSTRAINT fk_usuarios_estufa FOREIGN KEY (estufa_id) REFERENCES estufas(id) ON DELETE RESTRICT;

ALTER TABLE cultivos
  ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD COLUMN atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER criado_em;
ALTER TABLE diario_cultivo MODIFY descricao VARCHAR(500) NOT NULL;

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
