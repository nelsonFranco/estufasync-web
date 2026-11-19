# Endpoints

| Endpoint | Método | Função |
|---|---|---|
| `index.php` | GET | Login |
| `login.php` | POST | Autenticação |
| `cadastro.php` | GET | Formulário de cadastro |
| `cadastrar.php` | POST | Criar usuário |
| `logout.php` | POST | Encerrar sessão |
| `conexaoThingSpeak.php` | GET | Última leitura |
| `historicoThingSpeak.php` | GET | Histórico |
| `paginas/boasvindas.php` | GET | Dashboard |
| `paginas/estufa.php` | GET/POST | Configuração da estufa |
| `paginas/cultivo.php` | GET/POST | Cultivo |
| `paginas/perfil.php` | GET/POST | Perfil do usuário |

Endpoints POST exigem token CSRF.