<?php
/*
All functions to do with fetching assets or scraping data from remote sites go here.
CMS sites that require special case functionality must extend this class and override the function as required.
*/

class ModuleFunctions
{
    public $dbo;
    public $redirectedURL;
    public $curlErr;
    public $httpCode;
    public $error_messages = [];

    //get the raw html from a remote site
    protected function curlWebPage($site_url)
    {
        $ch = curl_init();
        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $site_url);
        // Removes the headers from the output
        curl_setopt($ch, CURLOPT_HEADER, false);
        // Return the output instead of displaying it directly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // set a useragent to try and not get blocked
        //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; DuckDuckGo-Favicons-Bot/1.0; +http://duckduckgo.com)');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:107.0) Gecko/20100101 Firefox/107.0');
        curl_setopt($ch, CURLOPT_REFERER, '');
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
//            'Accept-Language: en-US,en;q=0.5',
//            'Accept-Encoding: gzip, deflate, br'
//            ));

        // Execute the curl session
        $html = curl_exec($ch);
        // Get the final URL
        $this->redirectedURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $this->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->curlErr = curl_error($ch);
        // Close the curl session
        curl_close($ch);

        return $html;
    }

    //try to get the favicon first looking for a link rel tag and if none is found then default to looking for the .ico file in the root folder
    protected function getIconFromHTML($html, $title, $site_url)
    {
        $ext = ".ico";
		$filename = $this->dbo->getTagFromTitle($title);
        $u = parse_url($site_url);
        $scheme = $u["scheme"];
        $base_url = $scheme . "://" . $u["host"];
		$favicon = $base_url . "/favicon.ico";

        if($html)
        {
            $dom = new DomDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            $dom->preserveWhiteSpace = false;
            $links = $dom->getElementsByTagName("link");
            $href = "";
            foreach ($links as $link)
            {
                if (strtolower($link->getAttribute('rel')) == 'shortcut icon')
                {
                    $href = $link->getAttribute('href');
                    break;
                }
                if (strtolower($link->getAttribute('rel')) == 'icon')
                {
                    $href = $link->getAttribute('href');
                    $sizes = $link->getAttribute('sizes');
                    if(($sizes == "16x16") || ($sizes == "any")) break;
                }
            }
            if($href)
            {
                $href = trim($href);
                if(substr($href, 0, 4) != "http")
                {
                    if(substr($href, 0, 1) != "/") $href = "/" . $href;
                    if(substr($href, 0, 2) == "//")
                        $href = $scheme . ":" . $href;
                    else
                        $href = $base_url . $href;
                }
                $favicon = $href;
            }
        }

        $dt = strrpos($favicon, ".");
        $e = substr($favicon, $dt);
        $e = explode("?", $e);
        $ext = $e[0];
//		$icon_url = ICON_FOLDER . $filename . $ext;
        $icon_url = $this->dbo->domain_obj->icon_folder . $filename . $ext;
		$filepath = $this->dbo->domain_vars->public_path . $icon_url;
		$icon_url = "/" . $icon_url;

		$fp = fopen($filepath, "w");
		$ch = curl_init($favicon);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; DuckDuckGo-Favicons-Bot/1.0; +http://duckduckgo.com)');
		//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:107.0) Gecko/20100101 Firefox/107.0');
		curl_setopt($ch, CURLOPT_REFERER, $site_url);
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
		curl_exec($ch);

        $err = curl_error($ch);
		curl_close($ch);
		fclose($fp);
        if($err)
        {
            if($href)
                $err = "Error: tried to get file at $href but got error $err";
            else
                $err = "Error: could not find any favicon for $site_url";
        }
        elseif(filesize($filepath) == 0)
        {
            $err = "Error: got a 0 length file";
        }
        elseif(!getimagesize($filepath))
        {
            $err = "Error: got a file that was not an image";
        }

        if($err)
        {
			unlink($filepath);
            return $err;
        }

        return $icon_url;
    }

    // Clean up input field data
    protected function cleanText($text)
    {
        do
        {
            $ws = $text;
            $text = str_replace("  ", " ", $ws);
        } while($text != $ws);
        $text = trim($text);

        $text = str_replace("\n", "", $text);
        $text = str_replace("\r", "", $text);
        $text = str_replace("\t", "", $text);
        return $text;
    }

    //function to insert a new post derived from only the site title, categories and site url of a remote site
    public function createPostFromHTML($title, $site_url, $categories)
    {
        $description = "";
        $alt_title = $title;
        $icon_url = "";

        //in the default case this is just to get the final redirected url if any
        $html = $this->curlWebPage($site_url);

        //now we have everything we need to make the post

        $text = "$title: ";

        if($this->redirectedURL)
        {
            $s = explode("?", $this->redirectedURL);
            $site_url = $s[0];
        }

        $text .= "final url: $site_url";

        if($html)
        {
            $time_visible = time();
            $post_type = "blog";
            $link_type = "dofollow";
            $display_state = "hide";
            $post_id = 0;

            $vars = compact("post_id", "title", "description", "site_url", "alt_title", "icon_url", "categories", "post_type", "link_type", "display_state", "time_visible");
            $post_result = $this->dbo->insertPost($vars);
        }

        //now just report back on what was done
        if($post_result)
        {
            $text = $title . ": " . $post_result;
        }
        elseif(!$html)
        {
            $text = $title . ": Could not get HTML at $site_url, received {$this->curlErr}";
        }

        return $text;
    }

    public function getFavicon($title, $site_url, $post_id)
    {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $site_url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// set a useragent to try and not get blocked
		//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:107.0) Gecko/20100101 Firefox/107.0');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; DuckDuckGo-Favicons-Bot/1.0; +http://duckduckgo.com)');
		curl_setopt($ch, CURLOPT_REFERER, '');
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
        // Execute the curl session
		$html = curl_exec($ch);
		curl_close($ch);

        $result = $this->getIconFromHTML($html, $title, $site_url);
        if(substr($result, 0, 5) != "Error")
        {
            $this->dbo->updateTableColumn("posts", "icon_url", $result, "post_id", $post_id, true);
        }
        return $result;
    }

    public function getReviewData($site_url, $post_id, $pagename)
    {
        return "Error: No default functionality - this function must be overridden";
    }

    // Insert or replace xml tags in a piece of text
    public function insertOrUpdateXMLTag($text, $tag, $value)
    {
        $tagname = "<$tag>";
        $closetag = "</$tag>";
        $tag = $tagname . $value . $closetag;
        if(($s = strpos($text, $tagname)) !== false)
        {
            $result = $text;
            if(($e = strpos($text, $closetag)) !== false)
            {
                $e += strlen($closetag);
                $result = substr($text, 0, $s) . $tag . substr($text, $e);
            }
        }
        else
        {
            $tag .= "\r\n</post>";
            $result = str_replace("\r\n</post>", "</post>", $text);
            $result = str_replace("</post>", $tag, $result);
        }
        return $result;
    }

    public function processSimilarwebData($data_dump)
    {
		$lines = explode("\n", $data_dump);
		$results = [];
        $format = "";
        $formats = [];

		foreach($lines as $line)
		{
			$line = $this->cleanText(str_replace("\t", "|", $line));
			if($line)
			{
				if($format)
				{
					$parts = explode("|", $line);
					if(sizeof($parts) != sizeof($formats))
					{
						$this->error_messages[] = "Mismatched fields between the line data and format string: " . $line;
						break;
					}
					$domain = $parts[0];
					if($domain)
					{
						//now create the vars for GlobalTraffic, Country1, Country2, Competitor1, etc
						for($j = 0; $j < sizeof($parts); $j++)
						{
							$parts[$j] = $parts[$j];
							$label = $formats[$j];
							$$label = $parts[$j];
						}
						$GlobalRank = str_replace(",", "", $GlobalRank);
						$GlobalRank = str_replace("#", "", $GlobalRank);
						if($TotalTraffic == "< 5K")
						{
							$TotalTraffic = "0";
						}
						else
						{
							$suffix = substr($TotalTraffic, -1);
							$val = floatval(substr($TotalTraffic, 0, -1));
							if($suffix == "K")
							{
								$val *= 1000;
							}
							elseif($suffix == "M")
							{
								$val *= 1000000;
							}
							elseif($suffix == "B")
							{
								$val *= 1000000000;
							}
							$TotalTraffic = (int)$val;
						}

						$data_line = array("GlobalRank"=>$GlobalRank, "TotalTraffic"=>$TotalTraffic);
						$results[$domain] = $data_line;
					}
				}
				else
				{
					$format = $line;
					$formats = explode("|", $format);
				}
			}
		}

		$lines_count = sizeof($results);
		$result_count = 0;

		$this->dbo->fetchPosts("full", "sortByID", 0, 99999);
		while($result = $this->dbo->getNextResultsRow())
		{
			extract($result);
			$d = parse_url($site_url);
			$domainstring = $d["host"];
			if(substr($domainstring, 0, 4) == "the.") $domainstring = substr($domainstring, 4);
			if(substr($domainstring, 0, 6) == "tour1.") $domainstring = substr($domainstring, 6);
			if(substr($domainstring, 0, 6) == "tour2.") $domainstring = substr($domainstring, 6);
			if(substr($domainstring, 0, 6) == "tour3.") $domainstring = substr($domainstring, 6);
			if(substr($domainstring, 0, 6) == "tour4.") $domainstring = substr($domainstring, 6);
			if(substr($domainstring, 0, 6) == "tour5.") $domainstring = substr($domainstring, 6);
			if(substr($domainstring, 0, 8) == "landing.") $domainstring = substr($domainstring, 8);
			if(substr($domainstring, 0, 4) == "www.") $domainstring = substr($domainstring, 4);
            unset($GlobalRank);
            unset($TotalTraffic);
			if(array_key_exists($domainstring, $results))
			{
				if(strpos($description, "<?xml") === false)
				{
					$this->error_messages[] = "Description for $domainstring does not contain xml";
				}
				else
				{
					extract($results[$domainstring]);
					$similarweb_url = "<![CDATA[https://www.similarweb.com/website/" . $domainstring . "/#overview]]>";
					$description = $this->insertOrUpdateXMLTag($description, "similarweb_url", $similarweb_url);
					$description = $this->insertOrUpdateXMLTag($description, "similarweb_time", time());
					if($GlobalRank) $description = $this->insertOrUpdateXMLTag($description, "similarweb_rank", $GlobalRank);
					if($TotalTraffic) $description = $this->insertOrUpdateXMLTag($description, "similarweb_traffic", $TotalTraffic);
					$description = addslashes($description);
					$this->dbo->updateTableColumn("post_descriptions", "description", $description, "post_id", $post_id, true);
					$result_count++;
				}
			}
		}
		return "Found $lines_count lines of data, $result_count matching posts";
    }

    public function rerankPosts()
    {
		$this->dbo->fetchPosts("full", "sortByPriority", 0, 99999);

		$post_array = [];
		while($row = $this->dbo->getNextResultsRow())
		{
			extract($row);
			$traffic = 0;
			if(strpos($description, "<?xml") !== false)
			{
				$xml=simplexml_load_string($description, "SimpleXMLElement", LIBXML_NOCDATA) or die("Error: Cannot create object");
				if(isset($xml->similarweb_traffic))
				{
					$traffic = (int)$xml->similarweb_traffic;
				}
			}
			$post_array[$post_id] = $traffic;
		}

		asort($post_array);
		$post_count = sizeof($post_array);
		$rank = 1;
		$granularity = (int)(65535 / $post_count);
		foreach($post_array as $post_id=>$traffic)
		{
			$position = $rank;
			if($traffic == 0) $position = 0;
			$this->dbo->updateTableColumn("posts", "rank", $position, "post_id", $post_id, true);
			$rank += $granularity;
		}

        return $post_count;
    }

    //Default behavior is to return the description unchanged
    public function processPostDescription(string $description) : string
    {
        return $description;
    }

    //Default behavior is to return the alt text unchanged
    public function processAltText(string $title, string $description) : string
    {
        return $title;
    }

}

?>