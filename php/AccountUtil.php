<?php namespace MFF\ppanel;
require_once dirname(__FILE__) . '/OutputHandler.php';

class AccountUtil {
  private function __construct() {
  }

  public static function connect($db, $serv, $nick, $sess) {
    $sess->clear();
    $gacc = $db->getGameAccountByNickAndServ($nick, $serv->id);
    $pacc = null;
    if ($gacc === null) {
      // No game account in the DB, create it as well as the panel account that goes with it
      $pacc = $db->newPanelAccount();
      $gacc = $db->newGameAccount($nick, $serv->id, $pacc->id);
    } else {
      // Game account is in the DB. Find the linked PanelAccount.
      $pacc = $db->getPanelAccountByGameAccount($gacc);
    }
    $db->updatePanelAccountAccessTime($pacc);
    $sess->setAccount($pacc);
  }

  const NO_SESSION_ACCOUNT = false;
  const ACCOUNT_ALREADY_ADDED = 0;
  const ACCOUNT_ADDED = 1;
  const ACCOUNT_ADDED_MERGE = 2;

  public static function addMergeAccount($db, $sess, $serv, $nick) {
    if ($sess->getAccountId() == null) {
      return array('status' => self::NO_SESSION_ACCOUNT);
    }
    $gacc = $db->getGameAccountByNickAndServ($nick, $serv->id);
    if ($gacc === null) {
      // No game account in the DB, create it and link it to the current panel account
      $gacc = $db->newGameAccount($nick, $serv->id, $sess->getAccountId());
      return array('status' => self::ACCOUNT_ADDED);
    } else {
      // Game account is bound to a PanelAccount. Merge it.
      $pacc = $db->getPanelAccountByGameAccount($gacc);
      // Unless it's the current account.
      if ($pacc->id === $sess->getAccountId()) {
        return array('status' => self::ACCOUNT_ALREADY_ADDED);
      }
      $ogaccs = $db->getGameAccountsForPanelAccId($pacc->id);
      foreach ($ogaccs as $ogacc) {
        $db->setGameAccountPanelId($ogacc, $sess->getAccountId());
      }
      $db->addPanelAccountMergedWith($db->getPanelAccountById($sess->getAccountId()), trim($pacc->mergedWith) . ' ' . $pacc->id);
      $db->deletePanelAccount($pacc);
      return array('status' => self::ACCOUNT_ADDED_MERGE, 'mergedWithRandId' => $pacc->randId);
    }
    return false;
  }

  public static function isConnected($db, $sess) {
    /* Security: user who has an invalid randId (e.g. live DB changes) is NOT connected */
    return $sess->getAccountId() !== null && $sess->getAccountRandId() !== null && $db->panelAccountExistsForRandId($sess->getAccountRandId()) === true;
  }

  public static function disconnect($sess) {
    $sess->clearLogon();
  }
}

?>