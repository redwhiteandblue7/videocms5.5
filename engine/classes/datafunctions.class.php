<?php
	require_once(INCLUDE_PATH . 'classes/db.class.php');
	require_once(INCLUDE_PATH . 'classes/server.class.php');
    define('ONE_HOUR', 3600);
    define('TWENTYFOUR_HOURS', 86400);
	define('SEVEN_DAYS', 604800);
	define('ONE_WEEK', 604800);

class DataFunctions extends Db
{
    public $domain = "";
    public $error = "";
    public $num_rows = 0;
    public $current_time = 0;
    public $execute_time = 0;
    public $time0 = 0;
    public $time1 = 0;
    public $time2 = 0;
    public $time3 = 0;
    public $time4 = 0;
    public $time5 = 0;

    private $domain_vars;
    private $page_insert_columns = "";
    private $page_insert_values = [];

    private $domain_dom_id;
    private $google_dom_id;
    private $slash_ref_id;
    private $stat_update_id = 0;

    public function __construct($dn = "")
    {
        parent::__construct();

        if(!$dn) {
            $dn = $_SERVER["HTTP_HOST"];
        }

        if(!$dn) {
            $this->error = "Could not get hostname";
            return;
        }
        if(substr($dn, 0, 4) == "www.") $dn = substr($dn, 4);
        $this->domain = $dn;
        $q = "select * from domains where domain_name='$dn' and `status`=1";
        $r = $this->dbc->query($q) or die($this->dbc->error);
        if(!$r->num_rows) {
            $this->error = "Domain not found";
            return;
        }
        $this->domain_vars = $r->fetch_object();
    }

    public function updateDomain()
    {
        $t = time();
        $q = "update domains set
                time_last_stat_update=$t,
                stat_update_id=0
                where
                time_last_stat_update={$this->domain_vars->time_last_stat_update}
                and domain_id={$this->domain_vars->domain_id}
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error);
        return $this->dbc->affected_rows;
    }

    //this function builds the referrers, referdomains, pages, useragents, screentypes, visitors and pageloads
    public function buildPageloadStats($limit = 1000)
    {
        $prefix = $this->table_prefix;

        $this->current_time = time();
        $this->execute_time = microtime(true);

        $this->getDomainIDs();
        $this->getReferstringIDs();

        if($this->stat_update_id == 0)
        {
            $this->updateStatUpdateID();
        }

        $this->time0 = (microtime(true) - $this->execute_time);	//time to find default values

/*
            $q = "truncate {$prefix}_pageloads";
            $r = $this->dbc->query($q) or die($this->dbc->error) . ", query was $q";
            $q = "truncate {$prefix}_clickthrus";
            $r = $this->dbc->query($q) or die($this->dbc->error) . ", query was $q";
            $q = "truncate {$prefix}_badclicks";
            $r = $this->dbc->query($q) or die($this->dbc->error) . ", query was $q";
            $q = "truncate {$prefix}_visitors";
            $r = $this->dbc->query($q) or die($this->dbc->error) . ", query was $q";
*/
        $x1_time = microtime(true);

        $query = "select
                stat_id,
                stime,
                ctime,
                visit_type,
                referrer,
                creferrer,
                pagename,
                ref_id as visitor_reflink_id,
                site_id,
                gallery_id,
                link_id,
                ip_address,
                useragent,
                screentype,
                country,
                cookie_id,
                first_cookie_time,
                last_cookie_time,
                platform,
                browser,
                testgroup,
                user_id
                from {$prefix}_stats
                where stat_id>{$this->stat_update_id}
                order by stat_id
                limit $limit";
        $stat = $this->dbc->query($query) or die($this->dbc->error);

        $this->time1 = (microtime(true) - $x1_time);	//time to execute select query

        $stat_id = 0;
        $visitor_id = 0;
        $agent_id = 0;
        $platform_id = 0;
        $browser_id = 0;

        while($results = $stat->fetch_assoc()) {
            extract($results);

            $x2_time = microtime(true);

            $page_id = $this->getPageID($pagename);
            $referrer = $this->getReferrer($referrer, $creferrer);

            if($visitor_reflink_id) {
                //if this hit has a referral code, set the referrer as though it came from the domain defined in the hardlinks table for this ref
                $dom_id = $this->getDomainIDFromReflink($visitor_reflink_id);
                $ref_id = $this->slash_ref_id;
            } else {
                if(strlen($referrer) > 1) {
                    //otherwise find the referring domain id and refer string id by looking up in the relevant tables
                    if($d = parse_url($referrer)) {
                        $dom = $d["host"] ?? "";
                        $scheme = $d["scheme"] ?? "";
                        if(($dom) && substr($dom, 0, 4) == "www.") $dom = substr($dom, 4);
                        $dom_id = $this->getDomainID($dom, $scheme);
                        $referstring = $d["path"] ?? "";
                        if(isset($d["query"])) $referstring .= "?" . $d["query"];    
                    } else {
                        $dom_id = 0;
                        $referstring = "[" . $referrer . "]";
                    }
                    $referstring = $this->dbc->real_escape_string($referstring);
                    if(strlen($referstring) > 256) $referstring = substr($referstring, 0, 255);
                } else {
                    $dom_id = 0;
                    $referstring = $this->parseReferrer($referrer);
                }

                $ref_id = $this->getReferstringID($referstring);
                $visitor_reflink_id = $this->getReflinkID($dom_id);
            }

            if(!$agent_id || ($useragent != $last_useragent)) $agent_id = $this->getUseragentID($useragent);
            if(!$platform_id || ($platform !== $last_platform)) $platform_id = $this->getPlatformID($platform);
            if(!$browser_id || ($browser !== $last_browser)) $browser_id = $this->getBrowserID($browser);

            $country = strtolower($country);

            $this->time2 += (microtime(true) - $x2_time);	//time to process referrer, page and ua strings

    //We now have ref_id, stime, ctime, page_id, browser, user, ip, and res. All we need to know is the flag
    //value, for that we need to know who the visitor is.

            $last_time = 0;
            $first_dom_id = $dom_id;
            $first_reflink_id = $visitor_reflink_id;
            $visit_time = $stime;
            if($first_cookie_time == 0) {
                $first_cookie_time = $stime;
            }
            $session_time = ($visit_type == "bot") ? 0 : $stime;
            if(!is_numeric($testgroup)) $testgroup = 0;

            $x3_time = microtime(true);

            //now to work out who the visitor is
            //if he has a cookie id, he is not a new visitor. Search for him in the visitors table by cookie_id
            if($cookie_id) {
//              if($cookie_id == $last_cookie_id)
                if(false) {
                    //this is the same visitor just processed in the last loop so no need to search for him twice
                    $flag = "S";
                    $this->updateVisitor($visitor_id, 0, 0, $visit_time, $session_time, $user_id, $flag, $testgroup);
                } else {
                    $q = "select
                            visitor_id,
                            last_time,
                            first_dom_id,
                            first_reflink_id
                            from {$prefix}_visitors
                            where
                            cookie_id=$cookie_id
                            order by last_time desc limit 1";
                    $r = $this->dbc->query($q) or die($this->dbc->error);
                    if($row = $r->fetch_assoc()) {
                        extract($row);
                        $r->free();
                        $flag = ($stime - $last_time < ONE_HOUR) ? "S" : "R";
                        $this->updateVisitor($visitor_id, 0, $agent_id, $visit_time, $session_time, $user_id, $flag, $testgroup);
                    } else {
                        //not found but he has a cookie id so he is a returning visitor who has been deleted from visitors table. Re-insert
                        $flag = "R";
                        $visitor_id = $this->insertVisitor($cookie_id, $visit_time, $session_time, $first_cookie_time, $screentype, $country, $agent_id, $ip_address, $user_id, $dom_id, $visitor_reflink_id, $platform_id, $browser_id, $testgroup);
                    }
                }
            } else {
                //no cookie id could mean a new visitor, not accepting cookies, or deleted cookie, therefore search by ip address and user agent
                //the cookie id must be created using exact same parameters as when setting the cookie otherwise we won't find him again on second pageload
                //if he is a returning visitor with deleted cookie we need to update with the new cookie id
                $cookie_id = ServerFuncs::createCookieID($ip_address, $stime);
                $q = "select
                        visitor_id,
                        last_time,
                        first_dom_id,
                        first_reflink_id
                        from {$prefix}_visitors
                        where
                        ip_address=$ip_address
                        and agent_id=$agent_id
                        order by last_time desc limit 1";
                $r = $this->dbc->query($q) or die($this->dbc->error);
                if($row = $r->fetch_assoc()) {
                    extract($row);
                    $flag = ($stime - $last_time < ONE_HOUR) ? "S" : "R";
                    $this->updateVisitor($visitor_id, $cookie_id, $agent_id, $visit_time, $session_time, $user_id, $flag, $testgroup);
                    $r->free();
                } else {
                    //not found so this is a completely new visitor
                    $flag = "N";
                    $visitor_id = $this->insertVisitor($cookie_id, $visit_time, $session_time, $first_cookie_time, $screentype, $country, $agent_id, $ip_address, $user_id, $dom_id, $visitor_reflink_id, $platform_id, $browser_id, $testgroup);
                }
            }

            $this->time3 += (microtime(true) - $x3_time);

            $x4_time = microtime(true);

            //Now insert the pageload
            if(($visit_type == "bot") || ($ctime == 2)) {
                $flag = "B";
            } elseif(($visit_type == "click") || ($ctime == 1)) {
                $flag = "C";
            }

            if($ctime < 3) $ctime = 0;
            $this->addPageload($stat_id, $ip_address, $stime, $ctime, $ref_id, $dom_id, $page_id, $visitor_id, $site_id, $gallery_id, $link_id, $flag, $first_dom_id, $first_reflink_id, $testgroup);

            $last_cookie_id = $cookie_id;
            $last_useragent = $useragent;
            $last_platform = $platform;
            $last_browser = $browser;
            $this->time4 += (microtime(true) - $x4_time);
        }

        $stat->free();

        $x5_time = microtime(true);

        $this->insertPageloadRows();

        $this->time5 = microtime(true) - $x5_time;

        if($stat_id != 0) {
            $time0 = (int)($this->time0 * 1000);
            $time1 = (int)($this->time1 * 1000);
            $time2 = (int)($this->time2 * 1000);
            $time3 = (int)($this->time3 * 1000);
            $time4 = (int)($this->time4 * 1000);
            $time5 = (int)($this->time5 * 1000);

            echo "Times to process stats in milliseconds: ";
            echo $time0 . ", " . $time1 . ", " . $time2 . ", " . $time3 . ", " . $time4 . ", " . $time5 . "\n\r";

            $q = "update domains set
                    time_last_stat_update={$this->current_time},
                    stat_update_id=$stat_id,
                    time0=$time0,
                    time1=$time1,
                    time2=$time2,
                    time3=$time3,
                    time4=$time4
                    where domain_id={$this->domain_vars->domain_id}";
            $r = $this->dbc->query($q) or die($this->dbc->error);
        }
    }

    private function getDomainIDs()
    {
        //first save ourselves some time by finding the most common referrer domains and strings
        $dn = $_SERVER["HTTP_HOST"];

        $q = "select dom_id from referdomains where domainstring='$dn'";
        $r = $this->dbc->query($q) or die($this->dbc->error);
        if($r->num_rows) {
            $this->domain_dom_id = $r->fetch_row()[0];
            $r->free();
        } else {
            $r->free();
            $q = "insert into referdomains set domainstring='$dn'";
            $r = $this->dbc->query($q) or die($this->dbc->error);
            $this->domain_dom_id = $this->dbc->insert_id;
        }

        $q = "select dom_id from referdomains where domainstring='google.com'";
        $r = $this->dbc->query($q) or die($this->dbc->error);
        if($r->num_rows) {
            $this->google_dom_id = $r->fetch_row()[0];
            $r->free();
        } else {
            $r->free();
            $q = "insert into referdomains set domainstring='google.com'";
            $r = $this->dbc->query($q) or die($this->dbc->error);
            $this->google_dom_id = $this->dbc->insert_id;
        }

    }

    private function getReferstringIDs()
    {
        $prefix = $this->table_prefix;
        $q = "select ref_id from {$prefix}_referstrings where referstring='/'";
        $r = $this->dbc->query($q) or die($this->dbc->error);
        if($r->num_rows) {
            $this->slash_ref_id = $r->fetch_row()[0];
            $r->free();
        } else {
            $r->free();
            $q = "insert into {$prefix}_referstrings set referstring='/'";
            $r = $this->dbc->query($q) or die($this->dbc->error);
            $this->slash_ref_id = $this->dbc->insert_id;
        }
    }

    private function updateStatUpdateID()
    {
        $prefix = $this->table_prefix;
        $q = "select max(stat_id) as stat from {$prefix}_pageloads";
        $r = $this->dbc->query($q) or die($this->dbc->error);
        if($r->num_rows) {
            $this->stat_update_id = $r->fetch_row()[0];
            $r->free();
            if(!is_numeric($this->stat_update_id)) $this->stat_update_id = 0;
        }
    }

    private function getPageID($pagename)
    {
        if($pagename == "") {
            return 0;
        } else {
            $prefix = $this->table_prefix;
            if(strlen($pagename) > 127) $pagename = substr($pagename, 0, 126);
            $pagename = $this->dbc->real_escape_string($pagename);
            $q = "select pagename_id from {$prefix}_pagenames where pagename='$pagename'";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
            if($row = $r->fetch_assoc()) {
                $pagename_id = $row["pagename_id"];
                $r->free();
            } else {
                $r->free();
                $q = "insert into {$prefix}_pagenames set pagename='$pagename'";
                $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
                $pagename_id = $this->dbc->insert_id;
            }
            return $pagename_id;
        }
    }

    private function getReferrer($referrer, $creferrer)
    {
        $ref = "";
        if($referrer) {
            $ref = strtolower($referrer);
        } elseif($creferrer) {
            $ref = strtolower($creferrer);
        }

        return $ref;
    }

    private function parseReferrer($ref)
    {
        if(!$ref) return "[Unknown]";
        if($ref == "-") return "[No referring link]";
        return $ref;
    }

    private function getDomainID($dom, $scheme)
    {
        if((!$scheme) || (!$dom) || ((substr($scheme, 0, 4) != "http") && (substr($scheme, 0, 11) != "android-app"))) return 0;

        $dn = $_SERVER["HTTP_HOST"];
        if($dom == $dn) return $this->domain_dom_id;
        if($dom == "google.com") return $this->google_dom_id;

        $dom = $this->dbc->real_escape_string($dom);
        if(strlen($dom) > 64) $dom = substr($dom, 0, 63);
        $q = "select dom_id from referdomains where domainstring='$dom' limit 1";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        if($r->num_rows) {
            $dom_id = $r->fetch_row()[0];
            $r->free();
            return $dom_id;
        }
        
        $r->free();
        $q = "insert into referdomains set domainstring='$dom'";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        return $this->dbc->insert_id;
    }

    private function getReferstringID($ref)
    {
        if($ref == "/") return $this->slash_ref_id;

        $prefix = $this->table_prefix;
        $ref = $this->dbc->real_escape_string($ref);
        if(strlen($ref) > 254) $ref = substr($ref, 0, 254);
        $q = "select ref_id from {$prefix}_referstrings where referstring='$ref'";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        if($r->num_rows) {
            $ref_id = $r->fetch_row()[0];
            $r->free();
            return $ref_id;
        }

        $r->free();
        $q = "insert into {$prefix}_referstrings set referstring='$ref'";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        return $this->dbc->insert_id;
    }

    private function getReflinkID($dom_id)
    {
        $prefix = $this->table_prefix;
        if($dom_id) {
            $q = "select ref_code from {$prefix}_hardlinks where dom_id=$dom_id";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
            if($r->num_rows) {
                $id = $r->fetch_row()[0];
                $r->free();
                return $id;
            } else {
                $r->free();
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function getDomainIDFromReflink($reflink)
    {
        $prefix = $this->table_prefix;
        $q = "select dom_id from {$prefix}_hardlinks where ref_code=$reflink";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        if($r->num_rows) {
            $id = $r->fetch_row()[0];
            $r->free();
            return $id;
        }
        return 0;
    }

    private function getUseragentID($useragent)
    {
        if($useragent == "") {
            return 0;
        } else {
            if(strlen($useragent) > 256) $useragent = substr($useragent, 0, 255);
            $useragent = $this->dbc->real_escape_string($useragent);
            $q = "select agent_id from useragents where useragent='$useragent'";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
            if($r->num_rows) {
                $agent_id = $r->fetch_row()[0];
                $r->free();
                return $agent_id;
            } else {
                $r->free();
                $q = "insert into useragents set useragent='$useragent'";
                $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
                return $this->dbc->insert_id;
            }
        }
    }

    private function getPlatformID($platform)
    {
        if($platform == "")
        {
            return 0;
        }
        else
        {
            if(strlen($platform) > 32) $platform = substr($platform, 0, 31);
            $platform = $this->dbc->real_escape_string($platform);
            $q = "select platform_id from platforms where platform='$platform'";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
            if($r->num_rows)
            {
                $platform_id = $r->fetch_row()[0];
                $r->free();
                return $platform_id;
            }
            else
            {
                $r->free();
                $q = "insert into platforms set platform='$platform'";
                $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
                return $this->dbc->insert_id;
            }
        }
    }

    private function getBrowserID($browser)
    {
        if($browser == "")
        {
            return 0;
        }
        else
        {
            if(strlen($browser) > 32) $browser = substr($browser, 0, 31);
            $browser = $this->dbc->real_escape_string($browser);
            $q = "select browser_id from browsers where browser='$browser'";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
            if($r->num_rows)
            {
                $browser_id = $r->fetch_row()[0];
                $r->free();
                return $browser_id;
            }
            else
            {
                $r->free();
                $q = "insert into browsers set browser='$browser'";
                $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
                return $this->dbc->insert_id;
            }
        }
    }

    private function insertVisitor($cookie_id, $time, $session_time, $cookie_time, $screentype, $country, $agent_id, $ip_address, $user_id, $first_dom_id, $first_reflink_id, $platform_id, $browser_id, $testgroup)
    {
        $prefix = $this->table_prefix;
        $q = "insert into {$prefix}_visitors set
                cookie_id=$cookie_id,
                first_time=$cookie_time,
                last_time=$time,
                session_time=$session_time,
                screentype=$screentype,
                country='$country',
                agent_id=$agent_id,
                ip_address=$ip_address,
                user_id=$user_id,
                first_dom_id=$first_dom_id,
                first_reflink_id=$first_reflink_id,
                platform_id=$platform_id,
                browser_id=$browser_id,
                testgroup=$testgroup
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        return $this->dbc->insert_id;
    }

    private function updateVisitor($visitor_id, $cookie_id, $agent_id, $time, $session_time, $user_id, $flag, $testgroup)
    {
        $prefix = $this->table_prefix;
        if($time<$session_time) $time = $session_time;	//this is a kludge to fix the error of last_time-session_time going negative

        $q = "update {$prefix}_visitors set
                last_time=$time";
        if($cookie_id != 0) $q .= ", cookie_id=$cookie_id";
        if($agent_id != 0) $q .= ", agent_id=$agent_id";
        if($flag == "R") $q .= ", session_time=$session_time";
        if($user_id != 0) $q .= ", user_id=$user_id";
        $q .= ", testgroup=$testgroup where visitor_id=$visitor_id";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
    }

    private function addPageload($stat_id, $ip_address, $stime, $ctime, $ref_id, $dom_id, $pagename_id, $visitor_id, $site_id, $gallery_id, $link_id, $flag, $first_dom_id, $first_reflink_id, $testgroup)
    {
        $this->page_insert_columns = "stat_id, ip_address, stime, ctime, ref_id, dom_id, pagename_id, visitor_id, flag, site_id, gallery_id, hardlink_id, first_dom_id, first_reflink_id, testgroup";
        $this->page_insert_values[] = "$stat_id, $ip_address, $stime, $ctime, $ref_id, $dom_id, $pagename_id, $visitor_id, '$flag', $site_id, $gallery_id, $link_id, $first_dom_id, $first_reflink_id, $testgroup";
    }

    private function insertPageloadRows()
    {
        $prefix = $this->table_prefix;

        if(sizeof($this->page_insert_values))
        {
            $q = "insert into {$prefix}_pageloads ($this->page_insert_columns) values ";
            foreach($this->page_insert_values as $values)
            {
                $q .= "($values), ";
            }
            $q = substr($q, 0, -2);
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        }
    }

    public function updateReferrerStats()
    {
        $t = time() - TWENTYFOUR_HOURS;
        $prefix = $this->table_prefix;

        $tables = array();
        $q = "show tables";
        $r = $this->dbc->query($q) or die($this->dbc->error);
        while($row = $r->fetch_row())
        {
            $tables[] = $row[0];
        }
        $r->free();

        $table_name = $prefix . "_hardlinks";
        if(in_array($table_name, $tables))
        {
            $q = "update {$prefix}_hardlinks set
                    outs_24_hours=(select count(1) as cnt
                            from {$prefix}_pageloads
                            where stime>$t
                            and {$prefix}_pageloads.hardlink_id={$prefix}_hardlinks.hardlink_id)
                    where status>0
                    ";
            $r = $this->dbc->query($q) or die($this->dbc->error);
            $q = "update {$prefix}_hardlinks set
                    clicks_24_hours=(select count(1) as cnt
                                from {$prefix}_pageloads
                                where stime>$t
                                and {$prefix}_pageloads.first_reflink_id={$prefix}_hardlinks.ref_code)
                    where status>0
                    ";
            $r = $this->dbc->query($q) or die($this->dbc->error);
            $q = "update {$prefix}_hardlinks set
                    ins_24_hours=(select count(1) as cnt
                        from {$prefix}_pageloads
                        where stime>$t
                        and flag!='S'
                        and {$prefix}_pageloads.first_reflink_id={$prefix}_hardlinks.ref_code)
                    where status>0
                    ";
            $r = $this->dbc->query($q) or die($this->dbc->error);
        }
    }

    //*
    //* Daily stats update functions
    //*
    public function storeDailyStats()
    {
        $prefix = $this->table_prefix;
        $q = "select max(stat_time) from daily_stats where domain_id={$this->domain_vars->domain_id}";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);

        if($r->num_rows)
        {
            $last_stat_time = $r->fetch_row()[0];
            $r->close();
        }

        if(!$last_stat_time)
        {
            $q = "select min(stime) from {$prefix}_pageloads where 1";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
            $first_stat_time = $r->fetch_row()[0] ?? 0;
            $r->close();
            if($first_stat_time == 0) return;

            $gmt_td = (intval(date("O")) / 100) * 3600;                    //get difference between time zone and GMT
            $datearray = getdate($first_stat_time + $gmt_td);
            extract($datearray);
            $last_stat_time = gmmktime(0, 0, 0, $mon, $mday, $year);
            $q = "insert into daily_stats set
                    stat_time=$last_stat_time,
                    domain_id={$this->domain_vars->domain_id}
                    ";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        }
        $this->updateDailyStats($last_stat_time);
        $last_stat_time += TWENTYFOUR_HOURS;

        $q = "select max(stime) from {$prefix}_pageloads where 1";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        $last_stime = $r->fetch_row()[0];

        if($last_stat_time < $last_stime)
        {
            $q = "insert into daily_stats set
                    stat_time=$last_stat_time,
                    domain_id={$this->domain_vars->domain_id}
                    ";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
            $this->updateDailyStats($last_stat_time);
        }
    }

    private function updateDailyStats($stat_day)
    {
        $prefix = $this->table_prefix;
        $next_day = $stat_day + TWENTYFOUR_HOURS;

        $q = "select count(1) as cnt from {$prefix}_pageloads where flag!='C' and stime>=$stat_day and stime<$next_day";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        $page_loads = $r->fetch_row()[0];
        $q = "select count(distinct visitor_id) as cnt from {$prefix}_pageloads where stime>=$stat_day and stime<$next_day";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        $visitors = $r->fetch_row()[0];

        $q = "select count(1) as cnt from searches, {$prefix}_pageloads where searches.dom_id={$prefix}_pageloads.dom_id and (flag='N' or flag='R') and stime>=$stat_day and stime<$next_day";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        $searches = $r->fetch_row()[0];

        $tid = 0;
        $trackname = $this->domain_vars->se_tracking;
        if($trackname)
        {
            $q = "select dom_id from referdomains where domainstring='$trackname'";
            $r = $this->dbc->query($q) or die($this->dbc->error);
            if($r->num_rows)
            {
                $tid = $r->fetch_row()[0];
                $r->close();
            }
        }

        if(is_numeric($tid))
        {
            $q = "select count(1) as cnt from {$prefix}_pageloads where {$prefix}_pageloads.dom_id=$tid and (flag='N' or flag='R') and stime>=$stat_day and stime<$next_day";
            $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
            $se_tracked = $r->fetch_row()[0];
        }
        else
        {
            $se_tracked = 0;
        }
        $q = "select count(1) as cnt from {$prefix}_pageloads where flag='C' and stime>=$stat_day and stime<$next_day";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        $click_thrus = $r->fetch_row()[0];
        $page_loads += $click_thrus;

        $q = "update daily_stats set
                page_loads=$page_loads,
                visitors=$visitors,
                searches=$searches,
                click_thrus=$click_thrus,
                se_tracked=$se_tracked
                where
                stat_time=$stat_day
                and domain_id={$this->domain_vars->domain_id}
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
    }

    //*
    //* Functions to clear old data from the stats and pageloads tables
    //*

    public function clearStatsData()
    {
        $this->emptyStatTables();
        return $this->emptyStatsTable();
    }

    private function emptyStatsTable()
    {
        $prefix = $this->table_prefix;
        $stats_ttl = time() - SEVEN_DAYS;

        $q = "select max(stime) as stm from {$prefix}_pageloads";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        $stime = $r->fetch_row()[0] ?? 0;
        $r->close();

        if($stime < $stats_ttl) $stats_ttl = $stime;

        $q = "delete from {$prefix}_stats where stime<$stats_ttl";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        return $this->dbc->affected_rows;
    }

    private function emptyStatTables()
    {
        $prefix = $this->table_prefix;
        $domain_id = $this->domain_vars->domain_id;

        $q = "select max(stat_time) as mt from daily_stats where domain_id=$domain_id";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        $last_stat_time = $r->fetch_row()[0] ?? 0;
        $r->close();
        $visits_ttl = time() - SEVEN_DAYS;
        if($visits_ttl > $last_stat_time) $visits_ttl = $last_stat_time;

        $q = "delete from {$prefix}_pageloads where stime<$visits_ttl";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", the query was $q");
        $q = "delete from {$prefix}_visitors where last_time<$visits_ttl";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", the query was $q");
    }

    public function updateTradeHits()
    {
        $q = "select * from domains where `status`=1";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", the query was $q");
        while($row = $r->fetch_assoc())
        {
            $dn = $row["subdomain"] . $row["domain_name"];
            $prefix = $this->getTablesPrefix($dn);
            $this->updateTradeWeeklyHits($prefix);
        }

        $t = time();
        $q = "update domains set time_last_update=$t";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", the query was $q");
    }

	private function updateTradeWeeklyHits($prefix)
	{
	    $t = time() - SEVEN_DAYS;

		$q = "update {$prefix}_hardlinks set
				outs_7_days=(select count(1) as cnt
						from {$prefix}_pageloads
						where stime>$t
						and {$prefix}_pageloads.hardlink_id={$prefix}_hardlinks.hardlink_id)
				where status>0
				";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", the query was $q");
        $q = "update {$prefix}_hardlinks set
				clicks_7_days=(select count(1) as cnt
						from {$prefix}_pageloads
						where stime>$t
						and {$prefix}_pageloads.first_reflink_id={$prefix}_hardlinks.ref_code)
				where status>0
				";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", the query was $q");
        $q = "update {$prefix}_hardlinks set
				ins_7_days=(select count(1) as cnt
						from {$prefix}_pageloads
						where stime>$t
						and flag!='S'
						and {$prefix}_pageloads.first_reflink_id={$prefix}_hardlinks.ref_code)
				where status>0
				";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", the query was $q");
    }

    //reset the daily clicks and daily views in the video stats and post stats tables
    public function dailyUpdate()
    {
        $this->getTablesArray();

        $q = "select * from domains where `status`=1";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        if(!$r->num_rows) return;

        while($row = $r->fetch_assoc())
        {
            $dn = $row["subdomain"] . $row["domain_name"];
            $prefix = $this->getTablesPrefix($dn);

            $table_name = $prefix . "_post_stats";
            if(in_array($table_name, $this->tables))
            {
                $q1 = "update $table_name set daily_clicks=0, daily_views=0, daily_prod=1000";
                $r1 = $this->dbc->query($q1) or die($this->dbc->error . ", query was $q1 in " . __FILE__ . " at " . __LINE__);
            }
        }
    }
}
