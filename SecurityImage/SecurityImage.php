<?php
session_start();

class SecurityImage
{
	var	$bg,		//Source image
		$image,		//The image
		$font,		// Located in fonts folder.
		$fontsize,	
		$colour,	// Colour of text
		$strLength,	// Length of random Text
		$text = "",
		$num_dots,	// Num of noise dots to add
		$chars = array("a","A","b","B","C","d","D","e","E","f","F","g","G","h","H","J",
			   "K","L","m","M","n","N","P","Q","r","R","t","T","?","%","+","!","*","#","§",
			   "U","V","W","X","Y","Z","1","2","3","4","5","6","7","8","9");
	
		/**
		 * Constructor, setup initial values.
		 * @return SecurityImage
		 */
		function SecurityImage()
		{
			$this->num_dots = 200;
			$this->strLength = 5;
			$this->fontsize = 16;
			$bg = "empty.gif"; 
			
			$this->image = imagecreatefromgif($bg);
			$this->colour = ImageColorAllocate ($this->image, mt_rand(0,100), mt_rand(0,100), 0); // Black
			
			$this->show();	// Automatically show when object created.
			
			 //Set session varible so our form can compare the text to users input
			$_SESSION['SECURITY_CODE'] = $this->text;
		}
	/**
	 * Display the image
	 */
	function show()
	{
		Header ("Content-type: image/png"); 
		$s=$this->genString();

		for ($i = 0; $i < $this->strLength; $i++) 
		{
			$angle = mt_rand(-16,16);	// Give text a slight random angle.
			$this->selectFont(); 		// Decide which random Font to use.
			imagettftext($this->image, $this->fontsize+mt_rand(0,4), $angle, 2+$i*17, 20, $this->colour, $this->font,$s[$i] );
		}
		
		$this->addNoise();
		
		imagepng($this->image);
		imagedestroy($this->image);
	}
	
	/**
	 * Generate a random string for our image
	 * using the caracters in our array.
	 *
	 */
	function genString()
	{
		for ($i = 0; $i < $this->strLength; $i++) 
		{
   			$this->text .= $this->chars[mt_rand(0, count($this->chars) - 1)];
		}
		
		return $this->text;
	}
	
	/**
	 * Compares the given text to the one in the Security 
	 * Image.
	 * 
	 * @return true if the text matches.
	 */
	function isMatch($t)
	{
		if($t == $this->text) 
		{
			return true;
		}
		else return false;
	}
	
	/**
	 * function to add extra "noise"
	 * or random dots to the image
	 *
	 */
	function addNoise()
	{
		$width = imagesx($this->image);
		$height = imagesy($this->image);

		//random dots.
		for($i = 0; $i < $this->num_dots; $i++)
		{
			imagefilledellipse($this->image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $this->colour);
		}
	}
	
	/**
	 * Chose a random TTF Font to use.
	 *
	 */
	function selectFont()
	{
		$this->font = "ARIAL.TTF"; 
		//switch (mt_rand(1,2))
		//{
		//	case 1 : $this->font = "FELIXTI.TTF"; break;
		//	case 2 : $this->font = "RAVIE.TTF"; break;
		//}
	}
} 

/**
 * Create a new object when we include this file.
 */
$secim = new SecurityImage();
?>