<?php
require_once __DIR__ . '/../seguranca.php';

// Sem login, a pessoa não pode acessar esta página.
exigir_login();
