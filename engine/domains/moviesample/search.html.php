<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");

class SearchPage extends MovieSamplePage
{
    protected $template = "results";
    protected $label = "Search results for ";

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base . "search.html";
    }

    public function process()
    {
        $this->post = new Post();
        //get the requested uri
        $req = $_SERVER['REQUEST_URI'];
        //get the query string part of it if there is one
        $query = "";
        $params = [];
        if(strpos($req, "?") !== false) {
            $query = substr($req, strpos($req, "?") + 1);
        }
        if($query) {
            //parse the query string
            parse_str($query, $params);
            //if there is a q parameter, use it as the search term
            if(isset($params["q"])) {
                $search = $params["q"];
                //we need to clean up the search term to avoid XSS attacks
                $search = strip_tags($search);
                //remove leading and trailing spaces
                $search = trim($search);
                //remove all non-alphanumeric characters except the apostrophe
                $search = preg_replace("/[^a-zA-Z0-9' ]/", "", $search);
                // now let's split it into an array of words
                $this->label .= "\"$search\"";
                $search_array = explode(" ", $search);
                //if it's up to 3 words we will try adding or removing plural and remove spaces
                $search_orig = $search;
                $search = str_replace(" and ", " ", $search);
                if(count($search_array) < 4) {
                    $search_tag = str_replace(" ", "", $search_orig);
                    $search_tag = str_replace("'", "", $search_tag);
                    if(substr($search_tag, -2) == "es") {
                        $search_tag2 = substr($search_tag, 0, -2);
                    } elseif(substr($search_tag, -1) == "s") {
                        $search_tag2 = substr($search_tag, 0, -1);
                    } else {
                        $search_tag2 = $search_tag . "s";
                    }
                    if(count($search_array) == 1)
                        $search .= " " . $search_tag2;
                    else
                        $search .= " " . $search_tag . " " . $search_tag2;
                }
                $this->num_of_videos = $this->post->searchPosts($search, 40);
            }
        }

        $this->show_trending = 10;
        $this->show_history = true;
        $this->show_tags = 30;
    }
}
?>
