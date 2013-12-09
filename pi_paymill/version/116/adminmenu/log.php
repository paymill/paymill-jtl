<?php

global $smarty, $oPlugin;

$smarty->assign("oPlugin", $oPlugin);

$smarty->assign("stepPlugin", 'Log');

if (array_key_exists('stepPlugin', $_REQUEST)) {
    $smarty->assign("stepPlugin", $_REQUEST['stepPlugin']);
}

if (array_key_exists('id', $_GET)) {
    $sql = "SELECT * FROM `xplugin_pi_paymill_log` WHERE id = '" . $_GET['id'] . "'";
    $log = $GLOBALS['DB']->executeQuery($sql, true);
    $smarty->assign('debug', $log->debug);
    $template = $oPlugin->cAdminmenuPfad . "template/log_detail.tpl";
    $queryData = $_GET;
    unset($queryData['id']);
} else {
    $recordLimit = 10;
    $page = $_GET['seite'];
    if (!isset($_GET['seite'])) {
        $page = 1;
    }


    $start = $page * $recordLimit - $recordLimit;

    $sql = "SELECT * FROM `xplugin_pi_paymill_log` LIMIT $start, $recordLimit";

    if (isset($_POST['reset_filter'])) {
        unset($_SESSION['connected']);
        unset($_SESSION['search_key']);
    }

    if (isset($_POST['submit']) || isset($_SESSION['search_key'])) {
        if (!isset($_SESSION['search_key'])) {
            $_SESSION['search_key'] = true;
        }

        isset($_POST['submit']) ? $searchKey = $_POST['search_key'] : $searchKey = $_SESSION['search_key'];
        if (array_key_exists('connected', $_POST) || array_key_exists('connected', $_SESSION)) {
            $_SESSION['connected'] = true;
            $sql = "SELECT identifier FROM `xplugin_pi_paymill_log` WHERE debug like '%" . $searchKey . "%' LIMIT $start, $recordLimit";
            $identifier = $GLOBALS['DB']->executeQuery($sql, true);
            $sql = "SELECT * FROM `xplugin_pi_paymill_log` WHERE identifier = '" . $identifier->identifier . "' LIMIT $start, $recordLimit";
        } else {
            $sql = "SELECT * FROM `xplugin_pi_paymill_log` WHERE debug like '%" . $searchKey . "%' LIMIT $start, $recordLimit";
        }
    }

    $data = $GLOBALS['DB']->executeQuery($sql, 2);
    $recordCount = count($GLOBALS['DB']->executeQuery("SELECT * FROM `xplugin_pi_paymill_log`", 2));

    $pageCount = $recordCount / $recordLimit;

    $smarty->assign("pageCount", $pageCount);
    $smarty->assign("page", $page);
    $smarty->assign("data", $data);

    $template = $oPlugin->cAdminmenuPfad . "template/log.tpl";
    
    $queryData = $_GET;
}

$smarty->assign('pageUrl', gibShopURL() . '/admin/plugin.php');

print($smarty->fetch($template));
?>