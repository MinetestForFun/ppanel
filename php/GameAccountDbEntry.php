<?php namespace MFF\ppanel;

class GameAccountDbEntry {
  public $id, $nick, $srvId, $ppaId;
  public function __construct($id, $nick, $srvId, $ppaId) {
    $this->id = $id;
    $this->nick = $nick;
    $this->srvId = $srvId;
    $this->ppaId = $ppaId;
  }
}

?>