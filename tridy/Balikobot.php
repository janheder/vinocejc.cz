<?php
// trida Balikobot

class Balikobot
{

private $api_user;  
private $api_key;   
public $id_eshop;
public $id_objednavky;
public $api_url;
public $cp_services;
public $data;

	function __construct($api_user,$api_key,$id_eshop=1,$id_objednavky)
	{
	   $this->api_user = $api_user;
	   $this->api_key = $api_key;
	   $this->id_eshop = $id_eshop;
	   $this->api_url ='https://api.balikobot.cz';
	   $this->id_objednavky = $id_objednavky;
	   $this->cp_services = array(1,2,3,4,5,6,8,7,9,10,11,12,13,14,15,16,19,20,21,22,23,24,25,26,27,28,30,31,32,33,34,37,38,40,41,42,43,44,45,46,47,50,51,52,53,54,55,56,57,58,60,68,69,70,71,76,77,78);
	   $this->eid = $id_eshop.$id_objednavky.time(); // eid generujeme v kostruktoru kvůli vícebalíkovým zásilkám


	  
	}




	public function zakaznik($name, $street, $city, $zip, $phone, $email, $company = null, $country = 'CZ') 
	{
        if (empty($name) || empty($street) || empty($city) || empty($zip) || empty($phone) || empty($email))
            throw new InvalidArgumentException('Chybí důležité údaje zákazníka.');
            
        switch ($country) 
        {
            case 'CZ':
                if (!preg_match('/^\d{5}$/', $zip))
                    throw new InvalidArgumentException('Invalid zip code has been entered. Match XXXXX pattern.');
                break;
			
			case 'SK':
                if (!preg_match('/^\d{5}$/', $zip))
                    throw new InvalidArgumentException('Invalid zip code has been entered. Match XXXXX pattern.');
                break;
			
            default:
                throw new UnexpectedValueException('Validation method is not implemented for '.$country.' country.');
        }
 
        /*if (!preg_match('/^\+420\d{9}$/', $phone) && !preg_match('/^\+421\d{9}$/', $phone))
            throw new \InvalidArgumentException('Invalid phone has been entered. Match +420YYYYYYYYY pattern.');*/

        $this->data['data']['rec_name'] = $name;
        $this->data['data']['rec_street'] = $street;
        $this->data['data']['rec_city'] = $city;
        $this->data['data']['rec_zip'] = $zip;
        $this->data['data']['rec_phone'] = $phone;
        $this->data['data']['rec_email'] = $email;
        $this->data['data']['rec_country'] = $country;
        if (isset($company))
           $this->data['data']['rec_firm'] = $company;

        $this->data['isCustomer'] = true;

        return $this;
    }
    
    
    public function sluzba($shipper, $service, array $options = []) 
    {
        if (empty($shipper) )
            throw new InvalidArgumentException('Invalid argument has been entered.');
            
        // clean first
        //$this->clean();

        switch ($shipper) 
        {
            case 'cp':
                if (!isset($options['price']))
                    throw new InvalidArgumentException('The price option is required for '.$shipper.' shipper.');
                break;

            case 'dpd':
                if ($service == 3) 
                {   //pickup
                    if (empty($options['branch_id']))
                        throw new InvalidArgumentException('The branch option is required for pickup service.');
                }
                break;

            case 'ppl':
                if (($service == 15) || ($service == 19))
                {	//palette shipping
                    if (!isset($options['mu_type']))
                        throw new InvalidArgumentException('The mu type option is required for this service.');
                        
                    if (!isset($options['weight']))
                        throw new InvalidArgumentException('The weight option is required for this service.');
                        
                } 
                else 
                {
                    if (isset($options['note']))
                        throw new InvalidArgumentException('The note option is not supported for this service.');
                }
                break;

            case 'zasilkovna':
                if (!isset($options['branch_id']))
                    throw new InvalidArgumentException('The branch option is required for '.$shipper.' shipper.');
                    
                if (!isset($options['price']))
                    throw new InvalidArgumentException('The price option is required for '.$shipper.' shipper.');
                break;

            case 'geis':
                if (isset($options['del_insurance']) && !isset($options['price']))
                    throw new InvalidArgumentException('The price option is required for insurance option.');
                    
                if ($service == 6) 
                {	//pickup
                    if (empty($options['branch_id']))
                        throw new InvalidArgumentException('The branch option is required for pickup service.');
                } 
                elseif (($service == 4) || ($service == 5)) 
                {	// palette
                    if (empty($options['mu_type']))
                        throw new InvalidArgumentException('The mu type option is required for pickup service.');
                        
                    if (empty($options['weight']))
                        throw new InvalidArgumentException('The weight option is required for pickup service.');
                }
                break;

            case 'ulozenka':
                if (in_array($service, [1, 5, 7, 10, 11]))   
                {	// pickup
                    if (empty($options['branch_id']))
                        throw new InvalidArgumentException('The branch option is required for pickup service.');
                }
                if ($service == 2) 
                {
                    if (!isset($options['price']))
                        throw new InvalidArgumentException('The price option is required for this service.');
                }
                if (in_array($service, [2, 6, 7])) 
                {
                    if (empty($options['weight']))
                        throw new InvalidArgumentException('The weight option is required for this service.');
                }
                break;

            case 'intime':
                if (isset($options['del_insurance']) && !isset($options['price']))
                    throw new InvalidArgumentException('The price option is required for insurance option.');
                    
                if (($service == 4) || ($service == 5)) 
                {	// pickup
                    if (empty($options['branch_id']))
                        throw new InvalidArgumentException('The branch option is required for pickup service.');
                }
                break;

            case 'gls':
                if (!isset($options['price']))
                    throw new InvalidArgumentException('The price option is required for '.$shipper.' shipper.');
                    
                if ($service == 2) 
                {	// pickup
                    if (empty($options['branch_id']))
                        throw new InvalidArgumentException('The branch option is required for pickup service.');
                }
                break;

            case 'toptrans':
                if (empty($options['mu_type']))
                    throw new InvalidArgumentException('The mu type option is required for this service.');
                    
                if (empty($options['weight']))
                    throw new InvalidArgumentException('The weight option is required for this service.');
                break;

            case 'pbh':
                if (!isset($options['price']))
                    throw new InvalidArgumentException('The price option is required for '.$shipper.' shipper.');
                break;
           
            case 'sp':
                if (!isset($options['price']))
                    throw new InvalidArgumentException('The price option is required for '.$shipper.' shipper.');
                break;     
        }

        // save options
        foreach ($options as $name => $value) 
        {
            $this->saveOption($name, $value, $shipper);
        }
        
        $this->data['data']['service_type'] = $service;
        $this->data['shipper'] = $shipper;

        $this->data['isService'] = true;

        return $this;
    }
    
    
    
    
    public function saveOption($name, $value, $shipper = null) 
    {
			/*var_dump($shipper);
			var_dump($name);
			var_dump($value);*/
			
        if (empty($name))
            throw new InvalidArgumentException('Invalid argument has been entered.');

        switch ($name) 
        {
            case 'branch_id':
                // pro PPL musíme z neznámého důvodu odstranit první dva znaky 
                if($shipper=='ppl')
                {
				  $value = substr($value, 2);
				}
                
                break;
                

            case 'mu_type':
                // do nothing
                break;

            case 'services':
                if (!is_array($value))
                    throw new InvalidArgumentException('Invalid value of services option has been entered.');

                foreach ($value as $serviceItem) 
                {
                    if (!in_array($serviceItem, $this->cp_services))
                        throw new InvalidArgumentException('Invalid '.$serviceItem.' value of services option has been entered.');
                }

                $value = implode('+', $value);
                break;

            case 'sms_notification':
            case 'del_insurance':
            case 'del_exworks':
            case 'comfort_service':
            case 'app_disp':
            case 'phone_notification':
            case 'b2c_notification':
            case 'require_full_age':
                if (!is_bool($value))
                    throw new InvalidArgumentException('Invalid value of '.$name.' option has been entered. Enter boolean.');

                $value = (bool)$value;
                break;

            case 'price':
            case 'weight':
                if (!is_numeric($value))
                    throw new InvalidArgumentException('Invalid value of '.$name.' option has been entered. Enter float.');

                $value = (float)$value;
                break;

            case 'note':
            case 'note_driver':
            case 'note_recipient':
            case 'password':
                if (!is_string($value))
                    throw new InvalidArgumentException('Invalid value of note option has been entered. Enter string.');

                $limit = 64;

                if ($shipper == 'dpd') 
                {
                    $limit = 70;
                } 
                elseif ($shipper == 'ppl') 
                {
                    $limit = 350;
                } 
                elseif ($shipper == 'geis') 
                {
                    $limit = ($name == 'note') ? 57 : 62;
                } 
                elseif ($shipper == 'ulozenka') 
                {
                    $limit = ($name == 'password') ? 99 : 75;
                } 
                elseif ($shipper == 'intime') 
                {
                    $limit = 75;
                } 
                elseif ($shipper == 'toptrans') 
                {
                    $limit = 50;
                }

                if (strlen($value) > $limit)
                    throw new InvalidArgumentException('Invalid value of note option has been entered. Maximum length is '.$limit.' characters.');
                break;

            case 'pieces_count':
                if (!is_int($value) || ($value < 1))
                    throw new InvalidArgumentException('Invalid value of pieces has been entered. Enter positive integer.');
                break;

            case 'real_order_id':
                if (!is_numeric($value) || (strlen($value) > 10))
                    throw new InvalidArgumentException('Invalid value of order option has been entered. Enter number, max 10 characters length.');
                break;
        }

        $this->data['data'][$name] = $value;
    }
    
    
    public function dobirka($price, $variableSymbol, $currency = 'CZK') 
    {
        if (empty($price) || empty($variableSymbol))
            throw new InvalidArgumentException('Špatná cena nebo var. symbol.');
            
        if (!is_numeric($price))
            throw new InvalidArgumentException('Špatná cena.');
           
        if (!is_numeric($variableSymbol))
            throw new InvalidArgumentException('Špatný var. symbol.');
            
        if (!$currency)
            throw new InvalidArgumentException('Špatná měna.');

        $this->data['data']['cod_price'] = (float)$price;
        $this->data['data']['vs'] = $variableSymbol;
        $this->data['data']['cod_currency'] = $currency;

        $this->data['isCashOnDelivery'] = true;

        return $this;
    }
    
    
    
    public function pridatBalik() 
    {
        if (!$this->data['isService'] || !$this->data['isCustomer'])
            throw new UnexpectedValueException('Call service and customer method before.');

        $this->data['data']['eid'] = $this->eid;

		// pokud je počet balíků větší jak 1 tak se jedná o vícebalíkovou zásilku a musíme zaslat jedotlivé části do Balíkobotu ve smyčce
		$pocet_baliku = $this->data['data']['pocet_baliku'];
		
		if($pocet_baliku > 1 && $this->data['shipper']!='toptrans')
		{
			for ($i = 1; $i <= $pocet_baliku; $i++) 
			{
			    $this->data['data']['order_number'] = $i;
			    
			    
			    if($this->data['data']['cod_price'])
				{
				   // pokud je dobírka tak se posílá pouze v prvním balíku
				   if($i>1)
				   {
				       unset($this->data['data']['cod_price']);
				   }
				}
				
				//var_dump($this->data);
				$response = $this->call('add', $this->data['shipper'], [$this->data['data']]);
			    
			    if($response[0])
				{
				   // insert

					 $data_insert = array(
								'cislo_obj' => intval($this->data['data']['real_order_id']),
							    'carrier_id' => addslashes($response[0]['carrier_id']),
							    'package_id' => $response[0]['package_id'],
							    'label_url' => addslashes($response[0]['label_url']),
							    'status' => $response[0]['status'],
							    'datum_pridani' => time(),
							    'shipper' => $this->data['shipper'],
							    'cislo_baliku' => $i
							     );
     
			    	 $query_insert = Db::insert('balikobot', $data_insert);
				  
				}
			}
		}
		elseif($pocet_baliku > 1 && $this->data['shipper']=='toptrans')
		{
			// toptrans není kompatibilní s ostatními dopravci
			
			$this->data['data']['order_number'] = 1;
			$this->data['data']['pieces_count'] = $pocet_baliku;
			
			$response = $this->call('add', $this->data['shipper'], [$this->data['data']]);
			    
			    if($response[0])
				{
				   // insert

					 $data_insert = array(
								'cislo_obj' => intval($this->data['data']['real_order_id']),
							    'carrier_id' => addslashes($response[0]['carrier_id']),
							    'package_id' => $response[0]['package_id'],
							    'label_url' => addslashes($response[0]['label_url']),
							    'status' => $response[0]['status'],
							    'datum_pridani' => time(),
							    'shipper' => $this->data['shipper'],
							    'cislo_baliku' => 1
							     );
     
			    	 $query_insert = Db::insert('balikobot', $data_insert);
				  
				}
			
		}
		else
		{
			$response = $this->call('add', $this->data['shipper'], [$this->data['data']]);
			
				if($response[0])
				{
				   // insert

					  $data_insert = array(
								'cislo_obj' => intval($this->data['data']['real_order_id']),
							    'carrier_id' => addslashes($response[0]['carrier_id']),
							    'package_id' => $response[0]['package_id'],
							    'label_url' => addslashes($response[0]['label_url']),
							    'status' => $response[0]['status'],
							    'datum_pridani' => time(),
							    'shipper' => $this->data['shipper'],
							    'cislo_baliku' => 1
							     );
     
			    	 $query_insert = Db::insert('balikobot', $data_insert);
				  
				}
		}
        
        //var_dump($this->data);
        
        //var_dump($response);
        $this->clean();

        if (!isset($response[0]['package_id']))
            throw new InvalidArgumentException('Invalid arguments. Errors: ' . var_dump($response) . '.', 400);

        return $response[0];
    }
    
    
    public function smazatBalik($shipper, $packageId) 
    {
        
        if (empty($shipper) || empty($packageId))
            throw new InvalidArgumentException('Invalid argument has been entered.');

        $response = $this->call('drop', $shipper, ['id' => $packageId]);

        if (!isset($response['status']))
            throw new UnexpectedValueException('Unexpected server response.', 500);
            
        if ($response['status'] == 404)
            throw new UnexpectedValueException('The package does not exist or it was ordered.', 400);
            
        if ($response['status'] != 200)
            throw new UnexpectedValueException("Unexpected server response, code={$response['status']}.", 500);
    }
    
    
    public function objednatSvoz($shipper, array $packages = []) 
    {
        if (empty($shipper))
            throw new InvalidArgumentException('Invalid argument has been entered.');

        $response = $this->call('order', $shipper, empty($packages) ? [] : ['package_ids' => $packages]);

        if (!isset($response['status']))
            throw new UnexpectedValueException('Unexpected server response.', 500);
            
        if ($response['status'] == 406)
            throw new UnexpectedValueException('Invalid package numbers.', 400);
            
        if ($response['status'] != 200)
            throw new UnexpectedValueException("Unexpected server response, code={$response['status']}.", 500);

        return $response;
    }
    
    
    
    public function sledovatZasilku($shipper, $carrierId) 
    {
        if (empty($shipper) || empty($carrierId))
            throw new InvalidArgumentException('Invalid argument has been entered.');

        $response = $this->call('track', $shipper, ['id' => $carrierId]);

        if (isset($response['status']) && ($response['status'] != 200))
            throw new UnexpectedValueException("Unexpected server response, code={$response['status']}.", 500);
            
        if (empty($response[0]))
            throw new UnexpectedValueException('Unexpected server response.', 500);

        return $response[0];
    }
    
    
    
    public function call($request, $shipper, array $data = [], $url = null) 
    {
        if (empty($request) || empty ($shipper))
            throw new InvalidArgumentException('Invalid argument has been entered.');
            
            

        $r = curl_init();
        
        // úprava pro toptrans - musíme zasílat na v2
        if($shipper=='toptrans' && $request=='add' )
        {
			curl_setopt($r, CURLOPT_URL, $this->api_url."/v2/$shipper/$request");
		}
		else
		{
			curl_setopt($r, CURLOPT_URL, $url ? "$this->api_url/$shipper/$request/$url" : "$this->api_url/$shipper/$request");
		}
        

        curl_setopt($r, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($r, CURLOPT_HEADER, false);
        if (!empty($data)) {
            curl_setopt($r, CURLOPT_POST, true);
            curl_setopt($r, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($r,CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode("$this->api_user:$this->api_key"),
            'Content-Type: application/json',
        ]);
        $response = curl_exec($r);
        curl_close($r);
		//var_dump(json_decode($response, true));
        return json_decode($response, true);
        //return $response;
    }
    
    
    
    
	public function clean() 
	{
        $this->data = [
            'isService' => false,
            'isCustomer' => false,
            'isCashOnDelivery' => false,
            'shipper' => null,
            'data' => [],
        ];
	}
    
    
    
}
    
    
    