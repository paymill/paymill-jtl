<?php

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
        $identifier = xtc_db_fetch_array(xtc_db_query($sql));
        $sql = "SELECT * FROM `xplugin_pi_paymill_log` WHERE identifier = '" . $identifier['identifier'] . "' LIMIT $start, $recordLimit";
    } else {
        $sql = "SELECT * FROM `xplugin_pi_paymill_log` WHERE debug like '%" . $searchKey . "%' LIMIT $start, $recordLimit";
    }
}


$data = $GLOBALS['DB']->executeQuery($sql, 2);
$recordCount = count($data);



$pageCount = $recordCount / $recordLimit;
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <td>
            <div>
                <b>Page: </b>
                <?php for ($a = 0; $a <= $pageCount; $a++) : ?>
                    <?php $b = $a + 1; ?>
                    <?php if ($page == $b) : ?>
                        <b><?php echo $b; ?></b>
                    <?php else : ?>
                        <a href="<?php echo 'paymill_logging.php?seite=' . $b; ?>"><?php echo $b; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <form action="paymill_logging.php" method="POST" style="float: left;">
                <input value="" name="search_key"/><input type="submit" value="Search" name="submit"/>
                <input type="checkbox" name="connected" value="true">&nbsp;Connected Search
            </form>
            <form action="paymill_logging.php" method="POST" style="float: right;">
                <input type="submit" value="Reset Filter" name="reset_filter"/>
            </form>
            <table width="100%">
                <tr>
                    <th>ID</th>
                    <th>Connector ID</th>
                    <th>Message</th>
                    <th>Debug</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($data as $log): ?>
                    <tr>
                        <td><center><?php echo $log->identifier; ?></center></td>
                        <td><center><?php echo $log->id; ?></center></td>
                        <td><?php echo $log->message; ?></td>
                        <td>
                        <?php if (strlen($log->debug) < 500): ?>
                            <pre><?php echo $log->debug; ?></pre>
                        <?php else: ?>
                            <center>
                                <a href="<?php echo 'paymill_log.php?id=' . $log->id; ?>">See more</a>
                            </center>
                        <?php endif; ?>
                        </td>
                        <td>
                            <center><?php echo $log->date; ?></center>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <form action="paymill_logging.php" method="POST" style="float: left;">
                <input name="search_key"/><input type="submit" value="Search" name="submit"/>
                <input type="checkbox" name="connected" value="true">&nbsp;Connected Search
            </form>
            <form action="paymill_logging.php" method="POST" style="float: right;">
                <input type="submit" value="Reset Filter" name="reset_filter"/>
            </form>
            <div style="clear: both;">
                <b>Page: </b>
                <?php for ($a = 0; $a <= $pageCount; $a++) : ?>
                    <?php $b = $a + 1; ?>
                    <?php if ($page == $b) : ?>
                        <b><?php echo $b; ?></b>
                    <?php else : ?>
                        <a href="<?php echo 'paymill_logging.php?seite=' . $b; ?>"><?php echo $b; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <p>
            </p>
        </td>
    </tr>
</table>