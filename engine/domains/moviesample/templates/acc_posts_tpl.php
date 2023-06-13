<?php
    if($this->user->logged_in) {
        $this->post = new Post();
        $this->num_of_videos = $this->post->videoPosts(0, 9999, "sortByAdded", 0, 0, $this->user->user_id);
?>
<h1>This is your posted videos page.</h1>
<div class="acc-grid accbtns">
<?php
        if($this->num_of_videos) {
?>
<div class="acc-span label">
<a href="/myaccount.html"><button class="large">Go Back To My Account Page</button></a>
<p>Here are the videos you have posted. Use the buttons next to each video to edit or hide/unhide the video.</p>
</div>
<?php
            include(INCLUDE_PATH . "domains/moviesample/templates/post_content_tpl.php");
        } else {
?>
<div class="acc-span label">
<a href="/myaccount.html"><button class="large">Go Back To My Account Page</button></a>
<p>No videos found.</p>
</div>
<?php
        }
?>
</div>
<?php
    } else {
?>
<p class="hugepad">You must be logged in to view this page.</p>
<?php
    }
?>