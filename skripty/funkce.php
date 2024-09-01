<?php

// funkce - zakladni funkce systemu

$stav_adm_arr = array(1=>'aktivní',0=>'neaktivní');
$stav_adm_sluzby_arr = array(1=>'ANO',0=>'NE');
$staty_arr = array(1=>'Česká republika',2=>'Slovensko');
$typ_sl_kod_arr = array(1=>'Částka',2=>'Procento');
$staty_iso_arr = array(1=>'CZ',2=>'SK');
$hvezdy_arr = array(1=>'*',2=>'**',3=>'***',4=>'****',5=>'*****');

$_SESSION['stav_adm_arr'] = $stav_adm_arr;
$_SESSION['stav_adm_sluzby_arr'] = $stav_adm_sluzby_arr;
$_SESSION['staty_arr'] = $staty_arr;
$_SESSION['typ_sl_kod_arr'] = $typ_sl_kod_arr;
$_SESSION['staty_iso_arr'] = $staty_iso_arr;
$_SESSION['hvezdy_arr'] = $hvezdy_arr;

function autoloadTridy($trida)
{
                
     if(is_file(__WEB_DIR__.'/tridy/'.$trida.'.php')) 
     {
		require_once(__WEB_DIR__.'/tridy/'.$trida.'.php');

	 }

	         
}
 

function handle_sql_errors($query, $error_message)
{
    echo '<pre>';
    echo $query;
    echo '</pre>';
    echo $error_message;
    die();
}


function sanitize($s)
{
	if(is_numeric($s))
	{ 
	 $san = intval($s);	// cele cislo
	}
	elseif(is_float($s))
	{
	 $san = floatval($s);	// des. cislo
	}
	elseif(is_array($s))
	{   $s2 = array();
	      foreach ($s as $key => $value)
               {
                $s2[$key] = sanitize($value);
               }

	 $san = $s2;	// pole
    }
	else
	{
	 $san = addslashes(strip_tags($s));	// retezec	
	}

return $san;
}



function getip() 
{
    if ($_SERVER) 
	 {
        if ( $_SERVER['HTTP_X_FORWARDED_FOR'] ) 
        {
            $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } 
        elseif ( $_SERVER['HTTP_CLIENT_IP'] ) 
        {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } 
        else 
        {
            $realip = $_SERVER['REMOTE_ADDR'];
        }

     } 
	 else 
	 {
        if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) 
        {
            $realip = getenv( 'HTTP_X_FORWARDED_FOR' );
        } 
        elseif ( getenv( 'HTTP_CLIENT_IP' ) )
        {
            $realip = getenv( 'HTTP_CLIENT_IP' );
        } 
        else 
        {
            $realip = getenv( 'REMOTE_ADDR' );
        }
     }

    return $realip;
}


function cut_text($text,$delka)
{
	$pocet_znaku = mb_strlen($text,'UTF-8');
	if($delka<$pocet_znaku)
	{
		$c_text = substr($text, 0, $delka); 
		if(!preg_match('//u', $c_text)) 
		{
		/* Odstraníme poslední půlznak */
		$c_text = preg_replace('/[\xc0-\xfd][\x80-\xbf]*$/', '', $c_text);
		} 
	}
	else
	{
	$c_text = $text;
	}
	
	return $c_text;
}


function telefon_karta($tel)
{
	$a = array(" ","+","-",",",".","420","+420","00420");
	$b = array("","","","","","","","");
	$sb = str_replace($a, $b, $tel);
	
	return $sb;
}


function bez_diakritiky($text) 
{

    $url = preg_replace('~[^\\pL0-9_]+~u', '-', $text);
    $url = trim($url, '-');
    $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
    $url = strtolower($url);
    $url = preg_replace('~[^-a-z0-9_]+~', '', $url);
    return $url;
}


function kontrola_ref()
{
$server = "https://".$_SERVER['SERVER_NAME'];
$referer = $_SERVER['HTTP_REFERER'];

	if(!preg_match("#^".$server."#", $referer))
	{
	    return ("Spatny referer<br>originalni stranky jsou na <a href=\"".__URL__."\">".__URL__."</a>");
	}
	else
	{
		return false;
	}
}


function dump_array($array) 
{
	if(is_array($array))
	{
		$size = count($array);
		$string = "";
		if($size) 
		{
			$count = 0;
			$string .= "{ ";
			// Vkládáme klíče a hodnoty všech prvků pole do textového řetězce.
			foreach ($array as $var => $value) 
			{
				$string .= $var." = ".$value;
				if ($count++ < ($size-1)) 
				{
				$string .= ", ";
				}
			}
			$string .= " }";
		}
		return $string;
	} 
	else 
	{
	// Pokud se nejedná o pole, vrátíme původní hodnotu.
	return $array;
	}
}


class XML2Array {
 
    private static $xml = null;
	private static $encoding = 'UTF-8';
 
    /**
     * Initialize the root XML node [optional]
     * @param $version
     * @param $encoding
     * @param $format_output
     */
    public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
        self::$xml = new DOMDocument($version, $encoding);
        self::$xml->formatOutput = $format_output;
		self::$encoding = $encoding;
    }
 
    /**
     * Convert an XML to Array
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return DOMDocument
     */
    public static function &createArray($input_xml) {
        $xml = self::getXMLRoot();
		if(is_string($input_xml)) {
			$parsed = $xml->loadXML($input_xml);
			if(!$parsed) {
				throw new Exception('[XML2Array] Error parsing the XML string.');
			}
		} else {
			if(get_class($input_xml) != 'DOMDocument') {
				throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
			}
			$xml = self::$xml = $input_xml;
		}
		$array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $array;
    }
 
    /**
     * Convert an Array to XML
     * @param mixed $node - XML as a string or as an object of DOMDocument
     * @return mixed
     */
    private static function &convert($node) {
		$output = array();
 
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = trim($node->textContent);
				break;
 
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
 
			case XML_ELEMENT_NODE:
 
				// for each child node, call the covert function recursively
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = self::convert($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;
 
						// assume more nodes of same kind are coming
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					} else {
						//check if it is not an empty text node
						if($v !== '') {
							$output = $v;
						}
					}
				}
 
				if(is_array($output)) {
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1) {
							$output[$t] = $v[0];
						}
					}
					if(empty($output)) {
						//for empty nodes
						$output = '';
					}
				}
 
				// loop through the attributes and collect them
				if($node->attributes->length) {
					$a = array();
					foreach($node->attributes as $attrName => $attrNode) {
						$a[$attrName] = (string) $attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					if(!is_array($output)) {
						$output = array('@value' => $output);
					}
					$output['@attributes'] = $a;
				}
				break;
		}
		return $output;
    }
 
    /*
     * Get the root XML node, if there isn't one, create it.
     */
    private static function getXMLRoot(){
        if(empty(self::$xml)) {
            self::init();
        }
        return self::$xml;
    }
}



function get_operating_system() 
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $operating_system = 'Unknown Operating System';
    $ostype = 1;

    //Get the operating_system
    if (preg_match('/iphone/i', $u_agent)) 
    {
        $operating_system = 'IPhone';
        $ostype = 2;
    } 
    elseif (preg_match('/ipod/i', $u_agent)) 
    {
        $operating_system = 'IPod';
        $ostype = 2;
    } 
    elseif (preg_match('/ipad/i', $u_agent)) 
    {
        $operating_system = 'IPad';
        $ostype = 2;
    } 
    elseif (preg_match('/android/i', $u_agent)) 
    {
        $operating_system = 'Android';
        $ostype = 2;
    } 
    elseif (preg_match('/blackberry/i', $u_agent)) 
    {
        $operating_system = 'Blackberry';
        $ostype = 2;
    } 
    elseif (preg_match('/webos/i', $u_agent)) 
    {
        $operating_system = 'Mobile';
        $ostype = 2;
    }
    elseif (preg_match('/linux/i', $u_agent)) 
    {
        $operating_system = 'Linux';
        $ostype = 1;
    } 
    elseif (preg_match('/macintosh|mac os x|mac_powerpc/i', $u_agent)) 
    {
        $operating_system = 'Mac';
        $ostype = 1;
    } 
    elseif (preg_match('/windows|win32|win98|win95|win16/i', $u_agent)) 
    {
        $operating_system = 'Windows';
        $ostype = 1;
    } 

    
    return $operating_system;
}


function random_strings($delka) 
{ 
  
    // String of all alphanumeric character 
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
  
    // Shufle the $str_result and returns substring 
    // of specified length 
    return substr(str_shuffle($str_result), 0, $delka); 
} 


function pocet_produktu_skladem()
{
   $pocet = 0;
   $data_k = Db::queryRow('SELECT sum(ks_skladem) AS POCET FROM produkty_varianty WHERE aktivni_var=? ', array(1));
   $pocet = $data_k['POCET'];
   return $pocet;
}


function eval_buffer($string) 
{
   ob_start();
   eval("$string[2];");
   $return = ob_get_contents();
   ob_end_clean();
   return $return;
}

function eval_print_buffer($string) 
{
   ob_start();
   eval("print $string[2];");
   $return = ob_get_contents();
   ob_end_clean();
   return $return;
}

function eval_html($string) 
{
   $string = preg_replace_callback("/(<\?=)(.*?)\?>/si","eval_print_buffer",$string);
   return preg_replace_callback("/(<\?php|<\?)(.*?)\?>/si","eval_buffer",$string);
}  

?>
