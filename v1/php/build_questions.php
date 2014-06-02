<?php
/*
	Class to print out, in HTML, form fields matching the questions in the database.

	Takes as a parameter a JSON string of the questions.
	
*/

class BuildQuestions {

	public $inputJSON;		// JSON containing all the custom questions we want to build into form fields
	public $outputHTML;		// The string to display in the browser containing all the form code
	public $inputArr;		// Associative Array version of the input JSON


	// Constructor
	function BuildQuestions($s) {

		$this->inputJSON = $s;
		$this->inputArr = array();
		$this->convertJSONtoArr();
		$this->setNameifBlank();
	
		$this->makeHTML();
		//return $this->outputHTML;
	}
	
	// Takes the JSON we got as input and converts it to a PHP-friendly Associate Array
	private function convertJSONtoArr() {
		$this->inputArr = (json_decode($this->inputJSON, true));
	}

	// When the inputJSON doesn't have a Name key, we give it a generic name
	private function setNameifBlank() {
		if (!isset ($this->inputArr['name'])) {
			$this->inputArr['name'] = "generic".time();
		}
	}	
	
	// Function prints out the HTML
	public function makeHTML() {
		
		$output = "\n\t\t";
		
		// Top portion of HTML form
		$this->makeHTMLtop($output);
	
	
		foreach ($this->inputArr['questions'] as $key => $value) {

			$output.= "\n\t\t".'<li id="li_'.$value['id'].'" >';
			$output.= "\n\t\t".'<label class="description" for="'.$value['id'].'">'.$value['text'].'</label>';
			$output.= "\n\t\t".'<div>';
			
			// We will build the various input widgets based on question type
			switch($value['type']) {
			
				// Text string
				case 1:
					$output.= "\n\t\t".'<input id="'.$value['id'].'" value="'.$value['default'].'" data-init-text="'.$value['text'].'" name="'.$value['id'].'" class="element text" type="text" />';
					break;
				// Yes or No selection	
				case 2:
					$output.= "\n\t\t".'<input id="'.$value['id'].'_yes" name="'.$value['id'].'" class="element radio" type="radio" value="1" checked="checked"/>';
					$output.= "\n\t\t".'<label class="choice" for="'.$value['id'].'_yes">Yes</label>';
					$output.= "\n\t\t".'<input id="'.$value['id'].'_no" name="'.$value['id'].'" class="element radio" type="radio" value="0" />';
					$output.= "\n\t\t".'<label class="choice" for="'.$value['id'].'_no">No</label>';
					break;
				// Select Box
				case 3:
					$output.= "\n\t\t".'<select class="element select medium" id="'.$value['id'].'" name="'.$value['id'].'">';
					// Build the select options from a comma-separated string in the 'options' field					
					$arr = explode(',', $value['options']);
					foreach ($arr as $key2 => $value2) {
						$output.= "\n\t\t".'<option value="'.trim($value2).'" >'.trim($value2).'</option>';
					}
					$output.= "\n\t\t".'</select>';
					break;	
			}
			
			$output.= "\n\t\t\t".'</div>';
			$output.= "\n\t\t\t".'<p class="guidelines" id="guideline_'.$value['id'].'"><small>'.$value['guidelines'].'</small></p>';
			$output.= "\n\t\t\t".'</li>';

			//echo $output;
			//$this->outputHTML .= $output;
			//echo $this->outputHTML;
		} // END foreach loop 
		
		// Bottom portion of form
		$this->makeHTMLbottom($output);	
		
		// Throw some script up in this bitch
		$this->makeSubmitScript($output);	
		
		$this->outputHTML .= $output;
		// *****
		// FYI, we don't need to echo outputHTML both here and in the API call
		// It should be in the API call, but the problem is it's working here and not there...
		//echo $this->outputHTML;

	} // END function makeHTML()
	
	// Function generates the top portion of the HTML form
	// We pass in the output variable by reference
	public function makeHTMLtop(&$output) {
		$output.= "\n\t\t\t".'<!-- Start the PHP-generated form for '.$this->inputArr['name'].' //-->';	
		$output.= "\n\t\t\t".'<form id="form_'.$this->inputArr['name'].'" class="appnitro" method="post" action="">';
		$output.= "\n\t\t\t".'<div class="form_description">';
		$output.= "\n\t\t\t".'<h2>Your Custom Questions</h2>';
		$output.= "\n\t\t\t".'<p>These are the question you are asking your customers.</p>';
		$output.= "\n\t\t\t".'</div>';
		$output.= "\n\t\t\t".'<ul>';
		
	}
	
	// Function generates the bottom portion of the HTML form
	// Output variable is passed by reference, otherwise the changes stay within each function
	public function makeHTMLbottom(&$output) {
		$output.= "\n\t\t\t".'<li class="buttons">';
		$output.= "\n\t\t\t".'<input id="submit_'.$this->inputArr['name'].'" class="button_text" type="image" src="./img/right_arrow.jpg" type="submit" name="submit" value="Submit" />';
		$output.= "\n\t\t\t".'</li>';
		$output.= "\n\t\t\t".'</ul>';
		$output.= "\n\t\t\t".'</form>';
		$output.= "\n\t\t\t".'<!-- End the PHP-generated form for '.$this->inputArr['name'].' //-->';			
	}
	
	public function makeSubmitScript(&$output) {
	
			$output.= "\n\t\t\t".'<script>';
	    	$output.= "\n\t\t\t".'$(\'#form_'.$this->inputArr['name'].'\').submit(function() {';
					$output.= "\n\t\t\t".'var fd = JSON.stringify($(\'#form_'.$this->inputArr['name'].'\').serializeObject())';
					$output.= "\n\t\t\t".'$.post(\'./php/api.php?method=createCustomer&schema=customer\', fd, function(data) {';
						$output.= "\n\t\t\t".'$(\'#result\').text(data);';
						$output.= "\n\t\t\t".'customerPointer = data.trim();';
					$output.= "\n\t\t\t".'});';
        	$output.= "\n\t\t\t".'return false;';
        	$output.= "\n\t\t\t".'}';
    	$output.= "\n\t\t\t".');';
		$output.= "\n\t\t\t".'</script>';
	}
	
	
	// Simply echos the JSON that this class received as input
	public function echoInputJSON() {
		echo $this->inputJSON;
	}

	// Echos the key:value pairs of an Associative Array, including if one of those values
	// is itself an Associative Array
	// Parses through the converted JSON Object
	public function descArray($arr) {
		foreach ($arr as $key => $value) {
			echo($key) . ': '; // key
			echo($value); // value
			echo("<br />");
			if(is_array($value)) {
				//echo "we got an array on our hands.";
				$this->descArray($value);  // recursion
			}
		}
	}


 } // END class
?>