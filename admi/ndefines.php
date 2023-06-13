<?php
	//path to objects folder
	define('HOME_DIR', __DIR__ . '/../');
	define('INCLUDE_PATH', HOME_DIR . 'engine/');
	define('ADMIN_PATH', HOME_DIR . 'admi/');
	define('OBJECTS_PATH', INCLUDE_PATH . 'objects/');
	define('DB_PATH', INCLUDE_PATH .'/classes/dbclasses/');

    //this is for running on a local machine where domain is not available in HTTP_HOST
    //define ('FALLBACK_DOMAIN', "filthyasianporn.com");

	//general defines
	define('DEFAULT_ADMIN_ACTION', "ShowData");
	define('ADMIN_MODULES', "posts,videos,banners,sites,links");
	define('ROWS_PER_PAGE', 100);
	define('GALS_PER_PAGE', 20);
	define('BANNERS_FOLDER', "images");
	define('ICON_FOLDER', '/images/icons/');

	define('TWELVE_HOURS', 43200);
	define('ONE_HOUR', 3600);
	define('TWENTYFOUR_HOURS', 86400);
	define('SEVEN_DAYS', 604800);
	define('ONE_WEEK', 604800);

//	define('SCREENSHOT_API', "https://shot.screenshotapi.net/screenshot?token=KGXKBDT-QV8M40F-M1KDKRR-CJQ01WX&url=_SITEURL_&width=1280&height=1280&output=image&file_type=png&no_cookie_banners=true&fresh=true&wait_for_event=load");
	define('SCREENSHOT_API', "https://api.screenshotone.com/take?url=_SITEURL_&access_key=DXZRWzQ9nwbwNg&format=png&capture_beyond_viewport=false&viewport_width=1280&viewport_height=1280&block_cookie_banners=true&block_chats=true");

?>
