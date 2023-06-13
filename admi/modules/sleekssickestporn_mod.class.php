<?php

require_once('modules/default_mod.class.php');

class SleekssickestpornFunctions extends ModuleFunctions
{
    //function to return the description for a post derived from the scraped HTML of a remote site or the site title
    public function createPostFromHTML($title, $site_url, $categories)
    {

        $description = "";
        $alt_title = $title;

        //this is just to get the final redirected url if any
        $html = $this->curlWebPage($site_url);

        if($this->redirectedURL)
        {
            $s = explode("?", $this->redirectedURL);
            $site_url = $s[0];
        }

        $icon_url = $this->getIconFromHTML($html, $title, $site_url);
        if(substr($icon_url, 0, 5) == "Error")
        {
            $icon_url = "";
        }

        //now we have everything we need to make the post

        $text = "$title: ";

        $d = array("No review yet. Check back soon.",
                "We will get around to reviewing ## soon.",
                "Review coming soon.",
                "New addition! Review coming soon.",
                "New addition! Come back soon for a full review.",
                "We haven't reviewed this one yet, please check back later."
                );
        $description = str_replace("##", $title, $d[rand(0, sizeof($d) - 1)]);

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
					if(sizeof($parts) == sizeof($formats))
                    {
                        $domain = $parts[0];
                        if($domain)
                        {
                            //now create the vars for GlobalTraffic, etc
                            for($j = 0; $j < sizeof($parts); $j++)
                            {
                                $parts[$j] = $parts[$j];
                                $label = $formats[$j];
                                $$label = $parts[$j];
                            }
                            $GlobalRank = str_replace(",", "", $GlobalRank);
                            $GlobalRank = str_replace("#", "", $GlobalRank);
                            $AdultRank = str_replace(",", "", $AdultRank);
                            $AdultRank = str_replace("#", "", $AdultRank);
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

                            $data_line = array("GlobalRank"=>$GlobalRank, "TotalTraffic"=>$TotalTraffic, "AdultRank"=>$AdultRank, "RankCountry"=>$RankCountry);
                            $results[$domain] = $data_line;
                        }
                    }
                    else
					{
						$this->error_messages[] = "Mismatched fields between the line data and format string: " . $line;
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
            unset($AdultRank);
            unset($RankCountry);
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
					if($AdultRank) $description = $this->insertOrUpdateXMLTag($description, "similarweb_adultrank", $AdultRank);
					if($RankCountry) $description = $this->insertOrUpdateXMLTag($description, "similarweb_rankcountry", $RankCountry);
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
        $priority = 0;
		$rank = $post_count;
		foreach($post_array as $post_id=>$traffic)
		{
			$position = $rank;
			if($traffic == 0) $position = 0;
			$this->dbo->updateTableColumn("posts", "rank", $position, "post_id", $post_id, true);
			$this->dbo->updateTableColumn("posts", "priority", $priority, "post_id", $post_id, true);
			$rank--;
            $priority++;
		}

        return $post_count;
    }
}