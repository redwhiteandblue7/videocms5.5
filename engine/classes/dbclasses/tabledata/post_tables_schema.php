<?php
//database tables schema structures for tables required for all post types
//Need to be prefixed with domain prefix
define('POST_TABLE_SCHEMA', array(
        array("name"=>"_posts", "columns"=>[
            "id"=>          "int unsigned NOT NULL auto_increment primary key",
            "post_id"=>     "int unsigned NOT NULL default '0'",
            "video_id"=>    "int unsigned NOT NULL default '0'",
            "channel_id"=>  "int unsigned NOT NULL default '0'",
            "priority"=>    "smallint unsigned NOT NULL default '0'",
            "ranking"=>     "smallint unsigned NOT NULL default '0'",
            "thumb_url"=>   "varchar(255)",
            "video_url"=>   "varchar(255)",
            "alt_title"=>   "varchar(255)",
            "title"=>       "varchar(255)",
            "pagename"=>    "varchar(255)",
            "description"=> "text",
            "site_id"=>     "int unsigned NOT NULL default '0'",
            "trade_id"=>    "int unsigned NOT NULL default '0'",
            "duration"=>    "smallint unsigned NOT NULL default '0'",
            "orig_width"=>  "smallint unsigned NOT NULL default '0'",
            "orig_height"=> "smallint unsigned NOT NULL default '0'",
            "orig_thumb"=>  "varchar(255)",
            "opti_thumb"=>  "varchar(255)",
            "site_url"=>    "varchar(255)",
            "icon_url"=>    "varchar(255)",
            "time_added"=>  "int unsigned NOT NULL default '0'",
            "time_visible"=>    "int unsigned NOT NULL default '0'",
            "time_updated"=>    "int unsigned NOT NULL default '0'",
            "post_type"=>       "enum('blog','reviews','pornstars','video','channel') NOT NULL default 'blog'",
            "link_type"=>       "enum('dofollow','nofollow','none') NOT NULL default 'dofollow'",
            "display_state"=>   "enum('display','hide','delete') NOT NULL default 'display'",
            "daily_clicks"=>    "int unsigned NOT NULL default '0'",
            "daily_views"=>     "int unsigned NOT NULL default '0'",
            "daily_prod"=>      "int unsigned NOT NULL default '0'",
            "monthly_clicks"=>  "int unsigned NOT NULL default '0'",
            "monthly_views"=>   "int unsigned NOT NULL default '0'",
            "monthly_prod"=>    "int unsigned NOT NULL default '0'",
            "prev_prod"=>       "int unsigned NOT NULL default '0'",
            "time_last_viewed"=>"int unsigned NOT NULL default '0'",
            "total_clicks"=>    "int unsigned NOT NULL default '0'",
            "rating_count"=>    "int unsigned NOT NULL default '0'",
            "rating_total"=>    "int unsigned NOT NULL default '0'",
            "user_id"=>         "int unsigned NOT NULL default '0'"
        ]),
        array("name"=>"_post_tag_rel", "columns"=>[
            "id"=>      "int unsigned NOT NULL auto_increment primary key",
            "post_id"=> "int unsigned NOT NULL default '0'",
            "tag_id"=>  "int unsigned NOT NULL default '0'"
        ]),
        array("name"=>"_relatedpost_tag_rel", "columns"=>[
            "id"=>      "int unsigned NOT NULL auto_increment primary key",
            "post_id"=> "int unsigned NOT NULL default '0'",
            "tag_id"=>  "int unsigned NOT NULL default '0'"
        ])
    ));


?>