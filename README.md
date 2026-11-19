<div align="center">

# 🌿 EstufaSync

### Monitoramento inteligente para estufas, em um só painel.

Aplicação web acadêmica para acompanhar dados ambientais, cultivos, alertas e componentes de uma estufa conectada ao ThingSpeak.

`PHP 8.1+` &nbsp; `MySQL / MariaDB` &nbsp; `ThingSpeak` &nbsp; `PWA`

</div>

---

> [!WARNING]
> **Projeto acadêmico.** O EstufaSync foi criado para fins educacionais, demonstração e avaliação. Não deve ser usado como única fonte para decisões agrícolas, operacionais ou de segurança críticas.

<div align="center">

<!-- Substitua esta imagem pela captura principal: docs/assets/dashboard.png -->
<img src="https://placehold.co/1200x620/0f2f24/e7f5ee?text=Adicione+uma+captura+do+painel+aqui" alt="Espaço reservado para captura do painel do EstufaSync" width="900">

</div>

## ✨ Visão geral

O EstufaSync centraliza o acompanhamento de uma estufa conectada: mostra leituras de sensores, histórico em gráficos, limites de alerta, informações de cultivo, estado dos componentes e relatórios ambientais.

| O que o sistema acompanha | Recursos de apoio |
| --- | --- |
| Temperatura, umidade, pressão, radiação, vento e sinal Wi-Fi | Alertas, relatórios CSV, perfil e suporte |
| Cultivo, diário operacional e componentes | Sessões, controle de acesso, CSRF e auditoria |

## 🎬 Demonstração

<div align="center">

<!-- Cole aqui o link do vídeo. Exemplo:
[![Assistir à demonstração](https://img.youtube.com/vi/SEU_VIDEO_ID/maxresdefault.jpg)](https://www.youtube.com/watch?v=SEU_VIDEO_ID) -->

[▶️ **Adicionar vídeo de demonstração**](#)

</div>

## 🖼️ Galeria do projeto

Substitua os cartões pelas capturas reais. Recomendação: guarde os arquivos em `docs/assets/`, por exemplo, `dashboard.png`, `cultivo.png` e `alertas.png`.

<div align="center">

| Painel inicial | Cultivo e diário |
| --- | --- |
| <img src="https://placehold.co/600x360/163c2d/e7f5ee?text=Print+do+painel" alt="Espaço para print do painel" width="420"> | <img src="https://placehold.co/600x360/163c2d/e7f5ee?text=Print+do+cultivo" alt="Espaço para print do cultivo" width="420"> |
| Alertas e componentes | Relatórios ambientais |
| <img src="https://placehold.co/600x360/163c2d/e7f5ee?text=Print+dos+alertas" alt="Espaço para print dos alertas" width="420"> | <img src="https://placehold.co/600x360/163c2d/e7f5ee?text=Print+dos+relatorios" alt="Espaço para print dos relatórios" width="420"> |

</div>

## 🚀 Funcionalidades

- Autenticação, cadastro por código de convite e logout;
- Acesso compartilhado de vários usuários a uma mesma estufa (`1:N`);
- Painel com leituras ambientais e gráficos do ThingSpeak;
- Configuração da estufa, limites e alertas;
- Gestão de cultivo, ciclo e diário operacional;
- Acompanhamento de componentes e conectividade;
- Relatórios com médias, mínimos, máximos, disponibilidade, CSV e impressão;
- Perfil, suporte e recursos PWA (`manifest.json`, service worker e página offline).

## 🧰 Tecnologias

| Camada | Tecnologias |
| --- | --- |
| Interface | HTML5, CSS3 e JavaScript |
| Aplicação | PHP 8.1+ |
| Dados | MySQL ou MariaDB via PDO |
| Sensores | ThingSpeak |
| Hospedagem prevista | InfinityFree |
| Versionamento | Git e GitHub |

## ⚡ Começo rápido

### 1. Prepare o banco

Crie um banco MySQL — por exemplo, `estufa_inteligente` — e importe:

```text
banco/estrutura-atual-2026-11.sql
```

As migrações em `banco/migracoes/` servem apenas para bancos que já utilizavam uma estrutura anterior. Faça backup e não execute a mesma migração duas vezes.

### 2. Configure o ambiente

Copie `config.example.php` para `config.php` e preencha os dados locais:

```php
<?php
return [
    'db_host' => 'localhost',
    'db_name' => 'estufa_inteligente',
    'db_user' => 'SEU_USUARIO',
    'db_password' => 'SUA_SENHA',
    'thingspeak_canal' => 'SEU_CANAL',
    'thingspeak_read_key' => 'SUA_READ_API_KEY',
    'app_url' => 'http://localhost:8000',
    'app_secret' => 'SEGREDO_ALEATORIO',
    'estufa_id' => 1,
    'codigo_convite_hash' => 'HASH_SHA256_DO_CONVITE'
];
```

Gere os valores sensíveis:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
php -r "echo hash('sha256', 'SEU-CODIGO-DE-CONVITE'), PHP_EOL;"
```

> [!CAUTION]
> `config.php` contém credenciais e chaves privadas. Não o envie ao GitHub, não o exponha em capturas de tela e não o publique no diretório público.

### 3. Inicie localmente

Na pasta `web`, execute:

```bash
php -S localhost:8000
```

Abra [http://localhost:8000](http://localhost:8000). Em desenvolvimento local, mantenha `http://localhost:8000`; o redirecionamento HTTPS é destinado ao ambiente publicado.

## 📡 Mapeamento dos sensores

| Campo ThingSpeak | Leitura | Unidade |
| --- | --- | --- |
| Field 1 | Temperatura | °C |
| Field 2 | Umidade | % |
| Field 3 | Pressão | hPa |
| Field 4 | Radiação UVA/B | mV |
| Field 5 | Velocidade do vento | m/s |
| Field 8 | Sinal Wi-Fi | dBm |

A chave de leitura é consultada pelo backend e deve permanecer no PHP — nunca em JavaScript, HTML público ou no repositório.

## 🛡️ Segurança

- Consultas parametrizadas com PDO;
- Senhas protegidas com `password_hash` e `password_verify`;
- Sessões com expiração e regeneração de identificador;
- Proteção CSRF em operações de alteração;
- Cabeçalhos de segurança, política de conteúdo e limitação de tentativas;
- Auditoria, validação de imagens e bloqueio de scripts em uploads;
- Código de convite salvo como hash SHA-256.

Leia também [SECURITY.md](SECURITY.md) e [docs/seguranca.md](docs/seguranca.md).

## 🗂️ Estrutura do projeto

```text
web/
├── banco/                 # estrutura e migrações do banco
├── docs/                  # documentação e imagens do README
├── img/                   # imagens da aplicação
├── paginas/               # telas autenticadas
├── script/                # scripts JavaScript
├── style/                 # estilos da aplicação
├── config.example.php     # modelo de configuração
├── config.php             # configuração local/produção (não versionar)
├── conexao.php            # conexão com banco
├── conexaoThingSpeak.php  # leitura atual
├── historicoThingSpeak.php
├── index.php
├── manifest.json
├── service-worker.js
└── README.md
```

## 📚 Documentação

- [Estrutura do projeto](docs/estrutura.md)
- [Instalação local](docs/instalacao-local.md)
- [Endpoints](docs/endpoints.md)
- [Publicação no InfinityFree](docs/deploy-infinityfree.md)
- [Práticas de segurança](docs/seguranca.md)
- [Guia de contribuição](CONTRIBUTING.md)
- [Comunicação de vulnerabilidades](SECURITY.md)
- [Licença](LICENSE)

## 🤝 Contribuição

1. Crie uma branch para a alteração;
2. Não inclua segredos ou dados reais no repositório;
3. Teste localmente antes de enviar;
4. Descreva o que foi alterado e solicite revisão.

```text
feature/nome-da-funcionalidade
fix/nome-do-problema
docs/nome-da-documentacao
```

## 📌 Status

**Em desenvolvimento acadêmico.** Funcionalidades, integrações, textos e regras de segurança podem evoluir conforme os testes e os requisitos da equipe.

<div align="center">

Feito para aprender, monitorar e cultivar melhores soluções. 🌱

</div>
