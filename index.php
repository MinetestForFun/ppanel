<?php namespace MFF\ppanel;
require_once 'init.php';

require_once 'php/AccountUtil.php';

if (AccountUtil::isConnected($db, $sess)) {
  // Already connected, nothing to do here
  header('Location: panel.php');
  exit();
}

require_once 'php/BotHandler.php';
require_once 'php/HTTemplate.php';

$logonFailUsr = false;
$logonFailPwd = false;
$nick = null;

// If we have form data, fetch and check the nick, password and server
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nick = $_POST['nick'];
  $nickOk = !empty($nick) && strlen($nick) >= 1 && strlen($nick) <= 64;
  $passwd = $_POST['passwd'];
  $passwdOk = !empty($passwd) && strlen($passwd) >= 1 && strlen($passwd) <= 128;
  $servParm = $_POST['serv'];
  $servOk = !empty($servParm) && strlen($servParm) === 2;
  if ($servOk) {
    $serv = $servers->getByShortName($servParm);
    $servOk = ($serv !== null);
    if ($servOk && $nickOk && $passwdOk) {
      $auth = $serv->getAuthEntry($nick);
      if ($auth) {
        $valid = $auth->checkAuth($nick, $passwd);
        if ($valid) {
          // If the supplied login info is correct, find or create the game account in the DB and link
          // it to a panel account if necessary, and connect to it.
          AccountUtil::connect($db, $serv, $nick, $sess);
          header('Location: panel.php');
          exit();
        }
        $logonFailPwd = true;
      } else {
        $logonFailUsr = true;
      }
    }
  } else {
    // If the server param isn't good, either the client's browser
    // fucked up, either we face a bot. Bail out, since we have nothing
    // more productive to do anyway.
    BotHandler::fuckoff();
  }
}

HTTemplate::putDocHeader($itzr->f('mff.ppanel.titleFmt', $itzr->t('mff.ppanel.logon.pageTitle')));

?>
<div class="logon bar">
<?php HTTemplate::putLogotype($itzr->t('mff.ppanel.logon.title')); ?>
<form method="POST">
<div class="servers">
<?php
$first = true;
foreach ($servers as $srv) {
  echo '<input type="radio" name="serv" id="serv' . $srv->shortname . '" value="' . $srv->shortname . '"' . ($first ? " checked" : "") . '><label for="serv' . $srv->shortname . '" style="background-color: ' . $srv->color . '"><span>' . $srv->shortname . '</span><div>' . $itzr->t($srv->itzrName) .  '</div></label>';
  $first = false;
}
?>
</div>
<div class="forms">
<input name="nick" placeholder="<?php echo $itzr->t('mff.ppanel.logon.nickPlaceholder'); ?>" <?php echo $logonFailUsr ? 'class="wrong"': ''; echo $nick ? (' value="'.$nick.'"') : '' ?>>
<input type="password" name="passwd" placeholder="<?php echo $itzr->t('mff.ppanel.logon.passwdPlaceholder'); ?>" <?php echo $logonFailPwd ? 'class="wrong"': ''; ?>>
</div><input type="submit" value="➤">
</form>
</div>
<?php
HTTemplate::putDocFooter();
?>