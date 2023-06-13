<?php
trait TextFuncs
{
	public function ordinal($number, $format = false)
	{
		$ends = array('th','st','nd','rd','th','th','th','th','th','th');
		if ((($number % 100) >= 11) && (($number%100) <= 13))
			$ending = 'th';
		else
			$ending = $ends[$number % 10];

		if($format) $number = number_format(floatval($number));
		return $number . $ending;
	}

	public function getTagFromTitle($title)
	{
		$pagename = strtolower($title);
		$ps = array(" ", "!", "?", ",", ".", "&", ":", ";", "'", "\"");
		$pr = array("-", "");
		$pagename = str_replace($ps, $pr, $pagename);
		return $pagename;
	}

	/** Given a time in seconds, return a string representing an approximate time span taking into account singular and plural
	 * @param time
	 * @return string
	 */
	public function getTimeSpan($time)
	{
		$span = "";
		if($time < 60)
		{
			$span = $time . " second";
			if($time != 1) $span .= "s";
		}
		else if($time < 3600)
		{
			$span = floor($time / 60) . " minute";
			if(floor($time / 60) != 1) $span .= "s";
		}
		else if($time < 86400)
		{
			$span = floor($time / 3600) . " hour";
			if(floor($time / 3600) != 1) $span .= "s";
		}
		else if($time < 604800)
		{
			$span = floor($time / 86400) . " day";
			if(floor($time / 86400) != 1) $span .= "s";
		}
		else if($time < 2592000)
		{
			$span = floor($time / 604800) . " week";
			if(floor($time / 604800) != 1) $span .= "s";
		}
		else if($time < 31536000)
		{
			$span = floor($time / 2592000) . " month";
			if(floor($time / 2592000) != 1) $span .= "s";
		}
		else
		{
			$span = floor($time / 31536000) . " year";
			if(floor($time / 31536000) != 1) $span .= "s";
		}
		return $span;
	}

	/** Function to find all the hashtags in a string and return them as an array
	 * 
	 * @param text - a string with hashtags defined by #tagname
	 * @return array 
	 */
	public function getTagsFromString(string $text) : array
	{
		$tags = [];
		$pattern = "/#([a-zA-Z0-9]+)/";
		preg_match_all($pattern, $text, $matches);
		if(isset($matches[1])) {
			foreach($matches[1] as $tag) {
				$tags[] = strtolower($tag);
			}
		}
		return $tags;
	}

	/** Function to strip hashtags from a string
	 * @param text - a string with hashtags defined by #tagname
	 * @return string - the string with the hashtags removed
	 */
	public function stripTagsFromString(string $text) : string
	{
		$pattern = "/#([a-zA-Z0-9]+)/";
		$text = preg_replace($pattern, "", $text);
		return $text;
	}

	/** Get the first n words of a string */
	public function getFirstWords(string $text, int $num)
	{
		$words = explode(" ", $text);
		$alt = "";
		for($i = 0; $i < $num; $i++) {
			if(isset($words[$i])) {
				$alt .= $words[$i] . " ";
			}
		}
		return trim($alt);
	}

	/** Function to strip non alpha numeric characters from a string */
	public function stripNonAlphaNumeric(string $string) : string
	{
		return preg_replace("/[^a-zA-Z0-9]/", "", $string);
	}
}