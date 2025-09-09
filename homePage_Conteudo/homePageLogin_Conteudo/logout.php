
<?php
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header('Location: login.html?message=' . urlencode('Você saiu da sua conta.') . '&type=success');
exit();
?>
