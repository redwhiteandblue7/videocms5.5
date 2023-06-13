<?php
//database defines
	define("DB_HOST", "localhost");
	define("DB_USER", "admin_usr1967");
	define("DB_PASSWORD", "escaped2015");
	define("DB_NAME", "admin_db1973");

	define('VISITS_TTL', 604800);
	define('PAGELOADS_TTL', 604800);
	define('STATS_TTL', 604800);

//cookie defines
	define ('LAST_COOKIE_EXPIRE', 60*60*2);               //1 or 2 hours
	define ('FIRST_COOKIE_EXPIRE', 60*60*24*100);        //100 days
	define ('FIRST_TIME_COOKIE', "first_visit");
	define ('LAST_TIME_COOKIE', "last_visit");
?>