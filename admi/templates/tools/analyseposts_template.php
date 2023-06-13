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
    while($row = $this->dbo->getNextResultsRow())
    {
        extract($row);
        $brand = "";
        $access = "";
        $type = "";
        $content = "";
        $content_quality = "";
        $subject = "";

        if(strpos($description, "<?xml") !== false)
        {
//            $b2 = strrpos($description, "<brand>");
//            if($b2 !== false)
//            {
////                echo "<br /><br />" . $title . "<br /><textarea rows=\"5\" cols=\"80\">$description</textarea>";
//                $b1 = strpos($description, "<brand>");
//                if($b1 != $b2)
//                {
//                    $description = substr($description, 0, $b2) . "\r\n</post>";
////                    echo "<br /><textarea rows=\"5\" cols=\"80\">$description</textarea>";
//                }
//            }

            $xml=simplexml_load_string($description, "SimpleXMLElement", LIBXML_NOCDATA) or die("Error: Cannot create object");
            if(!isset($xml->brand))
            {
                if(strpos($categories, "branded") === false)
                {
                    $brand = "<brand>N/A</brand>";
                }
                else
                {
                    $b = explode(" ", $title);
                    $brand = "<brand>" . $b[0] . "</brand>";
                }
            }
            if(!isset($xml->access))
            {
                if(strpos($categories, "paysite") !== false)
                {
                    if(strpos($categories, "free") !== false)
                    {
                        $access = "<access>Free with premium upgrade</access>";
                    }
                    else
                    {
                        $access = "<access>Paid membership</access>";
                    }
                }
                else
                {
                    $access = "<access>Free</access>";
                }
            }
            if(!isset($xml->type))
            {
                if(strpos($categories, "paysite") !== false)
                {
                    if(strpos($categories, "network") !== false)
                    {
                        $type = "<type>Network paysite</type>";
                    }
                    else
                    {
                        $type = "<type>Standalone paysite</type>";
                    }
                }
                elseif(strpos($categories, "tube") !== false)
                {
                    $type = "<type>Tube site</type>";
                }
                elseif(strpos($categories, "blog") !== false)
                {
                    $type = "<type>Blog</type>";
                }
                else
                {
                    $type = "<type>Other</type>";
                }
            }
            if(!isset($xml->content))
            {
                if(strpos($categories, "pictures") !== false)
                {
                    $content = "<content>Pictures</content>";
                }
                else
                {
                    $content = "<content>Videos</content>";
                }
            }
            if(!isset($xml->content_quality))
            {
                if(strpos($categories, "pictures") !== false)
                {
                    $content_quality = "<content_quality>N/A</content_quality>";
                }
                else
                {
                    $lc_description = strtolower($description);
                    $lc_alt_title = strtolower($alt_title);
                    $lc_title = strtolower($title);

                    if(strpos($lc_title, "4k") !== false)
                    {
                        $content_quality = "<content_quality>All videos in 4K</content_quality>";
                    }
                    elseif(strpos($title, "HD") !== false)
                    {
                        $content_quality = "<content_quality>All videos in HD</content_quality>";
                    }
                    elseif((strpos($lc_alt_title, "4k") !== false) || (strpos($lc_description, "4k") !== false))
                    {
                        $content_quality = "<content_quality>4K or HD</content_quality>";
                    }
                    else
                    {
                        $content_quality = "<content_quality>HD or SD</content_quality>";
                    }
                }
            }
            if(!isset($xml->subject))
            {
                $pre = "";
                $pre2 = "";
                $act = "";
                $subject = "";
                if(strpos($categories, "general"))
                {
                    $subject = "<subject>Covers all porn categories</subject>";
                }
                else
                {
                    $pre_arr = array("japanese" => "Japanese ",
                        "indian"=>"Indian ",
                        "british"=>"British ",
                        "latina"=>"Latin ",
                        "webcams"=>"Live ",
                        "asian"=>"Asian ",
                        "european"=>"European ",
                    );
                    foreach($pre_arr as $key=>$value)
                    {
                        if(strpos($categories, $key) !== false)
                        {
                            $pre = $value;
                            break;
                        }
                    }
                    $pre2_arr = array(
                        "milf"=>"MILFs ",
                        "oldyoung"=>"old men with young women ",
                        "teen"=>"teens ",
                        "babes"=>"babes ",
                        "black"=>"ebony girls ",
                        "granny"=>"grannies ",
                        "gay"=>"gay guys ",
                        "trans"=>"transgenders ",
                        "gf"=>"ex girlfriends ",
                        "amateur"=>"amateurs ",
                        "pornstars"=>"pornstars "
                    );
                    foreach($pre2_arr as $key=>$value)
                    {
                        if(strpos($categories, $key) !== false)
                        {
                            $pre2 = $value;
                            break;
                        }
                    }
                    $acts_arr = array(
                        "anal"=>"anal sex",
                        "gangbang"=>"gangbangs",
                        "facial"=>"facials porn",
                        "group"=>"group sex",
                        "creampie"=>"creampies porn",
                        "interracial"=>"interracial sex",
                        "bigcock"=>"big cocks porn",
                        "tits"=>"big tits porn",
                        "handjobs"=>"handjobs porn",
                        "blowjobs"=>"blowjobs porn",
                        "flashing"=>"public flashing and nudity",
                        "public"=>"public sex and nudity",
                        "bisexual"=>"bisexual porn",
                        "lesbian"=>"girl on girl sex",
                        "cuckold"=>"cuckolding porn",
                        "taboo"=>"taboo (fauxcest, step family) porn",
                        "reality"=>"reality porn",
                        "fetish"=>"fetish sex",
                        "fisting"=>"fisting action",
                        "ladyboy"=>"ladyboy sex",
                        "hardcore"=>"hardcore porn",
                    );
                    foreach($acts_arr as $key=>$value)
                    {
                        if(strpos($categories, $key) !== false)
                        {
                            $act = $value;
                            break;
                        }
                    }
                    if($pre)
                    {
                        if($pre2)
                        {
                            $subject = $pre . $pre2;
                        }
                        else
                        {
                            $subject = $pre . "girls ";
                        }
                    }
                    elseif($pre2)
                    {
                        $subject = $pre2;
                    }
                    if($subject)
                    {
                        if($act)
                        {
                            $subject .= "in " . $act;
                        }
                        else
                        {
                            $subject .= "porn";
                        }
                    }
                    else
                    {
                        if($act)
                        {
                            $subject = "Various girls in " . $act;
                        }
                        else
                        {
                            $subject = "Other porn niche";
                        }
                    }
                    $subject = "<subject>" . ucfirst($subject) . "</subject>";
                }
            }

            $string = $brand . $access . $type . $content . $content_quality . $subject;
            if($string)
            {
                $string .= "\r\n</post>";
                $description = str_replace("\r\n</post>", "</post>", $description);
                $description = str_replace("</post>", $string, $description);
                $description = addslashes($description);
                $this->dbo->updateTableColumn("post_descriptions", "description", $description, "post_id", $post_id, true);
                $post_count++;
            }
        }
    }
?>
<br /><br /><?=$post_count;?> posts updated</form>
</section>