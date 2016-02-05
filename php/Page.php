<?php namespace MFF\ppanel;

class Page {
  private function __construct() {
  }

  public function getCodename() {
    return '[invalid page.codename]';
  }

  public function getLocalizedName() {
    return '[invalid page.localizedname]';
  }

  public function init() {
  }

  public function isDisplayed($pacc, $srv, $gacc) {
    return false;
  }

  public function getHTMLOutput() {
    return '[invalid page.htmloutput]';
  }
}

?>