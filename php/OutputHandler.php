<?php namespace MFF\ppanel;

class OutputHandler {
  private function __construct() {
  }

  public static function start() {
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
    ob_start('mb_output_handler');
  }

  public static function clear() {
    ob_clean();
  }
}

?>