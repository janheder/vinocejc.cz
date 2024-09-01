<?php
// trida pro generovani formularu
// wrapper = heder - pro prezentační vrstvu
class Form
{

    public $required;
    public $id;
    public $name ;
    public $action;
    public $method;
    public $submit;
    public $charset;
    public $form_html;
    public $multipart;
    public $form_open;
    //public $errors = array();

    function __construct($wrapper='', $action='', $method='post', $name='', $id='', $charset='utf-8', $form_html='', $multipart='', $ref_check=false)
    {

        if (!$wrapper) 
        {
			$this->wrapper = '';
		} 
        else 
        {
            $this->wrapper = strtolower($wrapper);
            
        }
	
	   $this->action = $action;
	   $this->method = $method;
	   $this->name = $name;
	   $this->id = $id;
	   $this->charset = $charset;
	   $this->form_html = $form_html;
	   $this->multipart = $multipart;
	   $this->ref_check = $ref_check;
	   $this->errors = false;
	   $this->refill = false;
	   
	   
	   if($multipart)
	   {
		   $this->multipart = 'enctype="multipart/form-data"';
	   }
	   else
	   {
		   $this->multipart = '';
	   }
	   
	   
	   $this->form_open = '<form autocomplete="off" name="'.$this->name.'" id="'.$this->id.'" method="'.$this->method.'" action="'.$this->action.'" accept-charset="'.$this->charset.'" '.$this->form_html.' '.$this->multipart.' >';
	}
	
	
	public function formOpen()
	{
		return $this->form_open;
		
	}
	
	


	
	public function inputText($name, $id, $value, $labelname, $input_html, $placeholder, $required=false, $refill=false, $append=false)
	{
		$inp_ret = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_ret .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_ret .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':';
			// úpravy z 11.8.2020 - hnězdička u req.
			if($required){$inp_ret .= ' <span class="r">*</span>';}
			$inp_ret .='</label>';
		}
		elseif($labelname)
		{
			$inp_ret .= '<label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if(!$id)
		{
			$id = $name;
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if($placeholder){$placeholder_inp = 'placeholder="'.$placeholder.'"';}
		else{$placeholder_inp = '';}
		
			$inp_ret .= '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' '.$placeholder_inp.' >';
			
			if($append){ $inp_ret .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_ret .= '</div>';
		}
		
		return $inp_ret;

	}
	
	
	
	public function inputEmail($name, $id, $value, $labelname, $input_html, $placeholder, $required=false, $refill=false, $append=false)
	{
		$inp_ret = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_ret .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_ret .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':';
			// úpravy z 11.8.2020 - hnězdička u req.
			if($required){$inp_ret .= ' <span class="r">*</span>';}
			$inp_ret .= '</label>';
		}
		elseif($labelname)
		{
			$inp_ret .= '<label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if(!$id)
		{
			$id = $name;
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if($placeholder){$placeholder_inp = 'placeholder="'.$placeholder.'"';}
		else{$placeholder_inp = '';}
		
			$inp_ret .= '<input type="email" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' '.$placeholder_inp.' >';
			
			if($append)
			{	
				if($this->wrapper == 'heder')
				{
					$inp_ret .= $append;
				}	
				else
				{
					$inp_ret .= ' <span>'.$append.'</span>';
				}
				 
			}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_ret .= '</div>';
		}
		
		return $inp_ret;

		
	}
	
	
	
	public function inputTel($name, $id, $value, $labelname, $input_html, $placeholder, $required=false, $refill=false, $append=false)
	{
		$inp_ret = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_ret .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_ret .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':';
			// úpravy z 11.8.2020 - hnězdička u req.
			if($required){$inp_ret .= ' <span class="r">*</span>';}
			$inp_ret .='</label>';
		}
		elseif($labelname)
		{
			$inp_ret .= '<label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if(!$id)
		{
			$id = $name;
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if($placeholder){$placeholder_inp = 'placeholder="'.$placeholder.'"';}
		else{$placeholder_inp = '';}
		
			$inp_ret .= '<input type="tel" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' '.$placeholder_inp.' >';
			
			if($append){ $inp_ret .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_ret .= '</div>';
		}
		
		return $inp_ret;

		
	}
	
	
	
	public function inputNumber($name, $id, $value, $labelname, $input_html, $placeholder, $required=false, $refill=false, $append=false)
	{
		$inp_ret = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_ret .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_ret .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':';
			// úpravy z 11.8.2020 - hnězdička u req.
			if($required){$inp_ret .= ' <span class="r">*</span>';}
			$inp_ret .= '</label>';
		}
		elseif($labelname)
		{
			$inp_ret .= '<label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if(!$id)
		{
			$id = $name;
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if($placeholder){$placeholder_inp = 'placeholder="'.$placeholder.'"';}
		else{$placeholder_inp = '';}
		
			$inp_ret .= '<input type="number" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' '.$placeholder_inp.' >';
			
			if($append){ $inp_ret .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_ret .= '</div>';
		}
		
		return $inp_ret;

		
	}
	
	
	
	
	public function inputPassword($name, $id, $value, $labelname, $input_html, $required=false, $append=false)
	{
		$inp_pass = '';
		
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_pass .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_pass .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':';
			// úpravy z 11.8.2020 - hnězdička u req.
			if($required){$inp_pass .= ' <span class="r">*</span>';}
			$inp_pass .= '</label>';
		}
		elseif($labelname)
		{
			$inp_pass .= '<label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if(!$id)
		{
			$id = $name;
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
			$inp_pass .= '<input type="password" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' >';
			
			if($append){ $inp_pass .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_pass .= '</div>';
		}
		
		return $inp_pass;
	}
	
	
	public function inputColor($name, $id, $value, $labelname, $input_html, $required=false, $append=false)
	{
		$inp_pass = '';
		
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_pass .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_pass .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':';
			// úpravy z 11.8.2020 - hnězdička u req.
			if($required){$inp_pass .= ' <span class="r">*</span>';}
			$inp_pass .= '</label>';
		}
		elseif($labelname)
		{
			$inp_pass .= '<label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if(!$id)
		{
			$id = $name;
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
			$inp_pass .= '<input type="color" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' >';
			
			if($append){ $inp_pass .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_pass .= '</div>';
		}
		
		return $inp_pass;
	}
	
	
	public function inputTextarea($name, $id, $value, $labelname, $input_html, $placeholder, $required=false, $refill=false, $append=false)
	{
		
		$txtarea_ret = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$txtarea_ret .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$txtarea_ret .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':';
			// úpravy z 11.8.2020 - hnězdička u req.
			if($required){$txtarea_ret .= ' <span class="r">*</span>';}
			$txtarea_ret .= '</label>';
		}
		elseif($labelname)
		{
			$txtarea_ret .= '<label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if(!$id)
		{
			$id = $name;
		}
		
		if($placeholder){$placeholder_inp = 'placeholder="'.$placeholder.'"';}
		else{$placeholder_inp = '';}
		
			$txtarea_ret .= '<textarea name="'.$name.'" id="'.$id.'" '.$input_html.' '.$required_inp.' '.$placeholder_inp.' >'.$value.'</textarea>';
			
			if($append){ $txtarea_ret .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$txtarea_ret .= '</div>';
		}
		
		return $txtarea_ret;
		
	}
	
	
	public function inputFile($name, $id, $value, $labelname, $input_html, $required=false, $refill=false, $append=false)
	{
		
		$inpf_ret = '';
		$name_ch = str_replace('[]','',$name);
		
		if($this->wrapper == 'bootstrap')
		{
			$inpf_ret .= '<div id="_'.$name_ch.'" class="form-group"><label class="control-label" for="'.$name_ch.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inpf_ret .= '<div id="_'.$name_ch.'" class="registerForm__inputGroup"><label for="'.$name_ch.'">'.$labelname.':';
			if($required){$inpf_ret .= ' <span class="r">*</span>';}
			$inpf_ret .= '</label>';
		}
		elseif($labelname)
		{
			$inpf_ret .= '<label class="control-label" for="'.$name_ch.'">'.$labelname.':</label>';
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if(!$id)
		{
			$id = $name;
		}
		
		
			$inpf_ret .= '<input type="file" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' >';
			
			if($append)
			{ 
				if($this->wrapper == 'heder')
				{
					$inpf_ret .= ' <div>'.$append.'</div>';
				}
				else
				{
				    $inpf_ret .= ' <span>'.$append.'</span>';	
				}
				
			}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inpf_ret .= '</div>';
		}
		
		return $inpf_ret;
		
	}
	
	
	public function inputSelect($name, $id, $value, $arr_data, $labelname, $input_html,  $required=false, $refill=false, $append=false)
	{
		// value is selected 
		
		$inp_sel = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_sel .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="_'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_sel .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':';
			if($required){$inp_sel .= ' <span class="r">*</span>';}
			$inp_sel .= '</label>';
		}
		elseif($labelname)
		{
			$inp_sel .= '<label class="control-label" for="_'.$name.'">'.$labelname.':</label>';
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if(!$id)
		{
			$id = $name;
		}
		
		
			$inp_sel .= '<select name="'.$name.'" id="'.$id.'" '.$input_html.' '.$required_inp.' ';
			if($this->wrapper == 'bootstrap'){$inp_sel .= ' class="form-control" ';}
			$inp_sel .= ' >';
			
			if(is_array($arr_data))
			{
				foreach($arr_data as $sel_key=>$sel_val)
				{	
					if($this->wrapper == 'heder')
					{
						if($sel_key==0){$sel_key = '';} // kvůli required
						$inp_sel .= '<option value="'.$sel_key.'" ';
						if(isset($value) && $value==$sel_key){$inp_sel .= ' selected ';}
						$inp_sel .= '>'.$sel_val.'</option>';
					}
					else
					{
					    $inp_sel .= '<option value="'.$sel_key.'" ';
						if(isset($value) && $value==$sel_key){$inp_sel .= ' selected ';}
						$inp_sel .= '>'.$sel_val.'</option>';	
					}
					
					
				}
			}
			
			$inp_sel .= '</select>';
			
			if($append){ $inp_sel .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_sel .= '</div>';
		}
		
		return $inp_sel;
		
	}
	
	
	
	public function inputRadio($name, $id, $value, $arr_data, $labelname, $input_html, $required=false, $refill=false, $append=false)
	{
		
		// value is checked 
		
		$inp_radio = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_radio .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_radio .= '<div id="_'.$name.'" class="registerForm__inputGroup --inline"><label for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($labelname)
		{
			$inp_radio .= '<label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if(!$id)
		{
			$id = $name;
		}
			
			if(is_array($arr_data))
			{
				foreach($arr_data as $sel_key=>$sel_val)
				{
					$inp_radio .= '<input type="radio" name="'.$name.'" id="'.$id.'_'.$sel_key.'" value="'.$sel_key.'" '.$input_html.' '.$required_inp.' ';
					if(isset($value) && $value==$sel_key){$inp_radio .= ' checked ';}
					$inp_radio .= '> <label for="'.$id.'_'.$sel_key.'">'.$sel_val.'</label>';
				}
			}
			
			
			if($append){ $inp_radio .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_radio .= '</div>';
		}
		
		return $inp_radio;
		
	}
	
	
	
	public function inputCheckboxMulti($name, $id, $value, $arr_data, $labelname, $input_html, $required=false, $refill=false, $append=false)
	{
		
		// value is checked 
		
		$inp_chbm = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_chbm .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{   
			if($labelname)
			{
				$inp_chbm .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':</label>';
			}
			
			
		}
		elseif($labelname)
		{   
			$name_ch = str_replace('[]','',$name);
			$inp_chbm .= '<label class="control-label" for="'.$name_ch.'">'.$labelname.':</label>';
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if(!$id)
		{
			$id = $name;
		}
		
			
			if(is_array($arr_data))
			{ 
				foreach($arr_data as $chbm_key=>$chbm_val)
				{
					if($this->wrapper == 'heder')
					{
						 $inp_chbm .= '<div id="_'.$name.'" class="registerForm__inputGroup --inline">
						 <label for="'.$id.'_'.$chbm_key.'" class="inline">'.$chbm_val.'</label><input type="checkbox" name="'.$name.'" id="'.$id.'_'.$chbm_key.'" value="'.$chbm_key.'" '.$input_html.' '.$required_inp.' ';
						// value is array!
						if(is_array($value) && in_array($chbm_key,$value)){$inp_chbm .= ' checked ';}
						$inp_chbm .= ' > <span class="registerForm__span"></span>';
						$inp_chbm .= '</div>';
					}
					else
					{
						$inp_chbm .= '<div style="float: left; width: 230px;">';
						$inp_chbm .= '<label for="'.$id.'_'.$chbm_key.'" class="inline"><input type="checkbox" name="'.$name.'" id="'.$id.'_'.$chbm_key.'" value="'.$chbm_key.'" '.$input_html.' '.$required_inp.' ';
						// value is array!
						if(is_array($value) && in_array($chbm_key,$value)){$inp_chbm .= ' checked ';}
						$inp_chbm .= ' > '.$chbm_val.'</label><div class="clear"></div>';
						$inp_chbm .= '</div>';
					}
					
					 
				}
			}
			
			 
			
			if($append){ $inp_chbm .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || ($this->wrapper == 'heder' && $labelname ))
		{
			$inp_chbm .= '</div>';
		}
		
		return $inp_chbm;
		
	}
	
	
	public function inputCheckboxMulti2($name, $id, $value, $arr_data, $labelname, $input_html, $required=false, $refill=false, $append=false)
	{
		// jina struktura pole
		// value is checked 
		
		$inp_chbm = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value2 = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value2 = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_chbm .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_chbm .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($labelname)
		{   
			$name_ch = str_replace('[]','',$name);
			$inp_chbm .= '<label class="control-label" for="'.$name_ch.'">'.$labelname.':</label>';
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}
		
		if(!$id)
		{
			$id = $name;
		}
		
			
			if(is_array($arr_data))
			{
				foreach($arr_data as $chbm_key=>$chbm_val)
				{
					$inp_chbm .= '<label for="'.$id.'_'.$chbm_key.'" class="inline"><input type="checkbox" name="'.$name.'" id="'.$id.'_'.$chbm_key.'" value="'.$chbm_key.'|'.$chbm_val.'" '.$input_html.' '.$required_inp.' ';

					if($this->refill && isset($value2) && $chbm_val==$value2){$inp_chb .= ' checked ';}
					// value is array!
					if(is_array($value) && in_array($chbm_val,$value)){$inp_chbm .= ' checked ';}
					$inp_chbm .= ' > '.$chbm_val.'</label><div class="clear"></div>';
					

				}
			}
			
			
			if($append){ $inp_chbm .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_chbm .= '</div>';
		}
		
		return $inp_chbm;
		
	}
	
	
	public function inputCheckbox($name, $id, $value, $data, $chb_name, $labelname, $input_html, $required=false, $refill=false, $append=false)
	{
		
		// value is checked 
		
		$inp_chb = '';
		
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value2 = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value2 = strip_tags($_GET[$name]);}
		}
		
		if($this->wrapper == 'bootstrap')
		{
			$inp_chb .= '<div id="_'.$name.'" class="form-group"><label class="control-label" for="_'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$inp_chb .= '<div id="_'.$name.'" class="registerForm__inputGroup --inline">';
		}
		elseif($labelname)
		{
			$inp_chb .= '<label class="control-label" for="_'.$name.'">'.$labelname.':</label>';
		}
		
		if($required){ $required_inp = 'required';}
		else{$required_inp = '';}	
		
		if(!$id)
		{
			$id = $name;
		}
			
		if($this->wrapper == 'heder')
		{
			$inp_chb .= '<label for="'.$id.'" class="inline">'.$chb_name;
			if($required){$inp_chb .= '&nbsp;<span class="r">*</span>';}
			$inp_chb .= '</label><input type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' ';
			if($this->refill && isset($value2) && $value==$value2){$inp_chb .= ' checked ';}
			elseif(isset($data) && $data==$value){$inp_chb .= ' checked ';}
			$inp_chb .= '> <span class="registerForm__span"></span>';	
		}	
		else
		{
		    $inp_chb .= '<label for="'.$id.'" class="inline"><input type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' '.$required_inp.' ';
			if($this->refill && isset($value) && $value==$value2){$inp_chb .= ' checked ';}
			elseif(isset($data) && $data==$value){$inp_chb .= ' checked ';}
			$inp_chb .= '> '.$chb_name.'</label><div class="clear"></div>';	
		}	
		
			
			
		if($append && $this->wrapper != 'heder'){ $inp_chb .= ' <span>'.$append.'</span>';}
		elseif($append && $this->wrapper == 'heder'){ $inp_chb .= $append;}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$inp_chb .= '</div>';
		}
		
		return $inp_chb;
		
	}
	
	
	
	public function inputHidden($name, $id, $value, $input_html, $refill=false)
	{
		if($this->refill)
		{
			if($this->method=='post' && isset($_POST[$name])){ $value = strip_tags($_POST[$name]);}
			if($this->method=='get' && isset($_GET[$name])){ $value = strip_tags($_GET[$name]);}
		}
		
		if(!$id)
		{
			$id = $name;
		}
		
		$inp_ret .= '<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.'  >';
		
		return $inp_ret;
		
	}
	
	
	public function inputSubmit($name, $id, $value, $labelname, $input_html, $append=false)
	{
	
		$sub_ret = '';
		
		
		if($this->wrapper == 'bootstrap')
		{
			$sub_ret .= '<div id="_'.$name.'" class="form-group"><label class="sr-only" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$sub_ret .= '<div id="_'.$name.'" class="registerForm__inputGroup">';
		}
		elseif($labelname)
		{
			$sub_ret .= '<label class="sr-only" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if(!$id)
		{
			$id = $name;
		}

		

		
			$sub_ret .= '<input type="submit" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' >';
			
			if($append){ $sub_ret .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$sub_ret .= '</div>';
		}
		
		return $sub_ret;
	
	}
	
	
	public function inputButton($name, $id, $value, $labelname, $input_html, $append=false)
	{
		
		$but_ret = '';
		
		
		if($this->wrapper == 'bootstrap')
		{
			$but_ret .= '<div id="_'.$name.'" class="form-group"><label class="sr-only" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$but_ret .= '<div id="_'.$name.'" class="registerForm__inputGroup"><label for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($labelname)
		{
			$but_ret .= '<label class="sr-only" for="'.$name.'">'.$labelname.':</label>';
		}
		
		if(!$id)
		{
			$id = $name;
		}

		

		
			$but_ret .= '<input type="button" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$input_html.' >';
			
			if($append){ $but_ret .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$but_ret .= '</div>';
		}

		
		return $but_ret;
		
	}
	
	
	public function addHTML($html)
	{
	
	 return $html;
	
	}
	
	
	
	public function captcha($name, $labelname, $append=false)
	{
	
	    $but_ret = '';
		
		
		if($this->wrapper == 'bootstrap')
		{
			$but_ret .= '<div id="_'.$name.'" class="form-group"><label class="sr-only" for="'.$name.'">'.$labelname.':</label>';
		}
		elseif($this->wrapper == 'heder')
		{
			$but_ret .= '<div id="_'.$name.'" class="registerForm__inputGroup">';
		}
		elseif($labelname)
		{
			$but_ret .= '<label class="sr-only" for="'.$name.'">'.$labelname.':</label>';
		}

			$but_ret .= ' <div class="g-recaptcha" data-callback="whenCaptchaChecked" data-sitekey="'.__CAPTCHA_SITE_KEY__.'"></div>';
			
			if($append){ $but_ret .= ' <span>'.$append.'</span>';}
		
		
		if($this->wrapper == 'bootstrap' || $this->wrapper == 'heder')
		{
			$but_ret .= '</div>';
		}
		
		return $but_ret;
	
	}
	
	
	
	public function submit()
	{
		if($this->method=='post' && $_POST['_formbp'])
		{
			if($this->required)
			{
				$req_arr = explode(',',$this->required);
				foreach($req_arr as $req_k=>$req_v)
				{
					if(!$_POST[$req_v] && !$_FILES[$req_v]['size'])
					{
						// úprava i pro files
						$this->addToErrors('proměnná <b>'.$req_v.'</b> není vyplněna');
					} 
					
					// kontrola captchy
					if($req_v == 'g-recaptcha-response')
					{
						if($this->recaptchaCheck()!==true)
						{
							$this->addToErrors('proměnná <b>'.$req_v.'</b> je chybně vyplněna');

						}
					}
				}
				 
			}
			
			// kontrola refereru
			if($this->ref_check && kontrola_ref())
			{
				$this->addToErrors('Špatný referer');
			}
			
			return true;
		}
		
		if($this->method=='get' && $_GET['_formbp'])
		{
			if($this->required)
			{
				$req_arr = explode(',',$this->required);
				foreach($req_arr as $req_k=>$req_v)
				{
					if(!$_GET[$req_v])
					{
						// přes GET neposíláme soubory
						$this->addToErrors('proměnná <b>'.$req_v.'</b> není vyplěna');
					} 
					
					// kontrola captchy
					if($req_v == 'g-recaptcha-response')
					{
						if($this->recaptchaCheck()!==true)
						{
							$this->addToErrors('proměnná <b>'.$req_v.'</b> je chybně vyplněna');
						}
					}
				}
				
				
				// kontrola refereru
				if($this->ref_check && kontrola_ref())
				{
					$this->addToErrors('Špatný referer');
				}
				 
			}
			
			return true;
		}
		
		return false;
	}
	
	
	public function formClose()
    {
        return '<input type="hidden" name="_formbp" value="'.openssl_encrypt(time(),'AES-128-ECB',__HESLO_ENCRYPT__).'" ></form>';
    }
    
    
    public function errors()
    {
        if (!empty($this->errors)) 
        {
            return $this->errors;
        } 
        else 
        {
            return false;
        }
    }
    
    
    public function recaptchaCheck()
	{	
		// https://www.google.com/recaptcha/admin/create
		if($_POST['g-recaptcha-response'])
		{
			$captcha_secret_key = __CAPTCHA_SECRET_KEY__;
			$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$captcha_secret_key."&response=".strip_tags($_POST['g-recaptcha-response']));
			$response = json_decode($verify, true);
			if($response['success'] == true)
			{
				return true;
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
    
    
    public function addToErrors($str)
    {
		if(!$this->errors)
		{
			$this->errors = array();
		}
        array_push($this->errors, $str);
        $this->refill = true;
    }
	
	
	
}
