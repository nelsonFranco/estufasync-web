# Instalação local

## Requisitos

- PHP 8.1 ou superior;
- MySQL ou MariaDB;
- PDO MySQL;
- OpenSSL.

## Instalação

1. Copie `config.example.php` para `config.php`.
2. Preencha os dados do banco.
3. Gere um segredo para `app_secret`.
4. Gere o hash do código de convite.
5. Importe `banco/estrutura.sql`.
6. Execute:

```bash
php -S localhost:8000

