<?php
    session_start();
    require_once 'model/funktionen.inc.php';
    

    $aktion = isset($_REQUEST['aktion'])?$_REQUEST['aktion']:'home';
    
    // LOGIK
    switch($aktion) {
        case 'home':
            break;
        case 'login':
            loginResult();
            break;
        case 'dashboard':
            break;
        case 'logout':
            logout();
            $aktion = 'home';
            break;
        case 'settings':
            break;
    }
    
    // SICHT
    require_once 'view/html_template/html_header.html';
    require_once 'view/' . $aktion . '.tpl.html';
    require_once 'view/html_template/html_footer.html';
?>