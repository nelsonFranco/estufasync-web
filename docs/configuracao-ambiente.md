## Configuração do ambiente

Execute os comandos dentro da pasta:

```text
C:\Users\SecGen\Desktop\estufasync-web\web
```

### Gerar o segredo da aplicação

Execute:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Copie o resultado para o arquivo `config.php`:

```php
'app_secret' => 'RESULTADO_GERADO'
```

> Nunca publique o valor real de `app_secret`.

### Gerar o hash do código de convite

Escolha um código privado e execute:

```bash
php -r "echo hash('sha256', 'ESTUFA-2026-CONVITE'), PHP_EOL;"
```

Copie o hash gerado para:

```php
'codigo_convite_hash' => 'HASH_GERADO'
```

O código original será utilizado no cadastro dos usuários, mas somente o hash deve ser armazenado no `config.php`.

### Gerar hash de senha

Para gerar uma senha protegida:

```bash
php -r "echo password_hash('Troque_Esta_Senha_123!', PASSWORD_DEFAULT), PHP_EOL;"
```

> Nunca armazene senhas em texto puro. Não use SHA-256 para senhas.

## Configuração do banco de dados

### Criar o banco pelo MySQL

```bash
mysql -u root -p -e "CREATE DATABASE estufa_inteligente CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Importar a estrutura atual

```bash
cmd /c "mysql -u root -p estufa_inteligente < banco\estrutura-atual-2026-11.sql"
```

### Importar pelo phpMyAdmin

1. Crie o banco `estufa_inteligente`.
2. Acesse a aba **Importar**.
3. Selecione o arquivo:

   ```text
   banco/estrutura-atual-2026-11.sql
   ```

4. Clique em **Importar**.
5. Confirme se as tabelas foram criadas corretamente.

## Migrações

Para um banco novo, importe somente:

```text
banco/estrutura-atual-2026-11.sql
```

Para um banco antigo, faça backup e execute:

```bash
cmd /c "mysql -u root -p estufa_inteligente < banco\migracoes\migracao-seguranca-2026-11.sql"
```

Se o banco antigo possuía confirmação por e-mail, execute também:

```bash
cmd /c "mysql -u root -p estufa_inteligente < banco\migracoes\migracao-remover-smtp-2026-11.sql"
```

> Faça backup antes de qualquer migração. Não execute a mesma migração duas vezes.