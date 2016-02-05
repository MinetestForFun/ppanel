<?php namespace MFF\ppanel;

class PanelAccountDbEntry {
  public $id, $randId, $mergedWith, $created, $lastLogin;

  public function __construct($id, $randId, $mergedWith, $created, $lastLogin) {
    $this->id = $id;
    $this->randId = $randId;
    $this->mergedWith = $mergedWith;
    $this->created = $created;
    $this->lastLogin = $lastLogin;
  }
}

?>