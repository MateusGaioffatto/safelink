<?php
// REDIRECIONAMENTO FORTE PARA HTTPS - COLOCAR NO TOPO ABSOLUTO
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect_url);
    exit();
}

session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Se não estiver logado, redirecionar para a página de login
    header('Location: logi.php?message=' . urlencode('Acesso restrito! Faça login.') . '&type=error');
    exit();
}


?>

<!DOCTYPE html>
<html lang="pt_br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Sobre sua Privacidade </title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="shortcut icon" href="../SafeLinks_Favicon_Logo.png">

    <!-- <link rel="stylesheet" href="perfil.css"> -->
    <link rel="stylesheet" href="sobrePrivacidade.css">
    <link rel="stylesheet" href="index_php.css">
    <link rel="stylesheet" href="../navBarStyle.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../style.css">
    <!-- Incluir estilos específicos de favoritos e histórico -->
    <!-- <link rel="stylesheet" href="favoritosStyle.css">
    <link rel="stylesheet" href="historicoStyle.css"> -->
</head>
<body style="background-repeat: repeat;">

  <!-- NAVBAR -->
    <nav class="navBarElemento" id="navBarElementoId">
        <div class="navBarContainer">
        <div class="navBarLogo" id="navBarLogoId"><a href="#"> SafeLinks </a></div>
        <ul class="navBarLinks" id="navBarLinksId">
            <li class="user-menu">
            <button class="user-menu-button" >
                <a href="perfil.php" ><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?> </a>
            </button>
            </li>
            <!-- <li><a href="#"><i class="fa fa-bell" id="notificacoesIcone"></i> Notificações </a></li> -->
            <li><a href="sobre.php"><i class="fa-solid fa-circle-info"></i> Sobre </a></li>
            <li><a href="dicas.php"><i class="fas fa-lightbulb"></i> Dicas </a></li> <!-- NOVO BOTÃO DICAS -->
            <li> 
            <a href="logout.php" class="sair-link">
                <i class="fas fa-sign-out-alt"></i> Deslogar
            </a>
            </li>
            <li class="modoEscuroClaroElemento" id="modoEscuroClaroElementoId">
            <a><i class="fas fa-moon"></i> Modo </a>
            </li>
        </ul>
        <div class="menuHamburguerElemento" id="menuHamburguerElementoId"> 
            <i class="fas fa-bars"></i> 
        </div>
        </div>
    </nav>

    <div class="sobrePrivacidadeDiv" id="sobrePrivacidadeDivId">
        <h3> Texto sobre a privacidade </h3>
    </div>
    
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- <script src="perfil.js"></script> -->
    <script src="../script.js"></script>
    <script src="../theme.js"></script>
    <!-- <script src="favoritosScript.js"></script> -->
    <!-- <script type="module" src="historicoScript.js" ></script> -->
    <!-- <script src="perfilDados.js"></script> -->
</body>
</html>