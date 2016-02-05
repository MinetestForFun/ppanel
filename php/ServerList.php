<?php namespace MFF\ppanel;
require_once dirname(__FILE__) . '/Server.php';

class ServerList implements \IteratorAggregate {
  public $servers = array();

  public function getIterator() {
    return new \ArrayIterator($this->servers);
  }

  public function add($srv) {
    $this->servers[] = $srv;
  }

  public function getByShortName($sn) {
    $sn = strtolower($sn);
    foreach ($this->servers as $srv) {
      if (strtolower($srv->shortname) == $sn) {
        return $srv;
      }
    }
    trigger_error('Server short-named "' . $sn . '" not found', E_USER_WARNING);
    return null;
  }

  public function getById($id) {
    foreach ($this->servers as $srv) {
      if ($srv->id == $id) {
        return $srv;
      }
    }
    trigger_error('Server id ' . $id . ' not found', E_USER_WARNING);
    return null;
  }
}

?>