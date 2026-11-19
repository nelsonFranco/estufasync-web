-- Sensoriamento Inteligente para Estufas
-- Migração incremental: remove recursos que dependiam de SMTP.
--
-- Use este arquivo SOMENTE se a migração de segurança anterior já foi importada.
-- Execute uma única vez e faça um backup do banco antes da importação.
-- Esta migração não remove usuários, senhas, estufas, cultivos ou auditorias.

-- A recuperação de senha usava links enviados por e-mail e não será mais utilizada.
DROP TABLE IF EXISTS recuperacoes_senha;

-- A confirmação de e-mail foi removida do cadastro e do login.
ALTER TABLE usuarios
  DROP COLUMN email_verificado_em,
  DROP COLUMN token_verificacao_hash,
  DROP COLUMN token_verificacao_expira_em;

-- Remove somente contadores antigos de operações de e-mail que deixaram de existir.
-- Os registros de auditoria são preservados para manter o histórico de segurança.
DELETE FROM tentativas_seguranca
WHERE acao IN (
  'mfa_envio',
  'mfa_codigo',
  'recuperacao_ip',
  'recuperacao_email',
  'reenviar_email',
  'reenviar_email_ip'
);
