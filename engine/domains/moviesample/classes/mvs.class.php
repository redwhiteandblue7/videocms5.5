<?php
    require_once(INCLUDE_PATH . "classes/userpage.class.php");
    require_once(OBJECTS_PATH . "tag.class.php");

class MovieSamplePage extends UserPage
{
    public $tags;
	protected $post_id = 0;
    protected $show_tags = 0;
    protected $show_trending = 0;
    protected $show_history = false;
    protected $show_related = 0;
    protected $account_template = "";
    protected $num_of_videos = 0;
    protected $num_of_channels = 0;
    protected $num_of_posts = 0;
    protected $videos = [];
    protected $channels = [];
    protected $post;
    protected $video;
    protected $channel;

    public function __construct()
    {
        parent::__construct();
        $this->tags = new Tag();
    }

    public function init()
    {
        parent::init();
		$this->session_token = $this->user->setSessionToken();
    }

    public function process()
    {
        return;
    }

    public function render()
    {
        $this->pageload_stat_id = $this->getPageloadStat();
        ob_start();
        require_once(INCLUDE_PATH . "domains/moviesample/templates/" . $this->template . "_template.php");
        ob_end_flush();
    }

    /** Shorten the text to the given number of characters */
    public function shorten(string $text, int $chars, int $extra = 0) : string
    {
        $text = strip_tags($text);
		$words = explode(" ", $text);
		$sentence = "";
		foreach($words as $word) {
			$word = trim($word);
			if(!$word) continue;
			if($sentence) $word = " " . $word;
			if((strlen($sentence . $word) + $extra) > $chars) {
                $sentence .= "...";
				break;
			}
			$sentence .= $word;
		}
        return $sentence;
    }
}
?>