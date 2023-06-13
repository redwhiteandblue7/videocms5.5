<?php

require_once('modules/default_mod.class.php');

class PornsiteslinksFunctions extends ModuleFunctions
{
    //function to return the description for a post derived from the scraped HTML of a remote site or the site title
    public function createPostFromHTML($title, $site_url, $categories)
    {

        $description = "";
        $alt_title = "";

        $html = $this->curlWebPage($site_url);

        $icon_url = $this->getIconFromHTML($html, $title, $site_url);
        if(substr($icon_url, 0, 5) == "Error")
        {
            $icon_url = "";
        }

        if($html)
        {
            $dom = new DomDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            $dom->preserveWhiteSpace = false;
            $titletag = $dom->getElementsByTagName("title");
            $alt_title = $titletag[0]->nodeValue;
            $metas = $dom->getElementsByTagName("meta");
            foreach ($metas as $meta)
            {
                if (strtolower($meta->getAttribute('name')) == 'description')
                {
                    $description = $meta->getAttribute('content');
                }
            }
        }

        //now we have everything we need to make the post

        $text = "$title: ";

        if($this->redirectedURL)
        {
            $s = explode("?", $this->redirectedURL);
            $site_url = $s[0];
        }

        if($alt_title)
        {
            $text .= "title ok, ";
        }
        else
        {
            $text .= "no title, ";
            $alt_title = $title;
        }

        if($description)
        {
            $text .= "description ok, ";
        }
        else
        {
            $d = array("The full review of ## is on its way real soon.",
                    "We will have the review of ## up any day now.",
                    "This site hasn't been reviewed yet. We're working on it right now.",
                    "## hasn't been reviewed yet but we're sorting that out real soon.",
                    "We are working flat out to get this website reviewed, check back soon.",
                    "We are working on the review for ##, please bear with us.",
                    "The full review of ## is coming real soon, a bit like your mom.",
                    "We will have the review of ## ready just as soon as we can pull our dicks out of your mom's ass.",
                    "There is nothing to read here yet. Rest assured we'll have that fixed as soon as.",
                    "Oops, looks like we haven't reviewed ## yet. We'll get that fixed as soon as we can."
                    );
            $description = str_replace("##", $title, $d[rand(0, sizeof($d) - 1)]);
            $text .= "no description, ";
        }
        if($icon_url)
            $text .= "icon ok, ";
        else
            $text .= "no icon, ";
        $text .= "final url: $site_url";

        if($html)
        {
            $time_visible = time();
            $post_type = "blog";
            $link_type = "dofollow";
            $display_state = "display";
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

						if($Country2 == "")
						{
							$Country2 = $Country3;
							$Country3 = "";
						}
						if($Competitor2 == "")
						{
							$Competitor2 = $Competitor3;
							$Competitor3 = "";
						}
						$data_line = array("GlobalRank"=>$GlobalRank, "TotalTraffic"=>$TotalTraffic, "Country1"=>$Country1, "Country2"=>$Country2, "Country3"=>$Country3, "Competitor1"=>$Competitor1, "Competitor2"=>$Competitor2, "Competitor3"=>$Competitor3,);
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
            unset($Competitor1);
            unset($Competitor2);
            unset($Competitor3);
            unset($Country1);
            unset($Country2);
            unset($Country3);
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
					if($Country1) $description = $this->insertOrUpdateXMLTag($description, "similarweb_cntry1", $Country1);
					if($Country2) $description = $this->insertOrUpdateXMLTag($description, "similarweb_cntry2", $Country2);
					if($Country3) $description = $this->insertOrUpdateXMLTag($description, "similarweb_cntry3", $Country3);
					if($Competitor1) $description = $this->insertOrUpdateXMLTag($description, "similarweb_cmp1", $Competitor1);
					if($Competitor2) $description = $this->insertOrUpdateXMLTag($description, "similarweb_cmp2", $Competitor2);
					if($Competitor3) $description = $this->insertOrUpdateXMLTag($description, "similarweb_cmp3", $Competitor3);
					$description = addslashes($description);
					$this->dbo->updateTableColumn("post_descriptions", "description", $description, "post_id", $post_id, true);
					$result_count++;
				}
			}
		}
		return "Found $lines_count lines of data, $result_count matching posts";

    }

    public function getReviewData($site_url, $post_id, $pagename)
    {
        $review_url = str_replace("##", $pagename, $site_url);
        $html = $this->curlWebPage($review_url);

        $review_text = "";
        $tag_prefix = "";

        if($this->httpCode == 404)
        {
            $retry_pagename = str_replace("-", "", $pagename);
            if($retry_pagename != $pagename)
            {
                $review_url = str_replace("##", $retry_pagename, $site_url);
                $html = $this->curlWebPage($review_url);
            }
        }

        if($html)
        {
            if(strpos($review_url, "rabbits") !== false)
            {
                $tag_prefix = "rr_";
                if(($s = strpos($html, "<td>Rabbit Score</td>")) !== false)
                {
                    $s += 21;
                    $t = strpos($html, ">", $s) + 1;
                    $e = strpos($html, "<", $t);
                    $review_text = substr($html, $t, $e - $t);
                    $review_score = str_replace("%", "", $review_text);
                    if(!is_numeric($review_score))
                    {
                        $review_text = "";
                    }
                }
            }
            elseif(strpos($review_url, "thebestporn") !== false)
            {
                $tag_prefix = "tbp_";
                if(($s = strpos($html, "Score_Box_TBP")) !== false)
                {
                    $s += 13;
                    $t = strpos($html, ">", $s) + 1;
                    $t = strpos($html, ">", $t) + 1;
                    $e = strpos($html, "<", $t);
                    $review_text = substr($html, $t, $e - $t);
                    $review_score = str_replace("%", "", $review_text);
                    if(is_numeric($review_score))
                    {
                        if(strpos($review_text, "%") === false) $review_text .= "%";
                    }
                    else
                    {
                        $review_text = "";
                    }
                }
            }
            elseif(strpos($review_url, "adultreviews") !== false)
            {
                $tag_prefix = "ar_";
                if(($s = strpos($html, ">Score ")) !== false)
                {
                    $s += 7;
                    $e = strpos($html, "<", $s);
                    $review_text = substr($html, $s, $e - $s);
                    $review_score = str_replace("%", "", $review_text);
                    if(is_numeric($review_score))
                    {
                        if(strpos($review_text, "%") === false) $review_text .= "%";
                    }
                    else
                    {
                        $review_text = "";
                    }
                }
            }
            elseif(strpos($review_url, "x3guide") !== false)
            {
                $tag_prefix = "x3_";
                if(($s = strpos($html, ">Final Score: <")) !== false)
                {
                    $s += 15;
                    $t = strpos($html, ">", $s) + 1;
                    $t = strpos($html, ">", $t) + 1;
                    $e = strpos($html, "<", $t);
                    $review_text = substr($html, $t, $e - $t);
                    $review_score = str_replace("%", "", $review_text);
                    if(is_numeric($review_score))
                    {
                        if(strpos($review_text, "%") === false) $review_text .= "%";
                    }
                    else
                    {
                        $review_text = "";
                    }
                }
            }
            elseif(strpos($review_url, "honestpornreviews") !== false)
            {
                $tag_prefix = "hpr_";
                if(($s = strpos($html, "Score<")) !== false)
                {
                    $s += 6;
                    $t = strpos($html, "header3", $s) + 9;
                    $e = strpos($html, "<", $t);
                    $review_text = trim(substr($html, $t, $e - $t));
                    $v = explode("/", $review_text);
                    $review_score = trim($v[0]);
                    if(is_numeric($review_score))
                    {
                        $review_score *= 10;
                        $review_text = trim($v[0]) . " / " . trim($v[1]);
                    }
                    else
                    {
                        $review_text = "";
                    }
                }
            }
        }

        if($this->redirectedURL) $review_url = $this->redirectedURL;
        if($this->httpCode == 404)
        {
            return "No review found at $review_url";
        }
        elseif($this->httpCode > 300)
        {
            return "Error: HTTP " . $this->httpCode;
        }
        elseif($this->curlErr)
        {
            return "Error: " . $this->curlErr;
        }

        $description = $this->dbo->getValueFromTableRowById("post_descriptions", "post_id", $post_id, "description", true);
        $description = $this->insertOrUpdateXMLTag($description, $tag_prefix . "reviewurl",  "<![CDATA[$review_url]]>");
        if($review_text)
        {
            $description = $this->insertOrUpdateXMLTag($description, $tag_prefix . "reviewtxt",  $review_text);
            if($review_score)
            {
                $description = $this->insertOrUpdateXMLTag($description, $tag_prefix . "reviewval",  $review_score);
            }
        }
        $description = addslashes($description);
        $this->dbo->updateTableColumn("post_descriptions", "description", $description, "post_id", $post_id, true);
        if($review_text)
            return "Found $review_text ($review_score%) at $review_url";
        else
            return "No score data found at $review_url";

    }
}

?>