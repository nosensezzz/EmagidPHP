<?php
namespace Emagid\Html ;


class Form {

	/**
	* @var $model the Db object
	*/
	private $model ; 


	/**
	* @var object - an object that holds validation resutls had they performed.
	*/
	public $valid_state ; 

	/**
	* Constructor
	*
	* @param Object  $model - object extended from Db
	*/
	function __construct($model){
		$this->model = $model; 
	}


	function textBoxFor($field_name, $htmlObjects = []){
		if(!isset($htmlObjects['type']))
		{
			$htmlObjects['type'] = 'text';	
		}

		$html = sprintf("<input name=\"%s\"", $field_name);

		foreach($htmlObjects as $key=>$val){
			$html.=sprintf(" %s=\"%s\"", $key,$val);
		}

		if(isset($this->model->{$field_name})){
			$html.= sprintf(" value=\"%s\"", $this->model->{$field_name});
		}

		$html.=" />";

		return $html;


	}

	function checkBoxFor($field_name, $cbval ,$htmlObjects = []){
		if(!isset($htmlObjects['type']))
		{
			$htmlObjects['type'] = 'checkbox';	
		}

		$html = sprintf("<input name=\"%s\"", $field_name);
		
				
		foreach($htmlObjects as $key=>$val){
			
			
				$html.=sprintf(" %s=\"%s\"" , $key,$val);
			
		}
			$selected = $this->model->{$field_name} == $cbval?"checked=\"checked\"":"";
			$html.= sprintf(" value=\"%s\" %s", $cbval,$selected);
		

		$html.=" />";

		return $html;


	}

	function multicheckBoxFor($field_name, $cbval ,$htmlObjects = []){
		if(!isset($htmlObjects['type']))
		{
			$htmlObjects['type'] = 'checkbox';	
		}

		$html = sprintf("<input name=\"%s\"", $field_name);

		foreach($htmlObjects as $key=>$val){
			
				$html.=sprintf(" %s=\"%s\"" , $key,$val);
		}

			$checked =  explode(",", $this->model->{$field_name});
			
			$selected = in_array($cbval, $checked)?"checked=\"checked\"":"";
			$html.= sprintf(" value=\"%s\" %s", $cbval,$selected);
		

		$html.=" />";

		return $html;


	}


	function textAreaFor($field_name, $htmlObjects = []){
		

		$html = sprintf("<textarea name=\"%s\"", $field_name);

		foreach($htmlObjects as $key=>$val){
			$html.=sprintf(" %s=\"%s\"", $key,$val);
		}




		$html.=">";

		if(isset($this->model->{$field_name})){
			$html.= $this->model->{$field_name};
		}
		$html.="</textarea>";


		return $html;


	}



	/**
	* Create a dropdownlist, supports associative, unassociative and a strong typed arrays for value 
	* 
	* @param String $field_name name of the field 
	* @param Array options  options for the select box
	* 
	* @todo Create documentation for the possible arrays for options 
	*/
	function dropDownListFor($field_name, $options = [], $label ='' , $htmlObjects = []){
		

		$html = sprintf("<select name=\"%s\"", $field_name);

		foreach($htmlObjects as $key=>$val){
			$html.=sprintf(" %s=\"%s\"", $key,$val);
		}



		$html.=">";

		$val = isset($this->model->{$field_name})?$this->model->{$field_name}:""; 

		if($label){
			$html.= sprintf("<option value=\"\">%s</option>",$label);
		}

		if(array_keys($options) !== range(0, count($options) - 1)){ // the array is associative 
			foreach($options as $key=>$value){

				$selected = $key == $val?"selected=\"selected\"":"";

				$html.= sprintf("<option value=\"%s\" %s>%s</option>",$key,$selected, $value);
			}
		}else if(is_array($options[0])){
			foreach($options[0] as $optionValue){
				$kfield = $options[1];
				$vfield = $options[2];

				$key = $optionValue->{$kfield};
				$value = $optionValue->{$vfield};
				
				$selected = $key == $val?"selected=\"selected\"":"";

				$html.= sprintf("<option value=\"%s\" %s>%s</option>",$key,$selected, $value);
			}

		}else{
			foreach($options as $value){
				$selected = $value == $val?"selected=\"selected\"":"";

				$html.= sprintf("<option value=\"%s\" %s>%s</option>",$value,$selected, $value);
			}
		}

		
		

		$html.="</select>";


		return $html;


	}



	 function editorFor($field_name, $options = [], $label ='' , $htmlObjects = []){

	 	if(!isset($this->model->fields[$field_name])){

	 		// no special setting for that field , return a text box setting :

	 		return $this->textBoxFor($field_name, $options = [], $label , $htmlObjects);

	 	}

	 	$fld=$this->model->fields[$field_name];

	 	if(!isset($fld['type']))
	 		$fld['type'] = 'text';

	 	switch($fld['type']){
	 		case 'textarea': 
	 			return $this->textAreaFor($field_name, $htmlObjects);
	 			break;
		}

		if(isset($fld['type'])){
			$htmlObjects['type'] = $fld['type'];
		}

		if($fld['required']){
			$htmlObjects['required'] = 'required';

		}

 		return $this->textBoxFor($field_name, $htmlObjects);

//		


		
	}




	/** 
	* Server side validation
	* @return object result of server side validation
	*			- $is_valid bool - true/false 
	*			- $errors array  - assoc array of errors
	*/
	public function isValid() {
		$obj = new \stdClass;

		$obj->is_valid = true; 
		$obj->errors   = [];

		foreach ($this->model->fields as $key=>$val) {
			$fld_value = $this->model->{$key}; 

			if(isset($val['required']) && $val['required'] && !$fld_value ){
				$obj->is_valid  = false;
				$obj->errors[]  = $key.' is required ' ; 

			}



			if(isset($val['unique']) && $val['unique']){

				$count = $this->model->getCount([
						'where' => [
							$key => $fld_value
						] 
					]);

				if($count){
					$obj->is_valid  = false;
					$obj->errors[]  = $fld_value.' is already taken' ; 
				}


				

			}
				
		}
		


		$this->valid_state = $obj ; 

		return $obj;

	}

	/**
	* Validate the form and save the object .
	*
	*  @return Object - returns the validation object, if isvalid == false it means that the submission failed.
	*/
	function submit() {
		$valid = $this->isValid() ;

		if($valid->is_valid){

			$this->model->save();
		}

		return $valid;
	} 

}
?>