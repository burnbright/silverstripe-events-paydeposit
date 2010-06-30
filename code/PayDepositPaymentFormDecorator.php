<?php

class PayDepositPaymentFormDecorator extends DataObjectDecorator{
	
	function extraStatics(){
		return array(
			'db' => array(
				'AllowPaymentModification' => 'Boolean',
				'LowerLimitValue' => 'Currency',
				'LowerLimitPercent' => 'Percentage',
				'PaymentAmountLabel' => 'Varchar(255)'
			)
		);
	}
	
	function updateCMSFields(&$fields){
		
		$fields->addFieldToTab('Root.Content.BookingOptions',new CheckboxField('AllowPaymentModification','Allow payments to be modified'));
		
		if($this->owner->AllowPaymentModification){
			$fields->addFieldsToTab('Root.Content.BookingOptions',
				array(
					new NumericField('LowerLimitPercent','Minimimum percentage of total that can be paid'),
					new CurrencyField('LowerLimitValue','Lowest amount that can be paid (will override percent, if present)'),
					new TextField('PaymentAmountLabel','Label for field')
				)
			);			
		}
		
	}
	
	function updatePaymentFields(&$fields){
		
		if($this->owner->AllowPaymentModification && $afield = $fields->fieldByName('Amount')){
			$value = $afield->Value();
			$fields->replaceField('Amount',$lcf = new LimitedCurrencyField('Amount','Amount',$value));
			
			$datavalue = $lcf->dataValue();
			$lcf->setUpperLimit($datavalue); //can't pay more
			
			if($this->owner->LowerLimitPercent > 0)	$lcf->setLowerLimit(ceil((double)$datavalue * (double)$this->owner->LowerLimitPercent));
			
			if($this->owner->LowerLimitValue > 0) $lcf->setLowerLimit($this->owner->LowerLimitValue);
			
			$lcf->setTitle($lcf->Title()." (deposit can be any amount ".$lcf->LabelExtra().")");
			if($this->owner->PaymentAmountLabel) $lcf->setTitle($this->owner->PaymentAmountLabel);
		}
	}
	
	function onBeforePayment(&$registration,&$payment,&$data,&$form){
		
		if($afield = $form->Fields()->fieldByName('Amount')){
			$payment->Amount = $afield->dataValue();
			$payment->write();		
		}
	}
	
}

?>
