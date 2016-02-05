<?php namespace MFF\ppanel;

class PageRegistry {
  public static $pages = array();

  private function __construct() {
  }

  public static function registerAllPages() {
    $dirpath = dirname(__FILE__) . '/../pages';
    $dir = dir($dirpath);
    while (($entry = $dir->read()) !== false) {
      if (strlen($entry) >= 4 /* .php */ && $entry[0] != '.' && substr($entry, -4) == '.php') {
        self::$pages[] = (include ($dirpath . '/' . $entry));
      }
    }
    $dir->close();
  }

  /// @returns Page instance bound to codename $cn, null if none found.
  public static function getPageByCodename($cn) {
    foreach (self::$pages as $page) {
      if ($page->getCodename() === $cn) {
        return $page;
      }
    }
    return null;
  }
}

?>