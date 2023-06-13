</nav></header>
<section class="edit">
<?php
    if(sizeof($this->error_messages))
    {
        echo "<p class=\"error centre\">{$this->error_messages[0]}</p>\n";
    }
    elseif(sizeof($this->status_messages))
    {
        foreach($this->status_messages as $message)
        {
             echo "<p class=\"success centre\">$message</p>\n";
        }
    }
    elseif($label = $this->dbo->getNextMessage())
    {
        echo "<p>$label</p>\n";
    }
?>
<form>
<?php
    $post_count = 0;
    $export_list = "";
    $urls = [];
    while($row = $this->dbo->getNextResultsRow())
    {
        extract($row);
        $similarweb_time = 0;
        $similarweb_url = "";
        $time = time();
        if(strpos($description, "<?xml") !== false)
        {
            $xml=simplexml_load_string($description, "SimpleXMLElement", LIBXML_NOCDATA) or die("Error: Cannot create object");
            if(isset($xml->similarweb_time))
            {
                $similarweb_time = (int)$xml->similarweb_time;
            }
            if($time - $similarweb_time > 2419200)  //28 days
            {
                if(isset($xml->similarweb_url))
                {
                    $similarweb_url = $xml->similarweb_url;
                }
                else
                {
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
                    $similarweb_url = "https://www.similarweb.com/website/" . $domainstring . "/#overview";
                }
                $urls[] = $similarweb_url;
                $post_count++;
                if($post_count == 100) break;
            }
        }
    }
    if(sizeof($urls))
    {
        foreach($urls as $url)
        {
            $export_list .= $url . "\r\n";
        }
    }
    else
    {
        $export_list = "No posts needing Similarweb data found.";
    }
?>
<form name="export_form" method="post" action="?a=exportsimilarweb&subpage=tools">
These are the first 100 similarweb URLs to be scraped:<br />
<textarea name="export_list" rows="50" cols="112"><?=$export_list;?></textarea>
</form>
<br /><br /><?=$post_count;?> links exported</form>
</section>