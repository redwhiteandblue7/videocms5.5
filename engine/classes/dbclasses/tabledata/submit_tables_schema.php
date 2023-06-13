<?php
//database tables schema structures for tables required for user submissions.

    define('SUBMIT_TABLE_SCHEMA', array(
        array("name"=>"user_submits", "columns"=>[
            "submit_id"=>       "int unsigned NOT NULL auto_increment primary key",
            "submit_time"=>     "int unsigned NOT NULL default '0'",
            "submit_domain"=>   "int unsigned NOT NULL default '0'",
            "progress"=>        "enum('new','notified','pending','processed','rejected') NOT NULL default 'new'",
            "ip_address"=>      "varchar(255)",
            "useragent"=>       "varchar(255)",
            "user_name"=>       "varchar(64)",
            "email_addr"=>      "varchar(128)",
            "submit_title"=>    "varchar(255)",
            "submit_category"=> "varchar(255)",
            "submit_tags"=>     "varchar(255)",
            "submit_url"=>      "varchar(255)",
            "submit_thumb"=>    "varchar(255)",
            "submit_content"=>  "text"
        ])
    ));
?>