<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(INCLUDE_PATH . "traits/text.trait.php");
    require_once(OBJECTS_PATH . "post.class.php");
    require_once(OBJECTS_PATH . "video.class.php");

class VideoPostSaveApi extends DefaultApi
{
    use TextFuncs;
    protected $return_text = "Error|video_post";

    public function process()
    {
        if(!$this->user->logged_in) {
            $this->return_text = "Error|not_logged_in";
            return;
        }

        $videoObj = json_decode($_POST["x"], false);

        if(!isset($videoObj->token) || $videoObj->token != $this->session_token) {
            $this->return_text = "Error|token";
            return;
        }

        if(!isset($videoObj->postID)) {
            $this->return_text = "Error|post_id";
            return;
        }
        if(!isset($videoObj->videoID)) {
            $this->return_text = "Error|video_id";
            return;
        }

        $video = new Video($videoObj->videoID);
        if(!$video->video_id) {
            $this->return_text = "Error|no_video";
            return false;
        }
        if($video->vars()->user_id != $this->user->user_id) {
            $this->return_text = "Error|invalid_user";
            return;
        }

        $post = new Post($videoObj->postID);
        if($videoObj->postID != 0) {
            if(!$post->post_id) {
                $this->return_text = "Error|no_post";
                return false;
            }
            $vars = $post->vars();
            if($vars->user_id != $this->user->user_id) {
                $this->return_text = "Error|invalid_user";
                return false;
            }
        } else {
            $vars = new stdClass();
            $vars->id = 0;
            $vars->video_id = $video->vars()->video_id;
            $vars->post_id = 0;
            $vars->user_id = $video->vars()->user_id;
            $vars->site_id = 0;
            $vars->display_state = "display";
            $vars->link_type = "dofollow";
            $vars->time_added = time();
            $vars->priority = 1;
            $vars->ranking = 1;
            $vars->thumb_url = "/" . $video->vars()->url_thumbnail;
            $vars->video_url = "/" . $video->getHighestResolution();
            $vars->duration = $video->vars()->duration;
            $vars->orig_thumb = "/" . $video->vars()->url_poster;
            $vars->orig_width = $video->vars()->orig_width;
            $vars->orig_height = $video->vars()->orig_height;
            $vars->post_type = "video";
        }

        $vars->title = $videoObj->title;
        $vars->alt_title = $this->processAltText($videoObj->title, $videoObj->description);
        $vars->description = $this->processPostDescription($videoObj->description, $videoObj->hashtags);
        $post->save($vars);
        if($post->error_type) {
            $this->return_text = "Error|$post->error_type";
            return false;
        }

        // Post was saved, so update the video record
        $video->vars()->channel_id = $videoObj->channelID;
        $video->vars()->process_state = "posted";
        $video->save();
        $this->return_text = "OK";
        return false;
    }

    /** Function to take the description passed in and make an xml string out of it with the fulltext and tags elements filled in
     * @param description - the description to process
     * @return string - the processed description 
     */
    public function processPostDescription(string $description, string $tags) : string
    {
        return "<?xml version='1.0' encoding='UTF-8'?>\r\n<post>\r\n<fulltext><![CDATA[$description]]></fulltext>\r\n<snippet></snippet>\r\n<tags>$tags</tags>\r\n</post>";
    }

    /** Function to make a unique alt title out of the title or description of a post, used for the alt title of thumbnails etc 
     * @param title - the title of the post
     * @param description - the description of the post
     * @return string - the processed alt title
     */
    public function processAltText(string $title, string $description) : string
    {
        if($description) {
            $alt_text = $this->stripTagsFromString($description);
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
        $alt_text = $this->getFirstWords($alt_text, 8);
        return $alt_text;
    }
}
?>