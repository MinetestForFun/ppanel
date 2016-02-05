<?php namespace MFF\ppanel;
require_once dirname(__FILE__) . '/OutputHandler.php';

class BotHandler {
  private function __construct() {
  }

  ///
  /// @brief Handle bots and weird cases
  /// Does so by sending a non standard HTTP code "471 Fuck Off"
  /// @warning Exits the PHP script!
  ///
  public static function fuckoff($desc = null) {
    OutputHandler::clear();
    header_remove();
    header("HTTP/1.1 471 Fuck Off");
    echo 'Bad input';
    if ($desc !== null)
      echo ': '.$desc;
    echo '. If you did things correctly but still see this, please use the "Support" section on the site and describe the exact steps you followed and what device/browser/extensions you use.';
    exit(); // Bail out
  }
}

?>