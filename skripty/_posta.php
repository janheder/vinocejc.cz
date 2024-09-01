<?php
// balík na poštu
define('__WEB_DIR__', '..');
require('./init.php');

// uložíme ID dopravy
if($_GET['idd'])
{
$K = new Kosik('kosik','','',__TYP_CENY__,__SLEVOVA_SKUPINA__);
$K->nastavSessDoprava(intval($_GET['idd']));
}
?>

<!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5" name="viewport">
     <title></title>
<meta name="description" content="" >
<meta name="keywords" content="" lang="cs" >
<meta name="robots" content="" >
<link rel="stylesheet" type="text/css" href="/css/posta/style.css" />
        <link rel="stylesheet" type="text/css" href="/css/ui-lightness/jquery-ui-1.8.13.custom.css" />
        <script src='/js/jquery-1.5.1.min.js' type='text/javascript'></script>
        <script src='/js/jquery-ui-1.8.13.custom.min.js' type='text/javascript'></script>
        <script src='/js/anyTitle.js' type='text/javascript'></script>
        <script src='/js/script-posta.js' type='text/javascript'></script>	
</head>        
<body>
<?php

// zde bude DIV kontejner, který se zobrazi kdyz vybereme balik na postu

echo '<div id="balik_n_p" style="width: auto;  padding: 10px; padding-top: 0px; margin-top: 0px;">';

require_once '../tridy/Posta.php';
$class = new Posta();
$tags = $class->getTags();
if ($_POST['search']) {
    $data = $class->search($_POST['mesto'], $_POST['psc']);
}
if ($_POST['posta']) {
    $posta = $class->getData($_POST['posta']);
}

/* --------------------- */
isset($_POST['hodnota']) ? $hodnotaZasilky = $_POST['hodnota'] : $hodnotaZasilky = 1;

        echo '<div id="naPoste">
            <a href="http://ceskaposta.cz/" target="_blank"><img src="/img/logoPosta.png" alt="Česká pošta" class="fl" /></a>
            <a href="/"><img src="/img/balik.png" alt="Balík Na poštu" class="fr" /></a>
            <div class="cl"></div>';


           
            if (isset($posta)) {
                $prom = "<div>
                                     <p>Vaší zásilku lze vyzvednout v rámci otevírací doby pobočky, v avízu uvedeném dnu uložení však nejdříve po <b>" . $posta['VYDEJ_NP_OD'] . " hodině</b>.</p>

                                     <table>
                                        <tr><th>Pondělí</th>
                                            <td>" . $posta['pondeli_od1'] . " - " . $posta['pondeli_do1'] . "</td>
                                            <td>" . $posta['pondeli_od2'] . " - " . $posta['pondeli_do2'] . "</td>
                                            <td>" . $posta['pondeli_od3'] . " - " . $posta['pondeli_do3'] . "</td>
                                        </tr>
                                        <tr><th>Úterý</th>
                                            <td>" . $posta['utery_od1'] . " - " . $posta['utery_do1'] . "</td>
                                            <td>" . $posta['utery_od2'] . " - " . $posta['utery_do2'] . "</td>
                                            <td>" . $posta['utery_od3'] . " - " . $posta['utery_do3'] . "</td>
                                        </tr>
                                        <tr><th>Středa</th>
                                            <td>" . $posta['streda_od1'] . " - " . $posta['streda_do1'] . "</td>
                                            <td>" . $posta['streda_od2'] . " - " . $posta['streda_do2'] . "</td>
                                            <td>" . $posta['streda_od3'] . " - " . $posta['streda_do3'] . "</td>
                                        </tr>
                                        <tr><th>Čtvrtek</th>
                                            <td>" . $posta['ctvrtek_od1'] . " - " . $posta['ctvrtek_do1'] . "</td>
                                            <td>" . $posta['ctvrtek_od2'] . " - " . $posta['ctvrtek_do2'] . "</td>
                                            <td>" . $posta['ctvrtek_od3'] . " - " . $posta['ctvrtek_do3'] . "</td>
                                        </tr>
                                        <tr>
                                            <th>Pátek</th>
                                            <td>" . $posta['patek_od1'] . " - " . $posta['patek_do1'] . "</td>
                                            <td>" . $posta['patek_od2'] . " - " . $posta['patek_do2'] . "</td>
                                            <td>" . $posta['patek_od3'] . " - " . $posta['patek_do3'] . "</td>
                                        </tr>
                                        <tr>
                                            <th>Sobota</th>
                                            <td>" . $posta['sobota_od1'] . " - " . $posta['sobota_do1'] . "</td>
                                            <td>" . $posta['sobota_od2'] . " - " . $posta['sobota_do2'] . "</td>
                                            <td>" . $posta['sobota_od3'] . " - " . $posta['sobota_do3'] . "</td>
                                        </tr>
                                        <tr><th>Neděle</th>
                                            <td>" . $posta['nedele_od1'] . " - " . $posta['nedele_do1'] . "</td>
                                            <td>" . $posta['nedele_od2'] . " - " . $posta['nedele_do2'] . "</td>
                                            <td>" . $posta['nedele_od3'] . " - " . $posta['nedele_do3'] . "</td>
                                        </tr>
                                     </table>
                                  </div>";

              
                
            } 
            else 
            {
                ?>

                <p>Balík Na poštu je nová služba České pošty, která Vám přináší možnost, zvolit si pobočku, na které bude Vaše zásilka připravena k vyzvednutí již následující pracovní den po podání. O připravenosti Vaší zásilky k vyzvednutí Vás budeme informovat prostřednictvím SMS nebo e-mailu.</p>
                <hr />
                <p>Zadejte název nebo PSČ obce, ve které si přejete zásilku vyzvednout na pobočce ČP.</p>

                <form method="post" action="">
                    <div class="form">
						<input type="hidden" name="search" value="true" />
						<input type="hidden" name="hodnota" value="1" />
                        Město / obec <input type="text" id="tags" name="mesto" class="inputMesto" /> PSČ <input type="text" name="psc" class="inputPsc" /> 
						<input type="submit" value="Hledat" class="btn" />
                    </div>
                </form>

    <?php
    if (isset($data)) {
        if (count($data) == 0) {
            echo '<p class="err">Zadaným kritériím nevyhovuje žádná pošta, změňte prosím Vámi zadaná kritéria vyhledávání.</p>';
        } else if (count($data) == 1 and $data[0]['V_PROVOZU'] == 1) {
            echo '<p class="err">Omlouváme se, ale Vámi zvolená pobočka v současnosti nemůže z provozních důvodů požadovanou službu nabídnout. Prosíme, změňte vyhledávací kritéria.</p>';
        } else {
            foreach ($data as $x) {
                if ($x['V_PROVOZU'] == 1) {
                    echo '<p class="war">Některé pobočky, která vyhovují Vami zadaným kritériím momentálně službu Na poštu neposkytují, výpis vyhledávání proto nabízí pouze aktálně dostupné pobočky.</p>';
                    break;
                }
            }
            if (count($data) == 1 and $data[0]['UKL_NP_LIMIT'] == 0 and $hodnotaZasilky >= 20000) {
                echo '<p class="err">Omlouváme se, ale Vámi zvolená pobočka bohužel v současnosti není oprávněna vydávat zásilky, jejichž udaná hodnota je vyšší než 20 000 Kč. Proto jsme si dovolili Vám nabídnout alternativní řešení.</p>';
            } else {
                echo '<hr /><p>Zadaným kritériím vyhovují tyto pošty:</p>';
            }
            ?>
                <table cellpadding="0" cellspacing="0">
                            <tr>
                                <th colspan="2">Pošta</th>
                                <th>Ulice</th>
                                <th>PSČ</th>
                                <th colspan="4">Informace</th>
                                <th align="right">Zvolte poštu</th>
                            </tr>
            <?php
            foreach ($data as $x) {
                if ($x['V_PROVOZU'] == 0) {
                    if ($hodnotaZasilky > 20000 and $x['UKL_NP_LIMIT'] == 0) {
                        $x['NAZ_PROV'] = $x['NAZ_NP_NAHR'];
                        $x['ADRESA'] = "";
                        $x['PSC'] = $x['PSC_NP_NAHR'];
                    }
                    $prom = "
                                     <p>Vaši zásilku lze vyzvednout v rámci otevírací doby pobočky, v avizovaném dni uložení však nejdříve po <b>" . $x['VYDEJ_NP_OD'] . " hodině</b>.</p>

                                     <table>
                                        <tr><th>Pondělí</th>
                                            <td>" . $x['pondeli_od1'] . " - " . $x['pondeli_do1'] . "</td>
                                            <td>" . $x['pondeli_od2'] . " - " . $x['pondeli_do2'] . "</td>
                                            <td>" . $x['pondeli_od3'] . " - " . $x['pondeli_do3'] . "</td>
                                        </tr>
                                        <tr><th>Úterý</th>
                                            <td>" . $x['utery_od1'] . " - " . $x['utery_do1'] . "</td>
                                            <td>" . $x['utery_od2'] . " - " . $x['utery_do2'] . "</td>
                                            <td>" . $x['utery_od3'] . " - " . $x['utery_do3'] . "</td>
                                        </tr>
                                        <tr><th>Středa</th>
                                            <td>" . $x['streda_od1'] . " - " . $x['streda_do1'] . "</td>
                                            <td>" . $x['streda_od2'] . " - " . $x['streda_do2'] . "</td>
                                            <td>" . $x['streda_od3'] . " - " . $x['streda_do3'] . "</td>
                                        </tr>
                                        <tr><th>Čtvrtek</th>
                                            <td>" . $x['ctvrtek_od1'] . " - " . $x['ctvrtek_do1'] . "</td>
                                            <td>" . $x['ctvrtek_od2'] . " - " . $x['ctvrtek_do2'] . "</td>
                                            <td>" . $x['ctvrtek_od3'] . " - " . $x['ctvrtek_do3'] . "</td>
                                        </tr>
                                        <tr>
                                            <th>Pátek</th>
                                            <td>" . $x['patek_od1'] . " - " . $x['patek_do1'] . "</td>
                                            <td>" . $x['patek_od2'] . " - " . $x['patek_do2'] . "</td>
                                            <td>" . $x['patek_od3'] . " - " . $x['patek_do3'] . "</td>
                                        </tr>
                                        <tr>
                                            <th>Sobota</th>
                                            <td>" . $x['sobota_od1'] . " - " . $x['sobota_do1'] . "</td>
                                            <td>" . $x['sobota_od2'] . " - " . $x['sobota_do2'] . "</td>
                                            <td>" . $x['sobota_od3'] . " - " . $x['sobota_do3'] . "</td>
                                        </tr>
                                        <tr><th>Neděle</th>
                                            <td>" . $x['nedele_od1'] . " - " . $x['nedele_do1'] . "</td>
                                            <td>" . $x['nedele_od2'] . " - " . $x['nedele_do2'] . "</td>
                                            <td>" . $x['nedele_od3'] . " - " . $x['nedele_do3'] . "</td>
                                        </tr>
                                     </table>
                                  ";

                    echo '<tr>
                            <td>' . $x['NAZ_PROV'] . '</td>
                            <td><img src="/img/ico_7.png" alt=""  class="anyTitle" popis="' . $prom . '" /></td>
                            <td class="anyTitle" popis="' . $prom . '">' . $x['ADRESA'] . '</td>
                            <td>' . $x['PSC'] . '</td>';
                    if ($x['BANKOMAT'] == 1)
                        echo '<td><img src="/img/ico_1.png" alt="" class="anyTitle" popis="Bankomat na poště" /></td>'; else
                        echo '<td></td>';
                    if ($x['KOMPLET_SERVIS'] == 1)
                        echo '<td><img src="/img/ico_2.png" alt="" class="anyTitle" popis="Příjem balíkových a listovních zásilek a peněžních poukázek" /></td>'; else
                        echo '<td></td>';
                    if ($x['PARKOVISTE'] == 1)
                        echo '<td><img src="/img/ico_3.png" alt=""  class="anyTitle" popis="Parkoviště u provozovny" /></td>'; else
                        echo '<td></td>';
                    if ($x['PRODL_DOBA'] == 1)
                        echo '<td><img src="/img/ico_4.png" alt=""  class="anyTitle" popis="prodloužená otevírací doba provozovny – po 18.00" /></td>'; else
                        echo '<td></td>';
                    echo '<td valign="bottom" align="right">
					<form method="post" action="/kosik?krok=2" target="_parent">
					<input type="hidden" name="_posta" value="'.$x['ID'].'" />
					<input type="hidden" name="_posta_psc" value="'.$x['PSC'].'" />
					<input type="hidden" name="_posta_nazev" value="'.$x['NAZ_PROV'].'" />
					<input type="hidden" name="_posta_adresa" value="'.$x['ADRESA'].'" />
					<input type="submit" class="btnSmall" value="Vybrat">
					</form>
					</td>
                           </tr>';
                }
            }
            ?>
                        </table>

                        <p class="help">
                            <b>Legenda</b><br/>
                            <img src="/img/ico_7.png" alt="Otevírací doba provozovny" /> <b>Otevírací doba provozovny</b> <br />
                            <img src="/img/ico_1.png" alt="Bankomat na poště" /> Bankomat na poště <br />
                            <img src="/img/ico_2.png" alt="Příjem balíkových a listovních zásilek a peněžních poukázek" /> Příjem balíkových a listovních zásilek a peněžních poukázek<br />
                            <img src="/img/ico_3.png" alt="Parkoviště u provozovny" /> Parkoviště u provozovny <br />
                            <img src="/img/ico_4.png" alt="Prodloužená otevírací doba provozovny" /> Prodloužená otevírací doba provozovny  – po 18.00 <br />
                        </p>



                        <?php
                    }
                } //end if isset($data)
            } //end else
            ?>

        <div class="clear" style="height: 10px;"></div>
        </div>

        <div id='plovouciHlaska' class='popisek'></div> <!-- popisek -->

        <script>
            $(function() {
                var availableTags = [<?= $tags ?>];
                $( "#tags" ).autocomplete({
                    source: availableTags
                });
            });
        </script>



</div>
</body>
</html>

<?php
Db::close();
?>
