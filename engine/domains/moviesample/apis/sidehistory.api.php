<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "post.class.php");

class SideHistoryApi extends DefaultApi
{
    protected $return_text = "Error getting history";
    protected $num_of_videos = 0;

    public function process()
    {
        if(isset($_POST["post_ids"])) {
            $post_obj = json_decode($_POST["post_ids"], false);
            //we have a post object with the post_id as key and the timestamp as value
            //let's sort them by timestamp. We need to make the object into an array first
            $post_obj = (array)$post_obj;
            arsort($post_obj);

            //now we build a comma separated list of post_ids
            $video_list = implode(",", array_keys($post_obj));

            //now let's get the posts for each list
            $this->post = new Post();
            $this->num_of_videos = $this->post->videoList($video_list, 10);
            $this->template = "sidehistory";
        }
    }
}
?>