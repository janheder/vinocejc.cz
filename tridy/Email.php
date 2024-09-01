<?php
// trida pro odesilani emailu
// do konstruktoru predavame typ 
// druhy parametr udava, ze je email s prilohami

class Email
{

public  $typ; // plaintext, html, reklamace
public  $prilohy;
public  $prilohy_reklamace;
private $to;  
private $from;  
private $subject;  
private $body;
private $priloha;
private $cc;
private $bcc;

	function __construct($typ,$prilohy=false)
	{
		 $this->typ = $typ;
		 $this->prilohy = $prilohy;
	}

	
	
	public function nastavFrom($from)
	{

		if($from)
		{
		  $this->from = sanitize($from);
		}
		
	}
	
	public function nastavTo($to)
	{

		if($to)
		{
		  $this->to = sanitize($to);
		}
		
	}
	
	public function nastavCc($cc)
	{

		if($cc)
		{
		  $this->cc = sanitize($cc);
		}
		
	}
	
	public function nastavBcc($bcc)
	{

		if($bcc)
		{
		  $this->bcc = sanitize($bcc);
		}
		
	}
	
	public function nastavSubject($subject)
	{

		if($subject)
		{
		  $this->subject = "=?utf-8?B?".base64_encode(sanitize($subject))."?="; // možno i s diakritikou
		}
		
	}
	
	public function nastavBody($body)
	{

		if($body)
		{

		  $this->body = $body;
		}
		
		
	}
	
	public function pridejPrilohu($priloha,$nazev_prilohy)
	{

		if($priloha)
		{

		  $this->priloha = sanitize($priloha);
		}
		if($nazev_prilohy)
		{

		  $this->nazev_prilohy = sanitize($nazev_prilohy);
		}
		
		
	}
	
	
	public function pridejPrilohyReklamace($prilohy)
	{
		// pole
		
		if($prilohy)
		{
		  $this->prilohy_reklamace = $prilohy;
		}
		
		
	}
	
	
	public function odesliEmail()
	{
		
		if($this->from && $this->to && $this->subject && $this->body)
		{
			
			if($this->typ=='plaintext' && !$this->prilohy)
			{
				// plaintext bez prilohy
				$headers = "From: ".$this->from."\n";
				$headers .= "Return-Path :".$this->from."\n";
				$headers .= "Reply-To :".$this->from."\n";
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-type: text/plain; charset=utf-8\n";
				$headers .= "Content-Transfer-Encoding: 8bit\n";
				
				if($this->cc)
				{
					$headers .= "Cc: ".$this->cc."\n";
					
				}
				
				if($this->bcc)
				{
					$headers .= "Bcc: ".$this->bcc."\n";
					
				}
				
				$headers .= "X-Mailer: powered by PHP / ".phpversion();
				
				$vysledek = mail($this->to, $this->subject, $this->body,$headers);
				return $vysledek;
			}
			elseif($this->typ=='plaintext' && $this->prilohy)
			{
				// plaintext s prilohou
				
				if($this->priloha)
				{
				$content = file_get_contents($this->priloha); // nazev souboru vcetne relativni cesty (./prilohy/cenik12345.pdf)
				$content = chunk_split(base64_encode($content));
				$uid = md5(uniqid(time()));
	
				$headers = "From: ".$this->from."\n";
				$headers .= "Return-Path :".$this->from."\n";
				$headers .= "Reply-To :".$this->from."\n";
				
				if($this->cc)
				{
					$headers .= "Cc: ".$this->cc."\n";
					
				}
				
				if($this->bcc)
				{
					$headers .= "Bcc: ".$this->bcc."\n";
					
				}
				
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\n\n";

				$body = "--". $uid."\n";
			    $body .= "Content-type: text/plain; charset=utf-8\n";
			    $body .= "Content-Transfer-Encoding: 7bit\n\n";
			    $body .= $this->body."\n\n";
			    $body .= "--".$uid."\n";
			    $body .= "Content-Type: application/octet-stream; name=\"".$this->nazev_prilohy."\"\n";
				$body .= "Content-Transfer-Encoding: base64\n";
				$body .= "Content-Disposition: attachment; filename=\"".$this->nazev_prilohy."\"\n\n";
				$body .= $content."\n\n";
				$body .= "--".$uid."--";
				
				$vysledek = mail($this->to, $this->subject, $body,$headers);
				return $vysledek;


			    }
				else
				{
					return false;
				}
			    
			}
			elseif($this->typ=='html' && !$this->prilohy)
			{
				// html bez prilohy
				

				$headers = "From: ".$this->from."\n";
				$headers .= "Return-Path :".$this->from."\n";
				$headers .= "Reply-To :".$this->from."\n";
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-type: text/html; charset=utf-8\n";
				$headers .= "Content-Transfer-Encoding: 8bit\n";
				
				if($this->cc)
				{
					$headers .= "Cc: ".$this->cc."\n";
					
				}
				
				if($this->bcc)
				{
					$headers .= "Bcc: ".$this->bcc."\n";
					
				}
				
				$headers .= "X-Mailer: Powered by PHP /".phpversion();
				
				$vysledek = mail($this->to, $this->subject, $this->body,$headers);
				return $vysledek;

			}
			elseif($this->typ=='html' && $this->prilohy)
			{
				// html s prilohou
				if($this->priloha)
				{
				$content = file_get_contents($this->priloha); // nazev souboru vcetne relativni cesty (./prilohy/cenik12345.pdf)
				$content = chunk_split(base64_encode($content));
				$uid = md5(uniqid(time()));
	
				$headers = "From: ".$this->from."\n";
				$headers .= "Return-Path :".$this->from."\n";
				$headers .= "Reply-To :".$this->from."\n";
				
				if($this->cc)
				{
					$headers .= "Cc: ".$this->cc."\n";
					
				}
				
				if($this->bcc)
				{
					$headers .= "Bcc: ".$this->bcc."\n";
					
				}
				
				
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\n\n";

				$body = "--". $uid."\n";
			    $body .= "Content-type: text/html; charset=utf-8\n";
			    $body .= "Content-Transfer-Encoding: 7bit\n\n";
			    $body .= $this->body."\n\n";
			    $body .= "--".$uid."\n";
			    $body .= "Content-Type: application/octet-stream; name=\"".$this->nazev_prilohy."\"\n";
				$body .= "Content-Transfer-Encoding: base64\n";
				$body .= "Content-Disposition: attachment; filename=\"".$this->nazev_prilohy."\"\n\n";
				$body .= $content."\n\n";
				$body .= "--".$uid."--";

				
				$vysledek = mail($this->to, $this->subject, $body,$headers);
				return $vysledek;


			    }
				else
				{
					return false;
				}
			
			}
			elseif($this->typ=='reklamace')
			{
				
				// plaintext s prilohami
				if($this->prilohy_reklamace)
				{
					$headers = "From: ".$this->from."\n";
					$headers .= "Return-Path :".$this->from."\n";
					$headers .= "Reply-To :".$this->from."\n";
					
					if($this->cc)
					{
						$headers .= "Cc: ".$this->cc."\n";
						
					}
					
					if($this->bcc)
					{
						$headers .= "Bcc: ".$this->bcc."\n";
						
					}

				$uid = md5(uniqid(time()));
				
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\n\n";

				$body = "--". $uid."\n";
			    $body .= "Content-type: text/plain; charset=utf-8\n";
			    $body .= "Content-Transfer-Encoding: 7bit\n\n";
			    $body .= $this->body."\n\n";


				// pole příloh
					$pocet_priloh_filter = array_filter($this->prilohy_reklamace['name']);
					$pocet_priloh = count(array_filter($pocet_priloh_filter));

					for ($xp = 0; $xp < $pocet_priloh; $xp++)
					{  

							$priloha_nazev = $this->prilohy_reklamace['name'][$xp];
							$priloha_tmp = $this->prilohy_reklamace['tmp_name'][$xp];

							$content = file_get_contents($priloha_tmp);  
						    $content2 = chunk_split(base64_encode($content));
						    
						    $body .= "--".$uid."\n";
						    $body .= "Content-Type: application/octet-stream; name=\"".$priloha_nazev."\"\n";
							$body .= "Content-Transfer-Encoding: base64\n";
							$body .= "Content-Disposition: attachment; filename=\"".$priloha_nazev."\"\n\n";
							$body .= $content2."\n\n";

					}
					
					
					$body .= "--".$uid."--";
				

				
				$vysledek = mail($this->to, $this->subject, $body,$headers);
				return $vysledek;


			    }
			    else
			    {
				   // bez příloh

				    $headers = "From: ".$this->from."\n";
					$headers .= "Return-Path :".$this->from."\n";
					$headers .= "Reply-To :".$this->from."\n";
					$headers .= "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/plain; charset=utf-8\n";
					$headers .= "Content-Transfer-Encoding: 8bit\n";
					
					if($this->cc)
					{
						$headers .= "Cc: ".$this->cc."\n";
						
					}
					
					if($this->bcc)
					{
						$headers .= "Bcc: ".$this->bcc."\n";
						
					}
					
					$headers .= "X-Mailer: powered by PHP / ".phpversion();
					
					$vysledek = mail($this->to, $this->subject, $this->body,$headers);
					return $vysledek;
				
				}
				 
			
			}
			else
			{
				return false;
			}
		
	}
	else
	{
		return false;
	}
  
  }
	
	
	
	
}

?>
