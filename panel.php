<?php namespace MFF\ppanel;
require_once 'php/AccountUtil.php';
require_once 'php/PageRegistry.php';
require_once 'php/PPanel.php';
require_once 'php/HTTemplate.php';
require_once 'init.php';

if (!AccountUtil::isConnected($db, $sess)) {
  // Not connected, nothing to do here
  header('Location: index.php');
  exit();
}

if (isset($_GET['logoff'])) {
  AccountUtil::disconnect($sess);
  header('Location: index.php');
  exit();
}

$gaccs = $db->getGameAccountsForPanelAccId($sess->getAccountId());

$g_parm = null;
if (isset($_GET['g'])) {
  $g_parm = intval($_GET['g']);
  $isOwnGameAcc = false;
  foreach ($gaccs as $gacc) {
    if ($gacc->id == $g_parm) {
      $isOwnGameAcc = true;
      break;
    }
  }
  if ($g_parm < 1 || !$isOwnGameAcc) {
    $g_parm = -1;
  }
}

$page = null;
PageRegistry::registerAllPages();
if ($g_parm === null || $g_parm !== -1) {
  if (isset($_GET['p'])) {
    // "p" parameter is passed, try to find said page
    $page = PageRegistry::getPageByCodename($_GET['p']);
  } else {
    // Default case: "p" unspecified, show overview
    $page = PageRegistry::getPageByCodename('overview');
  }
}
if ($page !== null) {
  $page->init();
}

HTTemplate::putDocHeader($itzr->f('mff.ppanel.titleFmt', ($page === null) ? "[invalid page]" : $page->getLocalizedName()), 'panelPage');

?>
<nav>
<div class="bg"></div>
<div>
<div class="right text-right small-info">
PPanel v<?php echo PPanel::getVersionString();
echo '<br><em>' . $sess->getAccountRandId() . '</em><br>'; ?>
<a class="underline" href="?logoff">Logoff</a>
</div>
<div style="padding-left:1em"><?php HTTemplate::putLogotype($itzr->t('mff.ppanel.title')); ?></div>
</div>
<hr>
<a href="?"><div class="menu-elm"><div class="head"><?php echo $itzr->t('mff.ppanel.pages.overview.pageTitle'); ?></div></div></a>
<?php
// list accounts
$pacc = $db->getPanelAccountById($sess->getAccountId());
foreach ($gaccs as $gacc) {
  $srv = $servers->getById($gacc->srvId);
  echo '<div class="menu-elm" style="background:' . $srv->color . '"><div class="head">' . $gacc->nick . '<div class="right">' . $srv->shortname . '</div></div>';
  $optionsCount = 0;
  $options = '<div class="more"><ul>';
  foreach (PageRegistry::$pages as $opage) {
    if ($opage->isDisplayed($pacc, $srv, $gacc)) {
      $isActive = $g_parm === $gacc->id && $opage->getCodename() === $page->getCodename();
      $options .= '<li' . ($isActive ? ' class="active"' : '') .  '><a href="?p=' . $opage->getCodename() . '&g=' . $gacc->id . '">' . $opage->getLocalizedName() . '</a></li>';
      $optionsCount++;
    }
  }
  $options .= '</ul></div>';
  if ($optionsCount > 0) {
    echo $options;
  }
  echo '</div>';
}
?><a href="?p=addacc"><div class="menu-elm" style="width:auto;display:inline-block"><div class="head">+ <?php echo $itzr->t('mff.ppanel.pages.addacc.pageTitle'); ?></div></div></a>
</nav>
<main>
<?php
if ($page !== null) {
  echo $page->getHTMLOutput();
} else {
  echo '[invalid page]';
}
?>
</main>
<?php
HTTemplate::putDocFooter();
?>