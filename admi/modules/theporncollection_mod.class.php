<?php

require_once('modules/default_mod.class.php');

class TheporncollectionFunctions extends ModuleFunctions
{
    //function to insert a new post derived from only the site title, categories and site url of a remote site
    public function createPostFromHTML($title, $site_url, $categories)
    {
        $alt_title = $title;
        $snippet = "";

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

        if($html)
        {
            $dom = new DomDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            $dom->preserveWhiteSpace = false;
            $titletag = $dom->getElementsByTagName("title");
            $snippet = $titletag[0]->nodeValue;
        }

        //now we have everything we need to make the post

        $text = "$title: ";

        if($snippet)
        {
            $text .= "title ok, ";
        }
        else
        {
            $text .= "no title, ";
        }

        if($icon_url)
            $text .= "icon ok, ";
        else
            $text .= "no icon, ";
        $text .= "final url: $site_url";

        if($html)
        {
            $post_type = "blog";
            $description = "<?xml version='1.0' encoding='UTF-8'?>\r\n<post>\r\n<fulltext>We will have a review of this soon, please check back later!</fulltext>\r\n<snippet><![CDATA[$snippet]]></snippet>";
            if(strpos($categories, "paysite") !== false)
            {
                $post_type = "reviews";
                $description .= "\r\n<total></total><quality></quality><value></value>";
            }
            $description .= "\r\n</post>";

            $time_visible = time();
            $link_type = "dofollow";
            $display_state = "display";
            $post_id = 0;
            $site_id = $this->dbo->getValueFromTableRowById("site_names", "site_name", "'$title'", "site_id", true);

            $vars = compact("post_id", "title", "description", "site_url", "site_id", "alt_title", "icon_url", "categories", "post_type", "link_type", "display_state", "time_visible");
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

}

?>