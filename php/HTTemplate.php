<?php namespace MFF\ppanel;

class HTTemplate {
  private function __construct() {
  }

  public static function putDocHeader($title, $bodyClass = null) {
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body class="<?php if ($bodyClass !== null) echo $bodyClass; ?>"><?php
  }

  public static function putDocFooter() {
?></body>
</html><?php
  }

  public static function putLogotype($text) {
?><div class="logotype">
<img src="img/mff_logo_460_460.png" alt="MinetestForFun"> <?php echo $text; ?>
</div><?php
  }
}

?>