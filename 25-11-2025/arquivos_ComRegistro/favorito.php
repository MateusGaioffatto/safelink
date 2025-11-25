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
        <title>Meus Produtos Favoritos - SafeLinks</title>
        <link rel="stylesheet" href="favoritosStyle.css">
        <link rel="stylesheet" href="../navBarStyle.css">
        <link rel="stylesheet" href="index_php.css">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="../SafeLinks_Favicon_Logo.png" type="image/png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
    <!-- NAVBAR -->
    <nav class="navBarElemento" id="navBarElementoId">
        <div class="navBarContainer">
        <div class="navBarLogo" id="navBarLogoId"><a href="index.php"> SafeLinks </a></div>
        <ul class="navBarLinks" id="navBarLinksId">
            <li class="user-menu">
                <button class="user-menu-button" >
                    <a href="perfil.php" ><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?> </a>
                </button>
            </li>
            <li><a href="historicos.php"><i class="fas fa-history"></i> Historico </a></li>
            <li><a href="index.php"><i class="fas fa-home"></i> Início </a></li>
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

    <div class="favoritos-container">
        <div class="favoritos-header">
            <h1><i class="fas fa-heart" style="color: red;"></i> Meus Produtos Favoritos</h1>
            <p>Gerencie todos os produtos que você salvou para consultas futuras</p>
        </div>
        
        <div class="favoritos-grid" id="favoritosGrid">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Carregando seus produtos favoritos...</p>
            </div>
        </div>
    </div>


    <script src="../script.js"></script>
    <script src="favoritosScript.js"></script>
    <script src="../theme.js"></script>
</body>
</html>