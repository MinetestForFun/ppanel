<?php namespace MFF\ppanel;
require_once 'php/DB.php';
require_once 'php/ITZR.php';
require_once 'php/Server.php';
require_once 'php/ServerList.php';
require_once 'php/Session.php';
require_once 'php/OutputHandler.php';

class Instances {
  public static $itzr, $db, $sess, $servers;

  public static function init() {
    self::$servers = new ServerList();
    $serversJson = json_decode(file_get_contents(dirname(__FILE__) . '/config/servers.json'), true);
    foreach ($serversJson['servers'] as $srv) {
      self::$servers->add(new Server($srv['id'], $srv['name'], $srv['itzrName'], $srv['host'], $srv['port'],
        $srv['shortName'], $srv['color'], $srv['worldpath'], $srv['pageTags']));
    }

    self::$sess = new Session();

    self::$itzr = new ITZR();
    self::$itzr->loadManifest('lang/manifest.itzm');
    self::$itzr->setLang(self::$sess->getLanguage());

    self::$db = new DB();
  }
}

Instances::init();
$servers = Instances::$servers;
$itzr = Instances::$itzr;
$db = Instances::$db;
$sess = Instances::$sess;

?>