<?php namespace MFF\ppanel;
require_once dirname(__FILE__) . '/AuthEntry.php';

class Server {
  public $id;

  public $name;
  public $itzrName;
  public $shortname;
  public $color;

  public $address;
  public $port;

  public $worldpath;

  public $pageTags;

  private $lastSeenData = null;

  public function __construct($id, $name, $itzrName, $address, $port, $shortname, $color, $worldpath, $pageTags) {
    $this->id = $id;
    $this->name = $name;
    $this->itzrName = $itzrName;
    $this->address = $address;
    $this->port = $port;
    $this->shortname = $shortname;
    $this->color = $color;
    $this->worldpath = $worldpath;
    $this->pageTags = $pageTags;
  }

  public function getAuthEntry($nick) {
    $handle = fopen($this->worldpath . '/auth.txt', 'r');
    if ($handle) {
      while (($line = fgets($handle)) !== false) {
        $data = explode(':', $line);
        if ($data[0] === $nick) {
          fclose($handle);
          return new AuthEntry($this, $data[0], $data[1], $data[2], $data[3]);
        }
      }
      fclose($handle);
    } else {
      // TODO: error?
    }
    return null;
  }

  private function cacheLastSeenData() {
    if ($this->lastSeenData !== null) {
      return;
    }
    $lua = new \Lua();
    $lua->eval("function getData()\n" . file_get_contents($this->worldpath . '/last-seen') . "\nend");
    $this->lastSeenData = $lua->call('getData');
  }

  public function getLastSeenData($nick) {
    $this->cacheLastSeenData();
    return $this->lastSeenData[$nick];
  }
}

?>