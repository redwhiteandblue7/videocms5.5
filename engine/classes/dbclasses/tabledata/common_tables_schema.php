<?php
//database tables schema structures for tables common to all sites in an engine5.0 cms network

//the tables initially needed before the admin user can log in
define('INITIAL_TABLE_SCHEMA', array(
        array("name"=>"domains", "columns"=>[
            "domain_id"=>   "int unsigned NOT NULL auto_increment primary key",
            "domain_name"=> "char(64)",
            "site_name"=>   "char(64)",
            "sub_domain"=>  "char(16)",
            "public_path"=> "char(255)",
            "asset_path"=>  "char(255)",
            "http_scheme"=> "char(5)",
            "priority"=>    "smallint unsigned default '0'",
            "status"=>      "tinyint unsigned NOT NULL default '0'",
            "se_tracking"=> "char(64)",
            "default_css"=> "char(64)",
            "css_version"=> "char(64) default '1.0'",
            "admin_ip"=>    "char(64)",
            "test_groups"=> "smallint unsigned NOT NULL default '0'",
            "admin_test_group"=>        "smallint unsigned NOT NULL default '0'",
            "time_last_update"=>        "int unsigned NOT NULL default '0'",
            "time_last_stat_update"=>   "int unsigned NOT NULL default '0'",
            "stat_update_id"=>          "int unsigned NOT NULL default '0'",
            "description"=> "text",
            "time0"=>       "int unsigned NOT NULL default '0'",
            "time1"=>       "int unsigned NOT NULL default '0'",
            "time2"=>       "int unsigned NOT NULL default '0'",
            "time3"=>       "int unsigned NOT NULL default '0'",
            "time4"=>       "int unsigned NOT NULL default '0'",
        ]),
        array("name"=>"users", "columns"=>[
            "user_id"=>     "int unsigned NOT NULL auto_increment primary key",
            "user_name"=>   "char(64)",
            "pass_word"=>   "char(255)",
            "user_nick"=>   "char(64)",
            "user_privilege"=>      "tinyint unsigned NOT NULL default '0'",
            "email_addr"=>          "char(128)",
            "time_registered"=>     "int unsigned NOT NULL default '0'",
            "time_activated"=>      "int unsigned NOT NULL default '0'",
            "time_last_login"=>     "int unsigned NOT NULL default '0'",
            "total_logins"=>        "int unsigned NOT NULL default '0'",
            "time_last_page"=>      "int unsigned NOT NULL default '0'",
            "activate_key"=>        "char(64)"
        ])
    ));

//common tables needed when the first domain is added
define('COMMON_TABLE_SCHEMA', array(
        array("name"=>"daily_stats", "columns"=>[
            "stat_id"=>     "int unsigned NOT NULL auto_increment primary key",
            "stat_time"=>   "int unsigned NOT NULL default '0'",
            "domain_id"=>   "int unsigned NOT NULL default '0'",
            "visitors"=>    "int unsigned NOT NULL default '0'",
            "page_loads"=>  "int unsigned NOT NULL default '0'",
            "click_thrus"=> "int unsigned NOT NULL default '0'",
            "searches"=>    "int unsigned NOT NULL default '0'",
            "se_tracked"=>  "int unsigned NOT NULL default '0'"
        ]),
        array("name"=>"sponsors", "columns"=>[
            "sponsor_id"=>  "int unsigned NOT NULL auto_increment primary key",
            "sponsor_name"=>"char(64)"
        ]),
        array("name"=>"useragents", "columns"=>[
            "agent_id"=>    "int unsigned NOT NULL auto_increment primary key",
            "useragent"=>   "char(255)"
        ]),
        array("name"=>"platforms", "columns"=>[
            "platform_id"=> "int unsigned NOT NULL auto_increment primary key",
            "platform"=>    "char(64)"
        ]),
        array("name"=>"browsers", "columns"=>[
            "browser_id"=>  "int unsigned NOT NULL auto_increment primary key",
            "browser"=>     "char(64)"
        ]),
        array("name"=>"referdomains", "columns"=>[
            "dom_id"=>      "int unsigned NOT NULL auto_increment primary key",
            "domainstring"=>"char(64)"
        ]),
        array("name"=>"searches", "columns"=>[
            "id"=>          "int unsigned NOT NULL auto_increment primary key",
            "dom_id"=>      "int unsigned NOT NULL default '0'"
        ]),
    ));

//tables each domain requires its own instance of
//Need to be prefixed with domain prefix
define('DOMAIN_TABLE_SCHEMA', array(
        array("name"=>"_pageloads", "columns"=>[
            "pageload_id"=> "int unsigned NOT NULL auto_increment primary key",
            "stat_id"=>     "int unsigned NOT NULL default '0'",
            "ip_address"=>  "int unsigned NOT NULL default '0'",
            "stime"=>       "int unsigned",
            "ctime"=>       "int unsigned",
            "ref_id"=>      "int unsigned",
            "dom_id"=>      "int unsigned",
            "first_dom_id"=>"int unsigned",
            "first_reflink_id"=>    "int unsigned",
            "pagename_id"=> "int unsigned",
            "visitor_id"=>  "int unsigned",
            "site_id"=>     "int unsigned NOT NULL default '0'",
            "gallery_id"=>  "int unsigned NOT NULL default '0'",
            "hardlink_id"=> "int unsigned NOT NULL default '0'",
            "testgroup"=>   "smallint unsigned NOT NULL default '0'",
            "flag"=>        "char(1)"
        ], "indexes"=>[
            "stime_ix"=>    "stime",
            "ref_id_ix"=>   "ref_id",
            "dom_id_ix"=>   "dom_id",
            "first_dom_id_ix"=>     "first_dom_id",
            "first_reflink_id_ix"=> "first_reflink_id",
            "page_id_ix"=>  "page_id",
            "link_id_ix"=>  "link_id",
            "visitor_id_ix"=>       "visitor_id"
        ]),
/*
        array("name"=>"_clickthrus", "columns"=>[
            "pageload_id"=> "int unsigned NOT NULL auto_increment primary key",
            "stat_id"=>     "int unsigned NOT NULL default '0'",
            "ip_address"=>  "int unsigned NOT NULL default '0'",
            "stime"=>       "int unsigned",
            "ctime"=>       "int unsigned",
            "ref_id"=>      "int unsigned",
            "dom_id"=>      "int unsigned",
            "first_dom_id"=>"int unsigned",
            "first_reflink_id"=>    "int unsigned",
            "page_id"=>     "int unsigned",
            "visitor_id"=>  "int unsigned",
            "site_id"=>     "int unsigned NOT NULL default '0'",
            "gallery_id"=>  "int unsigned NOT NULL default '0'",
            "link_id"=>     "int unsigned NOT NULL default '0'",
            "testgroup"=>   "smallint unsigned NOT NULL default '0'"
        ], "indexes"=>[
            "stime_ix"=>    "stime",
            "ref_id_ix"=>   "ref_id",
            "dom_id_ix"=>   "dom_id",
            "first_dom_id_ix"=>     "first_dom_id",
            "first_reflink_id_ix"=> "first_reflink_id",
            "link_id_ix"=>  "link_id",
            "visitor_id_ix"=>       "visitor_id"
        ]),
        array("name"=>"_badclicks", "columns"=>[
            "pageload_id"=> "int unsigned NOT NULL auto_increment primary key",
            "stat_id"=>     "int unsigned NOT NULL default '0'",
            "ip_address"=>  "int unsigned NOT NULL default '0'",
            "stime"=>       "int unsigned",
            "ctime"=>       "int unsigned",
            "ref_id"=>      "int unsigned",
            "dom_id"=>      "int unsigned",
            "first_dom_id"=>"int unsigned",
            "first_reflink_id"=>    "int unsigned",
            "page_id"=>     "int unsigned",
            "visitor_id"=>  "int unsigned",
            "site_id"=>     "int unsigned NOT NULL default '0'",
            "gallery_id"=>  "int unsigned NOT NULL default '0'",
            "link_id"=>     "int unsigned NOT NULL default '0'",
            "testgroup"=>   "smallint unsigned NOT NULL default '0'"
        ], "indexes"=>[
            "stime_ix"=>    "stime",
            "ref_id_ix"=>   "ref_id",
            "dom_id_ix"=>   "dom_id",
            "first_dom_id_ix"=>     "first_dom_id",
            "first_reflink_id_ix"=> "first_reflink_id",
            "link_id_ix"=>  "link_id",
            "visitor_id_ix"=>       "visitor_id"
        ]),
*/
        array("name"=>"_visitors", "columns"=>[
            "visitor_id"=>  "int unsigned NOT NULL auto_increment primary key",
            "cookie_id"=>   "bigint unsigned NOT NULL default '0'",
            "first_time"=>  "int unsigned NOT NULL default '0'",
            "last_time"=>   "int unsigned NOT NULL default '0'",
            "session_time"=>"int unsigned NOT NULL default '0'",
            "screentype"=>  "int unsigned NOT NULL default '0'",
            "agent_id"=>    "int unsigned NOT NULL default '0'",
            "ip_address"=>  "int unsigned NOT NULL default '0'",
            "user_id"=>     "int unsigned NOT NULL default '0'",
            "first_dom_id"=>        "int unsigned NOT NULL default '0'",
            "first_reflink_id"=>    "int unsigned NOT NULL default '0'",
            "country"=>     "char(5)",
            "testgroup"=>   "smallint unsigned NOT NULL default '0'",
            "platform_id"=> "int unsigned NOT NULL default '0'",
            "browser_id"=>  "int unsigned NOT NULL default '0'"
        ], "indexes"=>[
            "ip_ix"=>           "ip_address",
            "cookie_id_ix"=>    "cookie_id",
            "agent_id_ix"=>     "agent_id",
            "first_dom_id_ix"=>     "first_dom_id",
            "first_reflink_id_ix"=> "first_reflink_id"
        ]),
        array("name"=>"_referstrings", "columns"=>[
            "ref_id"=>      "int unsigned NOT NULL auto_increment primary key",
            "referstring"=> "char(255)"
        ]),
        array("name"=>"_pagenames", "columns"=>[
            "pagename_id"=>     "int unsigned NOT NULL auto_increment primary key",
            "pagename"=>    "char(128)"
        ]),
        array("name"=>"_stats", "columns"=>[
            "stat_id"=>     "int unsigned NOT NULL auto_increment primary key",
            "stime"=>       "int unsigned NOT NULL default '0'",
            "ctime"=>       "int unsigned NOT NULL default '0'",
            "visit_type"=>  "enum('unknown','page','click','bot') NOT NULL default 'page'",
            "referrer"=>    "varchar(511)",
            "creferrer"=>   "varchar(511)",
            "pagename"=>    "varchar(255)",
            "ref_id"=>      "int unsigned NOT NULL default '0'",
            "site_id"=>     "int unsigned NOT NULL default '0'",
            "gallery_id"=>  "int unsigned NOT NULL default '0'",
            "link_id"=>     "int unsigned NOT NULL default '0'",
            "ip_address"=>  "int unsigned NOT NULL default '0'",
            "useragent"=>   "varchar(255)",
            "screentype"=>  "int unsigned NOT NULL default '0'",
            "country"=>     "char(5)",
            "cookie_id"=>   "bigint unsigned NOT NULL default '0'",
            "first_cookie_time"=>   "int unsigned NOT NULL default '0'",
            "last_cookie_time"=>    "int unsigned NOT NULL default '0'",
            "testgroup"=>   "smallint unsigned NOT NULL default '0'",
            "platform"=>    "varchar(64)",
            "browser"=>     "varchar(64)",
            "user_id"=>     "int unsigned NOT NULL default '0'"
        ], "indexes"=>[
            "stime_ix"=>    "stime"
        ]),
        array("name"=>"_tags", "columns"=>[
            "tag_id"=>          "int unsigned NOT NULL auto_increment primary key",
            "tag_name"=>        "char(64)",
            "landing_page"=>    "char(128)",
            "invisible"=>       "enum('false','true') NOT NULL default 'false'"
        ]),
        array("name"=>"_sites", "columns"=>[
            "site_id"=>     "int unsigned NOT NULL auto_increment primary key",
            "sponsor_id"=>  "int unsigned NOT NULL default '0'",
            "site_name"=>   "char(255)",
            "site_ref"=>    "char(255)",
            "site_domain"=> "char(255)",
            "enabled"=>     "enum('enabled','disabled','removed') NOT NULL default 'enabled'",
            "pref"=>        "smallint unsigned NOT NULL default '0'",
        ]),
        array("name"=>"_site_tag_rel", "columns"=>[
            "id"=>      "int unsigned NOT NULL auto_increment primary key",
            "site_id"=> "int unsigned NOT NULL default '0'",
            "tag_id"=>  "int unsigned NOT NULL default '0'"
        ]),
        array("name"=>"_page_tag_rel", "columns"=>[
            "id"=>      "int unsigned NOT NULL auto_increment primary key",
            "page_id"=> "int unsigned NOT NULL default '0'",
            "tag_id"=>  "int unsigned NOT NULL default '0'"
        ]),
        array("name"=>"_pages", "columns"=>[
            "id"=>      "int unsigned NOT NULL auto_increment primary key",
            "page_id"=> "int unsigned NOT NULL default '0'",
            "page_name"=>       "char(128)",
            "page_filename"=>   "char(128)",
            "dest_url"=>        "char(128)",
            "description"=>     "text"
        ]),
        array("name"=>"_hardlinks", "columns"=>[
            "id"=>              "int unsigned NOT NULL auto_increment primary key",
            "hardlink_id"=>     "int unsigned NOT NULL default '0'",
            "ref_code"=>        "int unsigned NOT NULL default '0'",
            "status"=>          "tinyint unsigned NOT NULL default '0'",
            "request_status"=>  "tinyint unsigned NOT NULL default '0'",
            "dom_id"=>          "int unsigned NOT NULL default '0'",
            "outs_24_hours"=>   "int unsigned NOT NULL default '0'",
            "ins_24_hours"=>    "int unsigned NOT NULL default '0'",
            "clicks_24_hours"=> "int unsigned NOT NULL default '0'",
            "outs_7_days"=>     "int unsigned NOT NULL default '0'",
            "ins_7_days"=>      "int unsigned NOT NULL default '0'",
            "clicks_7_days"=>   "int unsigned NOT NULL default '0'",
            "domainstring"=>    "varchar(255)",
            "landing_page"=>    "varchar(255)",
            "anchor"=>          "varchar(255)",
            "description"=>     "varchar(255)",
            "time_visible"=>    "int unsigned NOT NULL default '0'",
            "time_last_valid"=> "int unsigned NOT NULL default '0'",
            "time_last_checked"=>   "int unsigned NOT NULL default '0'",
            "last_status"=>     "tinyint unsigned NOT NULL default '0'"
        ]),
        array("name"=>"_hardlink_tag_rel", "columns"=>[
            "id"=>      "int unsigned NOT NULL auto_increment primary key",
            "link_id"=> "int unsigned NOT NULL default '0'",
            "tag_id"=>  "int unsigned NOT NULL default '0'"
        ]),
        array("name"=>"_googlebot_stats", "columns"=>[
            "id"=>          "int unsigned NOT NULL auto_increment primary key",
            "stat_id"=>     "int unsigned NOT NULL default '0'",
            "gallery_id"=>  "int unsigned NOT NULL default '0'",
            "stime"=>       "int unsigned NOT NULL default '0'",
            "pagename"=>    "char(200)",
            "ip_address"=>  "int unsigned NOT NULL default '0'",
            "useragent"=>   "char(255)"
        ])
    ));
