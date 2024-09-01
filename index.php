<?
define('__WEB_DIR__', dirname(__FILE__));
require('./skripty/init.php');
// presmerovani URL dle udaju z DB
Presmerovani::presmerujURL();

$stranka = new Stranka();
$stranka->zpracujParam($_SERVER['REQUEST_URI']);
$stranka->nactiStranku();

Db::close();
?>
