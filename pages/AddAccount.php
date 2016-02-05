<?php namespace MFF\ppanel\pages;
require_once dirname(__FILE__) . '/../php/AccountUtil.php';
require_once dirname(__FILE__) . '/../php/BotHandler.php';
require_once dirname(__FILE__) . '/../php/Page.php';
require_once dirname(__FILE__) . '/../php/PageRegistry.php';

use MFF\ppanel\Instances as I;
use MFF\ppanel\AccountUtil as AccountUtil;

class AddAccount extends \MFF\ppanel\Page {
  private $addStatus = null;

  public function __construct() {
  }

  public function getCodename() {
    return 'addacc';
  }

  public function getLocalizedName() {
    return I::$itzr->t('mff.ppanel.pages.addacc.pageTitle');
  }

  public function init() {
    // If we have form data, fetch and check the nick, password and server
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $nick = $_POST['nick'];
      $nickOk = !empty($nick) && strlen($nick) >= 1 && strlen($nick) <= 64;
      $passwd = $_POST['passwd'];
      $passwdOk = !empty($passwd) && strlen($passwd) >= 1 && strlen($passwd) <= 128;
      $servParm = $_POST['serv'];
      $servOk = !empty($servParm) && strlen($servParm) === 2;
      if ($servOk) {
        $serv = I::$servers->getByShortName($servParm);
        $servOk = ($serv !== null);
        if ($servOk && $nickOk && $passwdOk) {
          $auth = $serv->getAuthEntry($nick);
          if ($auth) {
            $valid = $auth->checkAuth($nick, $passwd);
            if ($valid) {
              // If the supplied login info is correct, add the game account to the
              // current panel one, merging if necessary.
              $this->addStatus = AccountUtil::addMergeAccount(I::$db, I::$sess, $serv, $nick);
            }
            // TODO: handle invalid logins
          }
        }
      } else {
        // If the server param isn't good, either the client's browser
        // fucked up, either we face a bot. Bail out, since we have nothing
        // more productive to do anyway.
        \MFF\ppanel\BotHandler::fuckoff();
      }
    }
  }

  public function getHTMLOutput() {
    $out = '<h1>' . I::$itzr->t('mff.ppanel.pages.addacc.title') . '</h1>' .
      '<p>' . I::$itzr->t('mff.ppanel.pages.addacc.explanation') . '</p>';
    if ($this->addStatus !== null) {
      $out .= '<div class="alert ';
      switch ($this->addStatus['status']) {
      case AccountUtil::ACCOUNT_ADDED:
        $out .= 'alert-success">' . I::$itzr->t('mff.ppanel.pages.addacc.added');
        break;
      case AccountUtil::ACCOUNT_ADDED_MERGE:
        $out .= 'alert-success">' . I::$itzr->f('mff.ppanel.pages.addacc.addedMerge', $this->addStatus['mergedWithRandId']);
        break;
      case AccountUtil::ACCOUNT_ALREADY_ADDED:
        $out .= 'alert-info">' . I::$itzr->t('mff.ppanel.pages.addacc.alreadyAdded');
        break;
      default:
        $out .= 'alert-error">' . I::$itzr->t('mff.ppanel.pages.addacc.error');
      }
      $out .= '</div>';
    }
    $out .= '<div class="center" style="margin: 1em 0">
<form method="POST" class="logon">
<div class="servers">';
    $first = true;
    foreach (I::$servers as $srv) {
      $out .= '<input type="radio" name="serv" id="serv' . $srv->shortname . '" value="' . $srv->shortname . '"' . ($first ? " checked" : "") . '><label for="serv' . $srv->shortname . '" style="background-color: ' . $srv->color . '"><span>' . $srv->shortname . '</span><div>' . I::$itzr->t($srv->itzrName) .  '</div></label>';
      $first = false;
    }
    $out .= '</div>
<div class="forms">
<input name="nick" placeholder="' . I::$itzr->t('mff.ppanel.logon.nickPlaceholder') . '">
<input type="password" name="passwd" placeholder="' . I::$itzr->t('mff.ppanel.logon.passwdPlaceholder') . '">
</div><input type="submit" value="➤">
</form>
</div>';
  return $out;
  }
}

return new AddAccount();

?>