<?php namespace MFF\ppanel;

class Session {
  public function __construct() {
    session_start();
  }

  public function destroy() {
    session_destroy();
  }

  public function clear() {
    session_unset();
  }

  public function clearLogon() {
    unset($_SESSION['ppaId']);
    unset($_SESSION['ppaRandId']);
  }

  public function setLanguage($lang) {
    $_SESSION['lang'] = $lang;
  }

  public function getLanguage() {
    if (empty($_SESSION['lang'])) {
      return 'en';
    }
    return $_SESSION['lang'];
  }

  public function getAccountId() {
    if (!isset($_SESSION['ppaId']))
      return null;
    return $_SESSION['ppaId'];
  }

  public function setAccountId($id) {
    $_SESSION['ppaId'] = $id;
  }

  public function getAccountRandId() {
    if (!isset($_SESSION['ppaRandId']))
      return null;
    return $_SESSION['ppaRandId'];
  }

  public function setAccountRandId($id) {
    $_SESSION['ppaRandId'] = $id;
  }

  public function setAccount($acc) {
    $this->setAccountId($acc->id);
    $this->setAccountRandId($acc->randId);
  }
}

?>