<?php

class LimitedCurrencyField extends CurrencyField{
	
	private $upperlimit = null;
	private $lowerlimit = null;
	
	function setUpperLimit($limit){
		$this->upperlimit = $this->convertTo($limit);
	}
	
	function setLowerLimit($limit = 0){
		$this->lowerlimit = $this->convertTo($limit);
	}
	
	function validate($validator) {
		if(!parent::validate($validator)) return false;
		
		if($this->dataValue() > $this->convertFrom($this->upperlimit)) {
			$validator->validationError($this->name, "Value must be below $this->upperlimit", "validation", false);
			return false;
		}
		
		if($this->dataValue() < $this->convertFrom($this->lowerlimit)) {
			$validator->validationError($this->name, "Value must be above $this->lowerlimit", "validation", false);
			return false;
		}
		
		return true;
	}
	
	private function convertFrom($value){
		if($value){
			return preg_replace('/[^0-9.]/',"", $value);
		}else{
			return 0.00;
		}
	}
	
	private function convertTo($value){
		return '$' . number_format(ereg_replace('[^0-9.]',"",$value), 2);
	}
	
	public function LabelExtra(){
		if($this->upperlimit && $this->lowerlimit)
			return "between $this->lowerlimit and $this->upperlimit";
		if($this->lowerlimit)
			return "above $this->lowerlimit";
		if($this->upperlimit)
			return "below $this->upperlimit";
		return null;
	}
}

?>
