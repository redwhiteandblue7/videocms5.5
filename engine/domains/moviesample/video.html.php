<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");
    require_once(OBJECTS_PATH . "video.class.php");

class VideoPage extends MovieSamplePage
{
	protected $template = "error";

	public function process()
	{
		$this->post = new Post();
		$pagename = $this->uri[1];

		//we need to check that the post exists and that the pagename is valid
		//now see if there is a post with this pagename
		if(!$this->post->getPostByPagename($pagename)) {
			return;
		}

		//now check that the post is a video post
		if($this->post->vars()->post_type != "video") {
			return;
		}

		//now check that the post is active
		if($this->post->vars()->display_state == "delete") {
			$this->template = "obsolete";
			return;
		}


		$this->video = new Video($this->post->vars()->video_id);
		//if the video the post is linked to doesn't exist, then we can't display the post
		if(!$this->video->video_id) {
			return;
		}

		$this->post_id = $this->post->post_id;

		$this->canonical_url = $this->canonical_base . "video/" . $this->post->vars()->pagename;
		//only now can we switch from the error template to the post template
		$this->template = "video";
		//if the post is hidden, then we need to switch to the hidden template
		if($this->post->vars()->display_state == "hide") {
			$this->template = "hidden";
		}
		//now we can set up the vars for the page
		$description = new stdClass();
		$description->title = htmlspecialchars($this->post->vars()->title) . " - MovieSample.net";
		$this->page->description($description);
		$this->post->viewed();
		$this->post->videoPosts(0, 8, "sortByVisible", $this->video->vars()->channel_id);
		$this->show_related = 20;
		return;
	}

	public function videosList() : array
	{
		$videos = [];
		if($this->video->vars()->url_1080p) {
			$videos[] = [
				"label" => "1080p HD",
				"src" => "/" . $this->video->vars()->url_1080p
			];
		}
		if($this->video->vars()->url_720p) {
			$videos[] = [
				"label" => "720p HD",
				"src" => "/" . $this->video->vars()->url_720p
			];
		}
		if($this->video->vars()->url_480p) {
			$videos[] = [
				"label" => "480p SD",
				"src" => "/" . $this->video->vars()->url_480p
			];
		}
		if($this->video->vars()->url_low) {
			$videos[] = [
				"label" => "SD",
				"src" => "/" . $this->video->vars()->url_low
			];
		}
		return $videos;
	}
}
?>
