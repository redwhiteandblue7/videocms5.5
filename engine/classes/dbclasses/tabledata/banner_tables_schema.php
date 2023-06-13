<?php
//database tables schema structures for tables required for tube style sites.
//Need to be prefixed with domain prefix

    define('BANNER_TABLE_SCHEMA', array(
        array("name"=>"_banners", "columns"=>[
            "id"=>              "int unsigned NOT NULL auto_increment primary key",
            "banner_id"=>       "int unsigned NOT NULL default '0'",
            "banner_url"=>      "char(255)",
            "banner_width"=>    "smallint unsigned default '0'",
            "banner_height"=>   "smallint unsigned default '0'",
            "monthly_clicks"=>  "int unsigned NOT NULL default '0'",
            "monthly_views"=>   "int unsigned NOT NULL default '0'",
            "monthly_prod"=>    "int unsigned NOT NULL default '0'",
            "prev_prod"=>       "int unsigned NOT NULL default '0'"
        ])
    ));
?>