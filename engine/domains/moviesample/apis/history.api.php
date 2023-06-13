<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "post.class.php");

class HistoryApi extends DefaultApi
{
    protected $return_text = "Error getting history";

    public function process()
    {
        if(isset($_POST["post_ids"])) {
            $post_obj = json_decode($_POST["post_ids"], false);
            //we have a post object with the post_id as key and the timestamp as value
            //let's sort them by timestamp. We need to make the object into an array first
            $post_obj = (array)$post_obj;
            arsort($post_obj);
            //now let's sort them into five lists of post_ids: today, yesterday, this week, this month, older than this month
            $today = [];
            $yesterday = [];
            $this_week = [];
            $this_month = [];
            $older_than_this_month = [];
            //for simplicity let's base the times on GMT
            $now = time();
            $today_start = mktime(0, 0, 0, date("m", $now), date("d", $now), date("Y", $now));
            $yesterday_start = $today_start - 86400;
            $this_week_start = $today_start - (86400 * date("w", $now));
            $this_month_start = $today_start - (86400 * date("j", $now));
            foreach($post_obj as $post_id => $timestamp) {
                if($timestamp >= $today_start) {
                    $today[] = $post_id;
                } elseif($timestamp >= $yesterday_start) {
                    $yesterday[] = $post_id;
                } elseif($timestamp >= $this_week_start) {
                    $this_week[] = $post_id;
                } elseif($timestamp >= $this_month_start) {
                    $this_month[] = $post_id;
                } else {
                    $older_than_this_month[] = $post_id;
                }
            }

            //now let's get the posts for each list
            $this->post = new Post();
            $today_list = implode(",", $today);
            $this->num_of_videos = $this->post->videoList($today_list, 9999);
            if($this->num_of_videos) {
                echo "<div class=\"wide left pad\"><h2>Today</h2></div>";
                include(INCLUDE_PATH . "domains/moviesample/templates/thmb_content_tpl.php");
            }

            $yesterday_list = implode(",", $yesterday);
            $this->num_of_videos = $this->post->videoList($yesterday_list, 9999);
            if($this->num_of_videos) {
                echo "<div class=\"wide left pad\"><h2>Yesterday</h2></div>";
                include(INCLUDE_PATH . "domains/moviesample/templates/thmb_content_tpl.php");
            }

            $this_week_list = implode(",", $this_week);
            $this->num_of_videos = $this->post->videoList($this_week_list, 9999);
            if($this->num_of_videos) {
                echo "<div class=\"wide left pad\"><h2>This week</h2></div>";
                include(INCLUDE_PATH . "domains/moviesample/templates/thmb_content_tpl.php");
            }

            $this_month_list = implode(",", $this_month);
            $this->num_of_videos = $this->post->videoList($this_month_list, 9999);
            if($this->num_of_videos) {
                echo "<div class=\"wide left pad\"><h2>This month</h2></div>";
                include(INCLUDE_PATH . "domains/moviesample/templates/thmb_content_tpl.php");
            }

            $older_than_this_month_list = implode(",", $older_than_this_month);
            $this->num_of_videos = $this->post->videoList($older_than_this_month_list, 9999);
            if($this->num_of_videos) {
                echo "<div class=\"wide left pad\"><h2>Before this month</h2></div>";
                include(INCLUDE_PATH . "domains/moviesample/templates/thmb_content_tpl.php");
            }
            //we have to exit here to prevent the render() method from being called which expects a template or a string in $this->return_text
            exit();

        }
    }
}
?>