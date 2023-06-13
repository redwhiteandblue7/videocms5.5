<?php

class ServerFuncs
{
    public static function createCookieID($ip_address, $stime)
    {
        $cookie_id = $stime % 4294967296;		//use the lower 32 bits of the timestamp
        $cookie_id = $ip_address * 4294967296 + $cookie_id;	//with the 32 bit ip address to create a 64 bit unique identifier
        return $cookie_id;
    }

	//returns true if the ip address is in the cidr range
	public static function ip_in_cidr($ip, $cidr)
	{
		list($subnet, $mask) = explode('/', $cidr);
		$ip = ip2long($ip);
		$subnet = ip2long($subnet);
		$mask = ~((1 << (32 - $mask)) - 1);
		return ($ip & $mask) == ($subnet & $mask);
	}

	private static function getDomainPrefix($domainstring)
	{
		$domainstring = str_replace(".", "_", $domainstring);
		$domainstring = str_replace("-", "_", $domainstring);
		$domainstring = strtolower($domainstring);

		return $domainstring;
	}

	private static function getTablesPrefix($hostname)
	{
		if(substr($hostname, 0, 4) == "www.")
		{
			$hostname = substr($hostname, 4);
		}
		elseif(substr($hostname, 0, 5) == "test.")
		{
			$hostname = substr($hostname, 5);
		}
		$dom = explode(".", $hostname);
		$domain = $dom[0];
		return self::getDomainPrefix($domain);
	}

	public static function domainPrefix()
	{
        $h = $_SERVER["HTTP_HOST"];
        if(substr($h, 0, 3) == "192")
        {
            $h = FALLBACK_DOMAIN;
        }
		return self::getTablesPrefix($h);
	}


}
?>