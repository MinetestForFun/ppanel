<?php namespace MFF\ppanel\pages;
require_once dirname(__FILE__) . '/../php/Page.php';
require_once dirname(__FILE__) . '/../php/PageRegistry.php';

use MFF\ppanel\Instances as I;

class Overview extends \MFF\ppanel\Page {
  public function __construct() {
  }

  public function getCodename() {
    return 'overview';
  }

  public function getLocalizedName() {
    return I::$itzr->t('mff.ppanel.pages.overview.pageTitle');
  }

  private static function formatTimeOnline($s) {
    return sprintf('%d:%02d:%02d:%02d', floor($s/86400), floor($s/3600)%24, floor($s/60)%60, $s%60);
  }

  public function getHTMLOutput() {
    $accs = I::$db->getGameAccountsForPanelAccId(I::$sess->getAccountId());
    $out = '<h1>' . I::$itzr->t('mff.ppanel.pages.overview.title') . '</h1>
<p>' . I::$itzr->f('mff.ppanel.pages.overview.nGameAccounts', count($accs)) . '</p>
<table class="accoverview"><tr>' . 
    '<th>' . I::$itzr->t('mff.ppanel.pages.overview.acctable.server') . '</th>' .
    '<th>' . I::$itzr->t('mff.ppanel.pages.overview.acctable.nick') . '</th>' .
    '<th>' . I::$itzr->t('mff.ppanel.pages.overview.acctable.lastSeen') . '</th>' .
    '<th>' . I::$itzr->t('mff.ppanel.pages.overview.acctable.timeOnline') .
      ' (' . I::$itzr->t('mff.ppanel.pages.overview.acctable.dhms') . ')</th>' .
    '</tr>';
    foreach ($accs as $acc) {
      $srv = I::$servers->getById($acc->srvId);
      $ls = $srv->getLastSeenData($acc->nick);
      $out .= '<tr>' .
      '<td class="pixframe" style="background:' . $srv->color . ' ">' . $srv->name . '</td>' .
      '<td>' . $acc->nick . '</td>' .
      '<td class="text-right">' . I::$itzr->strftime(I::$itzr->t('mff.ppanel.pages.overview.acctable.lastSeenFormat'), $ls['lastonline']) . '</td>' .
      '<td class="text-right">' . self::formatTimeOnline($ls['timeonline']) . '</td>' .
      '</tr>';
    }
    $out .= '</table>';
    return $out;
  }
}

return new Overview();

?>