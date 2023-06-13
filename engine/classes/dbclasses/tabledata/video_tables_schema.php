<?php
//database tables schema structures for tables required for tube style sites.
//Need to be prefixed with domain prefix

    define('VIDEO_TABLE_SCHEMA', array(
        array("name"=>"_videos", "columns"=>[
            "id"=>              "int unsigned NOT NULL auto_increment primary key",
            "video_id"=>        "int unsigned NOT NULL default '0'",
            "orig_url"=>        "char(128)",
            "orig_filename"=>   "char(255)",
            "base_url"=>        "char(128)",
            "base_filename"=>   "char(80)",
            "orig_width"=>      "smallint unsigned NOT NULL default '0'",
            "orig_height"=>     "smallint unsigned NOT NULL default '0'",
            "duration"=>        "mediumint unsigned NOT NULL default '0'",
            "fps"=>             "float NOT NULL default '0'",
            "r_fps"=>           "float NOT NULL default '0'",
            "orientation"=>     "enum('landscape','portrait') NOT NULL default 'landscape'",
            "url_1080p"=>       "char(128)",
            "url_720p"=>        "char(128)",
            "url_480p"=>        "char(128)",
            "url_180p"=>        "char(128)",
            "url_low"=>         "char(128)",
            "url_vtt"=>         "char(128)",
            "url_poster"=>      "char(128)",
            "url_thumbnail"=>   "char(128)",
            "channel_id"=>      "int unsigned NOT NULL default '0'",
            "time_added"=>      "int unsigned NOT NULL default '0'",
            "time_processed"=>  "int unsigned NOT NULL default '0'",
            "transcode_start"=> "int unsigned NOT NULL default '0'",
            "process_state"=>   "enum('pending','transcoding','transcoded','processing','processed','tiling','ready','posted') NOT NULL default 'pending'",
            "transcoding"=>     "enum('none','low','180p','480p','720p','1080p') NOT NULL default 'none'",
            "progress"=>        "float NOT NULL default '0'",
            "user_id"=>         "int unsigned NOT NULL default '0'"
        ]),
        array("name"=>"_channels", "columns"=>[
            "id"=>              "int unsigned NOT NULL auto_increment primary key",
            "channel_id"=>      "int unsigned NOT NULL default '0'",
            "channel_name"=>    "char(64)",
            "link_url"=>        "char(255)",
            "channel_state"=>   "enum('pending','display','hide','delete') NOT NULL default 'display'",
            "time_added"=>      "int unsigned NOT NULL default '0'",
            "time_updated"=>    "int unsigned NOT NULL default '0'",
            "site_id"=>         "int unsigned NOT NULL default '0'",
            "user_id"=>         "int unsigned NOT NULL default '0'"
        ]),
        array("name"=>"_channel_tag_rel", "columns"=>[
            "id"=>          "int unsigned NOT NULL auto_increment primary key",
            "channel_id"=>  "int unsigned NOT NULL default '0'",
            "tag_id"=>      "int unsigned NOT NULL default '0'"
        ])
    ));
?>