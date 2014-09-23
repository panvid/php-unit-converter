<?php
 
// loading predined values in $unit, if needed
include("enum.php");
 
/***********************
 * Convert.class.php
 ***********************
 *
 * This class calculate the perfect representation of an value with unit.
 * If you set "0.0005 meter" you will get "0.5 millimeter" as result.
 *
 * @author David Pauli
 *
 * How to use the class
 *
 *   create object with:
 *
 *	Convert( number, base, steps, <digit>, <min>, <max> )
 *
 *	   float number	-	the value you want to convert
 *	   int base	-	the exponent of the value. E.g. you use kilometer your base is 3 (for kilo)
 *	   array steps	-	array of allowed numbers to convert. You can use enum if you bind it (e.g. $unit["LENGTH"])
 *	   float digit	-	<optional> You can set parameter if you want to aim some prefered number of digits before and after
 *				   the dot. If you type 4.3 the algorithm will prefer numbers with 4 digits before the dot and 3 digits
 *				   after the dot, e.g. 1234.56
 *	   float min	-	<optional> Set the value which it should not undercut (disregarded the base).
 *	   float max	-	<optional> Set the value which it should not overcall (disregarded the base).
 ***********************/
$convertion = new Convert(1, 9, $unit["LENGTH"]);	// create object
$result = $convertion->getResult(0);			// get best (0th) result
$convertion->debug();					// show debug-information (only for development)
 
class Convert {
 
	private $number		= 0.0;				// number with zero base (number * 10^base)
	private $steps		= array();			// filter in witch base to convert number
	private $min		= NULL;				// minimun number to convert (disregarded base)
	private $max		= NULL;				// maximum number to convert (disregarded base)
	private $prevDigit	= NULL;				// aimed digits before dot
	private $pastDigit	= NULL;				// aimed digits after dot
 
	private $result		= array();			// space to presave calculated number with base with ...
	private $rank		= array();			// ... its rank
 
	private $factor		= array();			// returnable number ...
	private $base		= array();			// ... and base
 
	// Constructor
	function __construct($number, $base, $steps, $digit = NULL, $min = NULL, $max = NULL) {
 
		$this->number	= $number * pow(10, $base);
		$this->steps	= $steps;
		$this->min	= $min;
		$this->max	= $max;
 
		if($digit != NULL) {
			$digit			= explode(".", (string) $digit, 2);
			$this->prevDigit	= $digit[0];
			$this->pastDigit	= $digit[1];
		}
		else $this->prevDigit = $this->pastDigit = NULL;
 
		$this->calculate();
		$this->rank();
		$this->prepare();
 
	}
 
	// calculate the possible values
	private function calculate() {
 
		// calculate all possiblities and rank them
		foreach($this->steps as $step=>$base) {
 
			// delete value if not in range $min and $max
			if($this->min !== NULL) {
				if(($this->number / $base) < $this->min) continue;
			}
			if($this->max !== NULL) {
				if(($this->number / $base) > $this->max) continue;
			}
 
			// filter values bigger than 10^14 or 10^-13), PHP only save 14 digits without rounding (64Bit IEEE)
			if($this->number/$base >= pow(10,14) || $this->number/$base <= pow(10,-15)) continue;
 
			$this->result[(string) $base] = (float) $this->number / $base;
		}
 
	}
 
	// rank every value
	private function rank() {
 
		foreach($this->result as $unity=>$number) {
 
			$base = $this->getBase($number);
 
			// initialize rank
			$rank = 0;
 
			// get array before and after dot
			$explode	= explode(".", number_format($number,14), 2);
			$prevNumber	= $explode[0];
			$pastNumber	= $this->deleteZeros($explode[1], "last");
 
			// get length and significant numbers of before and after dot
			$prevCount	= strlen($prevNumber);
			$pastCount	= strlen($pastNumber);
			$prevSigCount	= strlen($this->deleteZeros($prevNumber, "last"));
			$pastSigCount	= strlen($this->deleteZeros($pastNumber, "first"));
 
			// calculation
			$rank = $rank - abs($pastCount - $prevCount);
			if($pastCount > $prevCount) $rank++;
 
			if($prevSigCount == 1) $rank = $rank + 4;
			if($prevSigCount == 2) $rank = $rank + 2;
			if($prevSigCount == 3) $rank++;
 
			if($prevNumber != 0) $rank = $rank + 2;
 
			if($pastSigCount == 0) $rank = $rank + 2;
			if($pastSigCount == 1) $rank = $rank + 2;
			if($pastSigCount == 2) $rank = $rank + 3;
			if($pastSigCount == 3) $rank = $rank + 3;
			if($pastSigCount == 4) $rank++;
 
			if($base == 0) $rank = $rank+3;
			if($base % 3 == 0) $rank = $rank + 2;
			if($this->prevDigit !== NULL && $this->pastDigit !== NULL) {
				$rank = $rank - abs($pastSigCount - $this->pastDigit) - abs($prevSigCount - $this->prevDigit);
			}
 
			$this->rank[(string) $unity] = $rank;
 
		}
 
		arsort($this->rank, SORT_NUMERIC);
 
	}
 
	// make last save operations
	private function prepare() {
 
		foreach($this->rank as $base => $rank) {
 
			array_push($this->factor, $this->result[$base]);
			array_push($this->base, $base);
		}
 
	}
 
	/*
	   transform dezimal factor into his base-expression.
	   E.g. the decimal 0.001 is the base -3.
	*/
	private function getBase($number) {
 
		$base = (string) ($this->number / $number);
		$explode = explode(".", $base, 2);
 
		if(!isset($explode[1]))	$base = strlen($base)-1;
		else			$base = 0-strlen($explode[1]);
 
		return (int) $base;
 
	}
 
	/*
	   Operation to delete beginning or ending 0's to get significant digits of a number
	   E.g. deleteZeros( 1200200, last) transform to 12002
	*/
	private function deleteZeros($number, $where) {
 
		$return = "";
		$array = str_split($number);
 
		if($where == "first") $array = array_reverse($array);
 
		foreach($array as $key=>$value) {
 
			$return = (string) $return . $value;
			if($value!=0) break;
		}
 
		if($where == "first") $return = implode('', array_reverse(str_split($return)));
 
		return $return == 0 ? NULL : $return;
	}
 
	// function to show debug information
	public function debug() {
 
		// DEBUG
		echo "<h3>Value:</h3><pre>";
		print_r($this->result);
		echo "</pre><br/><h3>Ranking:</h3><pre>";
		print_r($this->rank);
		echo "</pre><hr/>";
	}
 
	/*
	   gets the wished calculated result. The best result is called 0, and so on.
	*/
	public function getResult($id) {
 
		if(!isset($this->factor[$id])) return NULL;
 
		$return[$this->base[$id]] = $this->factor[$id];
		return $return;
	}
 
}
 
?>
