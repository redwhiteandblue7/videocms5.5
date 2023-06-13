<?php

//the post class
//for interacting with posts data
    require_once(DB_PATH . 'posttools.db.class.php');
    require_once(OBJECTS_PATH . 'domain.class.php');

class Post
{
    private $dbo;
    private $row;
    private $results = [];
    private $result_pointer = 0;
    private $description;

    public $error_type = "";
    public $post_id = 0;

    public function __construct(int $id = 0)
    {
        $this->dbo = new PosttoolsDB();
        $this->dbo->setPrefix();

        if($id) {
            $this->row = $this->dbo->fetchPost($id);
            if($this->row) {
                $this->post_id = $this->row->post_id;
            }
        }
    }

    public function posts(int $start, int $limit, int $site_filter = 0, int $sponsor_filter = 0, $tag_filter = 0, string $sort_by = "") : int
    {
		$num_of_rows = $this->dbo->fetchPosts($start, $limit, $site_filter, $sponsor_filter, $tag_filter, $sort_by);
        $this->result_pointer = 0;
        $this->results = $this->dbo->results();

        return $num_of_rows;
    }

    public function next()
    {
        if($this->result_pointer < sizeof($this->results)) {
            $this->row = $this->results[$this->result_pointer++];
            $this->description = "";
            $this->post_id = $this->row->post_id;
            return $this->row;
        }

        return "";
    }

    //Get a filtered list of posts
    public function videoPosts(int $start, int $limit, string $sort_by = "", int $channel_id = 0, int $tag_id = 0, int $user_id = 0)
    {
		$this->dbo->post_id = $this->post_id;
        $num_of_rows = $this->dbo->fetchVideoPosts($start, $limit, $sort_by, "", $channel_id, $tag_id, $user_id);
        $this->result_pointer = 0;
        $this->results = $this->dbo->results();

        return $num_of_rows;
    }

    /** Get an array of post objects sorted by the most related to the current one by number of matching tags */
    public function relatedPosts(int $limit = 10) : int
    {
        $num_of_rows = 0;
        $list = $this->dbo->fetchRelatedPosts($this->row->post_id, $limit);
        if($list) {
            $this->result_pointer = 0;
            $num_of_rows = $this->dbo->fetchVideoPosts(0, $limit, "", $list);
            $this->results = $this->dbo->results();
        }
        return $num_of_rows;
    }

    /** Get an array of post objects sorted by the most related to the current one by number of matching tags */
    public function videoList(string $list, int $limit = 10) : int
    {
        $num_of_rows = 0;
        if($list) {
            $this->result_pointer = 0;
            $num_of_rows = $this->dbo->fetchVideoPosts(0, $limit, "", $list);
            $this->results = $this->dbo->results();
        }
        return $num_of_rows;
    }

    //Post search function
    public function searchPosts(string $search, int $limit = 10) : int
    {
        $num_of_rows = 0;
        if(trim($search)) {
            $list = $this->dbo->fetchSearchResults($search, $limit);
            if($list) {
                $this->result_pointer = 0;
                $num_of_rows = $this->dbo->fetchVideoPosts(0, $limit, "", $list);
                $this->results = $this->dbo->results();
            }
        }
        return $num_of_rows;
    }

    //Get the current set of posts
    public function getPosts() : array
    {
        return $this->results;
    }

    /** Get an array of channel objects with their video counts and the latest post relating to a video from each
     * Object will contain channelid and channelname, video_count and the post object row
     */
    public function channelsWithPosts()
    {
        return $this->dbo->fetchChannelsWithPosts();
    }

    public function getPostByPagename(string $pagename) : bool
    {
        if($this->row = $this->dbo->fetchPostByPagename($pagename)) {
            $this->post_id = $this->row->post_id;
            return true;
        }

        return false;
    }

    public function save(stdClass $vars) : bool
    {
        //post must have a title
        if(!isset($vars->title) || $vars->title == "") {
            $this->error_type = "no_title";
            return false;
        }

        //post must have a post type
        if(!isset($vars->post_type) || $vars->post_type == "") {
            $this->error_type = "no_post_type";
            return false;
        }

        //now check if we need to get the dims of the poster image
		if((($vars->orig_width ?? 0) == 0) && (($vars->orig_height ?? 0) == 0) && (($vars->orig_thumb ?? "") != "")) {
            $domain = new Domain();
            $img_path = $domain->urlToPath($vars->orig_thumb);
			$imginfo = getimagesize($img_path);
			if($imginfo !== false) {
				$vars->orig_width = $imginfo[0];
				$vars->orig_height = $imginfo[1];
			}
		}

        if(!isset($vars->priority)) {
            $vars->priority = 0;
        }
        if(!isset($vars->trade_id)) {
            $vars->trade_id = 0;
        }
        if(!isset($vars->ranking)) {
            $vars->ranking = 0;
        }
        if(!isset($vars->site_id)) {
            $vars->site_id = 0;
        }
        if(!isset($vars->channel_id)) {
            $vars->channel_id = 0;
        }

        $this->vars = $vars;
        $this->dbo->savePost($vars);
        return true;
    }

    public function delete() : bool
    {
        $post_id = $this->row->post_id;
		$this->dbo->deleteRow("post_tag_rel", "post_id", $post_id, true);
		$this->dbo->deleteRow("relatedpost_tag_rel", "post_id", $post_id, true);
		return $this->dbo->deleteRow("posts", "post_id", $post_id, true);
    }

    public function vars()
    {
        if(isset($this->row->post_id)) {
            return $this->row;
        }
        return "";
    }

    /** Get the post description as an object, creating it if it doesn't exist
     * @return stdClass
     */
    public function description() : stdClass
    {
        if(!$this->description) {
            $desc_obj = new stdClass();
            $description = $this->row->description ?? "";
            if(strpos($description, "<?xml") === false) {
                $description = nl2br(htmlspecialchars($description, ENT_QUOTES, "UTF-8"));
                if(strpos($description, "[") !== false) {
                    $description = $this->bbcode($description);
                }
                $desc_obj->post_texts[] = array("heading"=>"", "text" => $description);
            } else {
                $desc_obj->xml = simplexml_load_string($description);
                if($desc_obj->xml !== false) {
                    foreach($desc_obj->xml->fulltext as $ftext) {
                        $desc = nl2br(htmlspecialchars($ftext, ENT_QUOTES, "UTF-8"));
                        if(strpos($desc, "[") !== false) {
                            $desc = $this->bbcode($desc);
                        }
                        $heading = htmlspecialchars($ftext->attributes()->heading ?? "", ENT_QUOTES, "UTF-8");
                        $desc_obj->post_texts[] = array("heading"=>$heading, "text"=>$desc);
                    }
                }
            }
            $this->description = $desc_obj;
        }
        return $this->description;
    }

    /** Increment the view count in the post stats */
    public function viewed() : void
    {
        $this->dbo->updatePostStats($this->row->post_id);
    }

    //Get a list of sponsors
    public function getSponsors() : array
    {
        return $this->dbo->fetchTable("sponsors", "sponsor_name");
    }

    //Get a raw list of tags
    public function getTags() : array
    {
        return $this->dbo->fetchTable("tags", "tag_name", true);
    }

    //Get a raw list of sites without any filters
    public function getSites() : array
    {
        return $this->dbo->fetchTable("sites", "site_name", true);
    }

    /**
     * Get post tags for this post
     */
    public function tags() : array
    {
        return $this->dbo->fetchTags("post", $this->row->post_id);
    }

    /** Get related post tags for this post */
    public function relatedTags() : array
    {
        return $this->dbo->fetchTags("relatedpost", $this->row->post_id);
    }

    /** Get an array of values being the possible values from the post types enum column in the posts table
     * @return array of values
     */
    public function getPostTypes() : array
    {
        return $this->dbo->getEnumValues("posts", "post_type", true);
    }

    public function getLinkTypes() : array
    {
        return $this->dbo->getEnumValues("posts", "link_type", true);
    }

    public function getDisplayStates() : array
    {
        return $this->dbo->getEnumValues("posts", "display_state", true);
    }

    public function hide()
    {
        $this->dbo->updateColumn("posts", "display_state", "hide", "id", $this->row->id, true);
    }

    public function unhide()
    {
        $this->dbo->updateColumn("posts", "display_state", "display", "id", $this->row->id, true);
    }

	public function bbcode($string) : string
	{
        // All the default bbcode arrays.
		$bbcode = array(
                '#\[b\](.*?)\[/b\]#' => '<b>\\1</b>',
                '#\[i\](.*?)\[/i\]#' => '<i>\\1</i>',
                '#\[q\](.*?)\[/q\]#' => '<q>\\1</q>',
                '#\[h2\](.*?)\[/h2\]#' => '<h2>\\1</h2>',
                '#\[h3\](.*?)\[/h3\]#' => '<b>\\1</b>',
                '#\[h4\](.*?)\[/h4\]#' => '<b>\\1</b>',
                '#\[url=(.*?)\](.*?)\[/url]#' => '<a href="\\1" target="_blank">\\2</a>',
                '#\[img\](.*?)\[/img\]#' => '<img src="\\1" alt="" />'
		);
		$output = preg_replace(array_keys($bbcode), array_values($bbcode), $string);
		return $output;
	}

	public function bbcodeRemoval($string) : string
	{
        // All the default bbcode arrays.
		$bbcode = array(
                '#\[b\](.*?)\[/b\]#' => '\\1',
                '#\[i\](.*?)\[/i\]#' => '\\1',
                '#\[q\](.*?)\[/q\]#' => '\\1',
                '#\[h2\](.*?)\[/h2\]#' => '\\1',
                '#\[h3\](.*?)\[/h3\]#' => '\\1',
                '#\[h4\](.*?)\[/h4\]#' => '\\1',
                '#\[url=(.*?)\](.*?)\[/url]#' => '\\2',
                '#\[img\](.*?)\[/img\]#' => ''
		);
		$output = preg_replace(array_keys($bbcode), array_values($bbcode), $string);
		return $output;
	}

}

?>