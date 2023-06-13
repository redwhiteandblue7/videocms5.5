<?php
    //this is for running on a local machine where domain is not available in HTTP_HOST
    define ('FALLBACK_DOMAIN', "moviesample.net");

	//database defines
//	define("DB_HOST", "localhost");
//	define("DB_USER", "admin_usr1967");
//	define("DB_PASSWORD", "escaped2015");
//	define("DB_NAME", "admin_db1973");

	define("DB_HOST", "localhost");
	define("DB_USER", "admin_usr1967");
	define("DB_PASSWORD", "escaped2015");
	define("DB_NAME", "admin_ukas2023");

	define('USERNAME_LENGTH', 4);
	define('PASSWORD_LENGTH', 11);

	define('VISITS_TTL', 604800);
	define('PAGELOADS_TTL', 604800);
	define('STATS_TTL', 604800);
?>