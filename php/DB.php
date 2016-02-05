<?php namespace MFF\ppanel;
require_once dirname(__FILE__) . '/GameAccountDbEntry.php';
require_once dirname(__FILE__) . '/PanelAccountDbEntry.php';

class DB extends \PDO {
  private $gameAccTable = 'ppanel_gameacc';
  private $panelAccTable = 'ppanel_panelacc';

  public function __construct() {
    // TODO: don't go up, use a global PPanel root path instance
    $dbPath = dirname(__FILE__) . '/../db/accounts.sqlite3';
    $dbTemplatePath = $dbPath . '_template';
    if (!file_exists($dbPath) && file_exists($dbTemplatePath)) {
      copy($dbTemplatePath, $dbPath);
      chmod($dbPath, 0600);
    }
    parent::__construct('sqlite:' . $dbPath);
  }
  
  public function __destruct() {
    //parent::__destruct();
  }

  private function getRowById($table, $id) {
    $st = parent::prepare("SELECT * FROM $table WHERE id = :id");
    $st->execute(array('id' => $id));
    $rows = $st->fetchAll();
    if (count($rows) > 1) {
      throw new Exception("There's more than 1 row with id #$id in $table");
    }
    if (count($rows) == 0) {
      return null;
    }
    return $rows[0];
  }

  public function getGameAccountById($id) {
    $acc = $this->getRowById($this->gameAccTable, $id);
    if ($acc === null) {
      return null;
    }
    return new GameAccountDbEntry($acc['id'], $acc['nick'], $acc['srvId'], $acc['ppaId']);
  }

  public function getGameAccountByNickAndServ($nick, $srvId) {
    $st = parent::prepare("SELECT * FROM $this->gameAccTable WHERE nick = :nick AND srvId = :srvId");
    $st->execute(array('nick' => $nick, 'srvId' => $srvId));
    $rows = $st->fetchAll();
    if (count($rows) > 1) {
      throw new Exception("There's more than 1 row with nick $nick + srv $srvId in $this->gameAccTable");
    }
    if (count($rows) == 0) {
      return null;
    }
    $acc = $rows[0];
    return new GameAccountDbEntry($acc['id'], $acc['nick'], $acc['srvId'], $acc['ppaId']);
  }

  public function getGameAccountsForPanelAccId($ppaId) {
    $st = parent::prepare("SELECT * FROM $this->gameAccTable WHERE ppaId = :ppaId");
    $st->execute(array('ppaId' => $ppaId));
    $rows = $st->fetchAll();
    $gaccs = array();
    foreach ($rows as $acc) {
      $gaccs[] = new GameAccountDbEntry($acc['id'], $acc['nick'], $acc['srvId'], $acc['ppaId']);
    }
    return $gaccs;
  }

  public function newGameAccount($nick, $srvId, $ppaId) {
    $st = parent::prepare("INSERT INTO $this->gameAccTable (nick, srvId, ppaId) VALUES (:nick, :srvId, :ppaId)");
    $st->execute(array('nick' => $nick, 'srvId' => $srvId, 'ppaId' => $ppaId));
    return new GameAccountDbEntry(parent::lastInsertId(), $nick, $srvId, $ppaId);
  }

  public function setGameAccountPanelId($gacc, $ppaId) {
    $st = parent::prepare("UPDATE $this->gameAccTable SET ppaId = :ppaId WHERE id = :id");
    $st->execute(array('id' => $gacc->id, 'ppaId' => $ppaId));
  }

 
  public function panelAccountExistsForRandId($randId) {
    $st = parent::prepare("SELECT id FROM $this->panelAccTable WHERE randId = :randId");
    $st->execute(array('randId' => $randId));
    $rows = $st->fetchAll();
    if (count($rows) >= 1) {
      return true;
    }
    return false;
  }

  public function getPanelAccountById($id) {
    $pacc = $this->getRowById($this->panelAccTable, $id);
    if ($pacc === null) {
      return null;
    }
    return new PanelAccountDbEntry($pacc['id'], $pacc['randId'], $pacc['mergedWith'], $pacc['created'], $pacc['lastLogin']);
  }

  /// @returns PanelAccount that is bound to given game account
  /// @returns null if no linked panel account could be found
  public function getPanelAccountByGameAccount($gacc) {
    return $this->getPanelAccountById($gacc->ppaId);
  }

  public function newPanelAccount() {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charsLen = strlen($chars);
    $randId = '';
    for ($i = 0; $i < 10; $i++) {
        $randId .= $chars[rand(0, $charsLen - 1)];
    }
    $st = parent::prepare("INSERT INTO $this->panelAccTable (randId, created, lastLogin) VALUES (:randId, :ll, :ll)");
    $time = gettimeofday()['sec'];
    $st->execute(array('randId' => $randId, 'll' => $time));
    return new PanelAccountDbEntry(parent::lastInsertId(), $randId, '', $time, $time);
  }

  public function updatePanelAccountAccessTime($pacc) {
    $st = parent::prepare("UPDATE $this->panelAccTable SET lastLogin = :ll WHERE id = :id");
    return $st->execute(array('ll' => gettimeofday()['sec'], 'id' => $pacc->id));
  }

  public function addPanelAccountMergedWith($pacc, $mw) {
    $st = parent::prepare("UPDATE $this->panelAccTable SET mergedWith = mergedWith || :mw || ' ' WHERE id = :id");
    return $st->execute(array('mw' => trim($mw), 'id' => $pacc->id));
  }

  public function deletePanelAccount($pacc) {
    $st = parent::prepare("DELETE FROM $this->panelAccTable WHERE id = :id");
    $st->execute(array('id' => $pacc->id));
  }
}

?>