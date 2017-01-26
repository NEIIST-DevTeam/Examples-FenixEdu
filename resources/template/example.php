<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/../webscripts/Controller.php");
$controller = new Controller("/resources/template/template.htm");
$controller->setTitle("Teste");
echo($controller->getPageTop());
?>
<div class="panel-body text-center">
  <h1><span class="glyphicon glyphicon-cog"></span>&nbsp;Site em constru&ccedil;&atilde;o&nbsp;<span class="glyphicon glyphicon-wrench"></span></h1>
  <samp class="code">while(!site.isReady()) { visit(<a href="https://www.facebook.com/NEIIST/">neiist.getFacebookPage()</a>); }</samp>
</div>
<?php
echo($controller->getPageBottom());
?>
