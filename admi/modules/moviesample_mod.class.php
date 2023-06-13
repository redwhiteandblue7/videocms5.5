<?php
    require_once('modules/default_mod.class.php');

class MovieSampleFunctions extends ModuleFunctions
{
    /** Function to take the description passed in and make an xml string out of it with the fulltext and tags elements filled in
     * @param description - the description to process
     * @return string - the processed description 
     */
    public function processPostDescription(string $description) : string
    {
        $tags = $this->dbo->getTagsFromString($description);
        $tags = "#" . implode(" #", $tags);
        $description = trim($this->dbo->stripTagsFromString($description));
        $description = "<?xml version='1.0' encoding='UTF-8'?>\r\n<post>\r\n<fulltext><![CDATA[$description]]></fulltext>\r\n<snippet></snippet>\r\n<tags>$tags</tags>\r\n</post>";
        return $description;
    }

    /** Function to make a unique alt title out of the title or description of a post, used for the alt title of thumbnails etc 
     * @param title - the title of the post
     * @param description - the description of the post
     * @return string - the processed alt title
     */
    public function processAltText(string $title, string $description) : string
    {
        if($description) {
            $alt_text = $this->dbo->stripTagsFromString($description);
        } else {
            $alt_text = $title;
        }
        $alt_text = trim($alt_text);
        $chars = array("!", "?", ",", ".", "&", ":", ";", "'", "\"", "(", ")", "+", "=", "/", "\\", "[", "]", "{", "}", "<", ">", "|", "`", "~", "@", "#", "$", "%", "^", "*");
        $alt_text = str_replace($chars, "", $alt_text);
        $alt_text = str_replace("\r\n", " ", $alt_text);
        $alt_text = str_replace("\n", " ", $alt_text);
        $alt_text = str_replace("\r", " ", $alt_text);
        $alt_text = str_replace("  ", " ", $alt_text);
        $alt_text = $this->dbo->getFirstWords($alt_text, 8);
        return $alt_text;
    }
}

?>