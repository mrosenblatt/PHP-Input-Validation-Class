<?php

// Input Validation Class
// Version 1.00.00
// Property of Fliptel LLC. Copyright 2011. 
// Written and maintained by Matthew Rosenblatt

class Validate{
	
	private $vArray;
	
	private $input;
	private $required;
	private $missing;
	
	function setInput($input){
		$this->input = $input;
	}
	
	function setRequired($field,$error,$checks=null){
		$count = count($this->required);
		if($count == 0){
			$count = 0;
		} else {
			$count++;
		}
		$this->required[$count]['field'] = $field;
		$this->required[$count]['error'] = $error;
		
		if($checks != null){
			$this->required[$count]['checks'] = $checks;
		}
		
	}
	
	function verifyRequired(){
		
		$missing = array();
		$matches = array();
		
		$count = 0;
		
		foreach($this->required as $required){
			
			if(array_key_exists($required['field'],$this->input)){
				
				if(empty($this->input[$required['field']])){
					$missing[$count]['field'] = $required['field'];
					$missing[$count]['error'] = $required['error'];					  
				} else {
					$matches[$count]['field'] = $required['field'];
				}
				
			} else {
				
				$missing[$count]['field'] = $required['field'];
				$missing[$count]['error'] = $required['error'];
				
			}
			
			$count++;
			
		}
		
		if(count($missing) > 0){
			
			// Well, there's missing input. We'll stop here for now.
			$status['status'] = false;
			$status['errors'] = $missing;
		
		} else {
			
			// Everything is okay so far. Let's loop through the inputs 
			// again and see if any of them have a third field set for 
			// additional checks.
			
			// Create an array just for errors from hereon out.
			//$errors = array();
			
			$count = 0; // Just in case. I always forget this shit.
			
			foreach($this->required as $key){
								
				if(isset($key['checks'])){
					
					// There are more checks to perform! Let's do them!
					// Check to see if it's an array. If it is, then it 
					// has multiple checks to be performed.
					
					if(is_array($key['checks'])){
						
						// There is more than one check to perform on this input.
						
						$sCount = 0;
						
						foreach($key['checks'] as $check){
														
							if(strpos($check,'|') !== false){
								list($method,$option) = explode('|',$check);
							} else {
								$method = $check;
								$option = null;
							}
							
							if(method_exists('Validate',$method)){
								
								$result = $this->$method($key['field'],$option);
																
								if($result['status'] == false){
									
									// Add an entry into Errors.
									$ec = count($errors[$count])+1;	
									$errors[$key['field']][$sCount]['field'] = $key['field'];
									$errors[$key['field']][$sCount]['error'] = $result['error'];
									
								}
								
							} else {
								
								$ec = count($errors[$count])+1;	
								$errors[$key['field']][$sCount]['field'] = $key['field'];
								$errors[$key['field']][$sCount]['error'] = "FATAL ERROR: FUNCTION DOESN'T EXIST WITHIN VALIDATION CLASS ({$check})!";
								
							}
							
							$sCount++;
								
						}
						
						
					} else {
						
						// There is only one check to perform.
						
						// Check to see that the function noted even exists.
						// If not, just ignore it and don't bother with errors.
						
						$function = $key['checks'];
						
						
						if(strpos($function,'|') !== false){
							list($method,$option) = explode('|',$function);
						} else {
							$method = $function;
							$option = null;
						}
						
						if(method_exists('Validate',$method)){
													
							$result = $this->$method($key['field'],$option);
							
							if($result['status'] == false){
								
								$ec = count($errors[$count])+1;								
								// Add an entry into Errors.
								$errors[$key['field']][$ec]['field'] = $key['field'];
								$errors[$key['field']][$ec]['error'] = $result['error'];
								
							}
							
						} else {
							
								$ec = count($errors[$count])+1;								
								// Add an entry into Errors.
								$errors[$key['field']][$ec]['field'] = $key['field'];
								$errors[$key['field']][$ec]['error'] = "FATAL ERROR: FUNCTION DOESN'T EXIST WITHIN VALIDATION CLASS ({$check})!";
							
						}
						
					}
					$count++;
				}
				
			}
			
			if(count($errors) > 0){
				
				$status['status'] = false;
				$status['errors'] = $errors;
				
			} else {
				
				$status['status'] = true;
				
			}
			
		}
		
		return $status;
		
	}
	
	function checkEmail($field){
		
		$validated['status'] = false;
		$validated['error'] = 'Email Address provided ('.$this->input[$field].') is not valid.';
		
		if (filter_var($this->input[$field],FILTER_VALIDATE_EMAIL) !== false) {
			$validated['status'] = true;
		}
		
		return $validated;
		
	}
	
	function checkIPAddress($field){
		
		$validated['status'] = false;
		$validated['error'] = 'IP Address provided is not valid.';
		
		if (preg_match( "/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $this->input[$field])){
    	   $validated['status'] = true;
	    }
		
		return $validated;
		
	}
	
	function minLength($field,$option){
		
		$validated['status'] = false;
		
		if(strlen($this->input[$field]) >= $option){
			
			$validated['status'] = true;	
		} else {
			$validated['error'] = "Must be at least {$option} characters long.";
		}
		
		return $validated;
		
	}
	
	function maxLength($field,$option){
		
		$validated['status'] = false;
		
		if(strlen($this->input[$field]) <= $option){
			$validated['status'] = true;	
		} else {
			$validated['error'] = "Must not be more than {$option} characters long.";
		}
		
		return $validated;
		
	}
	
}

?>