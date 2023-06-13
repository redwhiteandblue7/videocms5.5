<aside class="grid-right">
<?php
    $side_post = new Post($this->post_id);

    if($this->show_tags ?? 0) {
        echo "<div><h3>Popular Tags</h3><ul class=\"tags\">";
        $tags = $this->tags->sortedTags($this->show_tags);
        foreach($tags as $tag) {
            if($tag->num_posts) {
                echo "<li><a href=\"/tag/" . $tag->tag_name . "\">#$tag->tag_name</a></li>";
            }
        }
        echo "</ul></div><div class=\"side-wide side-link\"><a href=\"/tags.html\">See All Tags</a></div>";
    }

    if($this->show_related ?? 0) {
        $related_videos = $side_post->relatedPosts($this->show_related);
        if($related_videos) {
            echo "<div class=\"side-grid\"><div class=\"side-wide\"><h3>More Videos Like This</h3></div>";
            while($video = $side_post->next()) {
                include(INCLUDE_PATH . "domains/moviesample/templates/sthmb_template.php");
            }
            echo "</div>";
        }
    }

    if($this->show_history ?? false) {
        echo "<div id=\"sidehistory\"></div>";
    }

    if($this->show_trending ?? 0) {
        $trending_videos = $side_post->videoPosts(0, $this->show_trending, "sortByTrend");
        if($trending_videos) {
            echo "<div class=\"side-grid\"><div class=\"side-wide\"><h3>Trending Videos</h3></div>";
            while($video = $side_post->next()) {
                include(INCLUDE_PATH . "domains/moviesample/templates/sthmb_template.php");
            }
            echo "</div>";
        }
        echo "<div class=\"side-wide side-link\"><a href=\"/trending.html\">See All Trending</a></div>";
    }
?>
</aside>