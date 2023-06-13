<?php
    require_once(DB_PATH . "stats.db.class.php");

//class to provide functions that analyse the page data
class AnalyticsDB extends StatsDB
{
    //function to count data for the summary stats
    public function countStats(string $type) : int
    {
        $prefix = $this->table_prefix;
        switch($type) {
            case "new":
                $q = "select count(1) as cnt from {$prefix}_pageloads where flag='N'" . $this->groupclause . " and stime" . $this->dateclause;
                break;
            case "return":
                $q = "select count(1) as cnt from {$prefix}_pageloads where flag='R'" . $this->groupclause . " and stime" . $this->dateclause;
                break;
            case "uniques":
                $q = "select count(distinct visitor_id) as cnt from {$prefix}_pageloads where (flag='N' OR flag='R')" . $this->groupclause . " and stime" . $this->dateclause;
                break;
            case "pageloads":
                $q = "select count(1) as cnt from {$prefix}_pageloads where stime" . $this->dateclause . $this->groupclause;
                break;
            case "clickthrus":
                $q = "select count(1) as cnt from {$prefix}_pageloads where flag='C' and stime" . $this->dateclause . $this->groupclause;
                break;
            case "trades":
                $q = "select count(1) as cnt from {$prefix}_pageloads where hardlink_id!=0 and stime" . $this->dateclause . $this->groupclause;
                break;
            case "sponsors":
                $q = "select count(1) as cnt from {$prefix}_pageloads where site_id!=0 and stime" . $this->dateclause . $this->groupclause;
                break;
            case "bad":
                $q = "select count(1) as cnt from {$prefix}_pageloads where flag='B' and stime" . $this->dateclause . $this->groupclause;
                break;
            case "searches":
                $q = "select count(1) as cnt from searches, {$prefix}_pageloads where
                    searches.dom_id={$prefix}_pageloads.dom_id and (flag='N' or flag='R') and stime" . $this->dateclause . $this->groupclause;
                break;
            case "tracked":
                if(!isset($_SESSION["se_track_id"]) || !is_numeric($_SESSION["se_track_id"])) return 0;
                $tid = $_SESSION["se_track_id"];
                if(!$tid) return 0;
                $q = "select count(1) as cnt from {$prefix}_pageloads where
                    {$prefix}_pageloads.dom_id=$tid and (flag='N' or flag='R') and stime" . $this->dateclause . $this->groupclause;
                break;
            case "bing":
                if(!isset($_SESSION["se_bing_id"]) || !is_numeric($_SESSION["se_bing_id"])) return 0;
                $tid = $_SESSION["se_bing_id"];
                $q = "select count(1) as cnt from {$prefix}_pageloads where
                    {$prefix}_pageloads.dom_id=$tid and (flag='N' or flag='R') and stime" . $this->dateclause . $this->groupclause;
                break;
            case "google":
                if(!isset($_SESSION["se_google_id"]) || !is_numeric($_SESSION["se_google_id"])) return 0;
                $tid = $_SESSION["se_google_id"];
                $q = "select count(1) as cnt from {$prefix}_pageloads where
                    {$prefix}_pageloads.dom_id=$tid and (flag='N' or flag='R') and stime" . $this->dateclause . $this->groupclause;
                break;
            case "pagetime":
                $q = "select avg(pgld) as pageloadtime from 
                (select ctime-stime as pgld from {$prefix}_pageloads where ctime>=stime and stime" . $this->dateclause . $this->groupclause . ") as pgld";
                break;
            case "regs":
                $q = "select count(1) as cnt from users where time_registered" . $this->dateclause;
                break;
            case "logins":
                $q = "select count(1) as cnt from users where time_last_login" . $this->dateclause;
                break;
            default:
                return -999;
        }
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $count = $r->fetch_row()[0];
        if(!$count) $count = 0;
        $r->free();
		return $count;
    }

    public function fetchReferrerData() : int
    {
        $prefix = $this->table_prefix;
        $pgroupclause = $this->pgroupclause;
        $daterange = $this->daterange;

        $q = "select
            count(1) as co,
            referdomains.dom_id
            from
            {$prefix}_pageloads
            left join referdomains on {$prefix}_pageloads.dom_id=referdomains.dom_id
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R') and
            {$prefix}_visitors.session_time={$prefix}_visitors.last_time
            $pgroupclause
            group by referdomains.dom_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $a = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $a["id-$dom_id"] = $co;
        }
        $r->close();

        $q = "select
            count(1) as co,
            vi.dom_id
            from
            (select
                {$prefix}_pageloads.stime,
                {$prefix}_visitors.visitor_id,
                referdomains.dom_id
                from
                {$prefix}_pageloads
                left join referdomains on {$prefix}_pageloads.dom_id=referdomains.dom_id
                left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
                where
                {$prefix}_pageloads.stime>=$daterange and
                ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
                $pgroupclause
                ) as vi
            left join {$prefix}_pageloads on {$prefix}_pageloads.visitor_id=vi.visitor_id
            where {$prefix}_pageloads.stime>=vi.stime and {$prefix}_pageloads.flag='C'
            group by vi.dom_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $b = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $b["id-$dom_id"] = $co;
        }
        $r->close();

        $q = "select
            count(1) as tot,
            referdomains.dom_id
            from
            {$prefix}_pageloads
            left join referdomains on {$prefix}_pageloads.dom_id=referdomains.dom_id
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            {$prefix}_pageloads.flag='R'
            $pgroupclause
            group by referdomains.dom_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $v = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $v["id-$dom_id"] = $tot;
        }
        $r->close();

        $q = "select
            sum({$prefix}_visitors.last_time-{$prefix}_visitors.session_time) as timeonsite,
            referdomains.dom_id
            from
            {$prefix}_pageloads
            left join referdomains on {$prefix}_pageloads.dom_id=referdomains.dom_id
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            {$prefix}_visitors.session_time>0 and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by referdomains.dom_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $s = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $s["id-$dom_id"] = $timeonsite;
        }
        $r->close();

        $q = "select
            count(1) as tot,
            max(stime) as maxstime,
            referdomains.dom_id,
            referdomains.domainstring,
            (select count(1) from searches where searches.dom_id=referdomains.dom_id) as srch
            from
            {$prefix}_pageloads
            left join referdomains on {$prefix}_pageloads.dom_id=referdomains.dom_id
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            {$prefix}_visitors.session_time>0 and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by referdomains.dom_id
            order by tot desc, maxstime desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

        $rownum = 1;
        while($row = $r->fetch_assoc()) {
            extract($row);

            $co = $a["id-$dom_id"] ?? 0;
            $ct = $b["id-$dom_id"] ?? 0;
            $rv = $v["id-$dom_id"] ?? 0;
            $tos = $s["id-$dom_id"] ?? 0;
            $t = floor($tos / $tot);
            if($rv == "") $rv = 0;
            if($dom_id == "") $dom_id = 0;
            $pc = (floor(($co * 1000) / $tot)) / 10;
            $prod = (floor(($ct * 1000) / $tot)) / 10;
            $this->results_rows[] = compact("rownum", "dom_id", "domainstring", "srch", "tot", "prod", "tos", "rv", "co", "ct", "pc", "t");
            $rownum++;
        }
        $r->close();
        return sizeof($this->results_rows);
    }

    public function fetchEntryPagesData() : int
    {
        $prefix = $this->table_prefix;
        $pgroupclause = $this->pgroupclause;
        $daterange = $this->daterange;

        $q = "select
            count(1) as co,
            {$prefix}_pagenames.pagename_id,
            {$prefix}_pagenames.pagename
            from
            {$prefix}_pageloads
            left join {$prefix}_pagenames on {$prefix}_pageloads.pagename_id={$prefix}_pagenames.pagename_id
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R') and
            {$prefix}_visitors.session_time={$prefix}_visitors.last_time
            $pgroupclause
            group by {$prefix}_pagenames.pagename_id
            order by co desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $a = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $a["id-$pagename_id"] = $co;
        }
        $r->close();
        $q = "select
            count(1) as tot,
            max({$prefix}_pageloads.stime) as maxstime,
            {$prefix}_pagenames.pagename_id,
            {$prefix}_pagenames.pagename
            from
            {$prefix}_pageloads
            left join {$prefix}_pagenames on {$prefix}_pageloads.pagename_id={$prefix}_pagenames.pagename_id
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by {$prefix}_pagenames.pagename_id
            order by tot desc, maxstime desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            extract($row);
            $co = $a["id-$pagename_id"] ?? 0;
            $pc = (floor(($co * 1000) / $tot)) / 10;
            $this->results_rows[] = compact("pagename", "pagename_id", "tot", "co", "pc");
        }
        $r->close();
        return sizeof($this->results_rows);
    }

    public function fetchPagesData() : int
    {
        $prefix = $this->table_prefix;
        $groupclause = $this->groupclause;
        $daterange = $this->daterange;
        $q = "select
                max(stime) as maxstime,
                pagename,
                {$prefix}_pagenames.pagename_id,
                count(1) as cnt
                from {$prefix}_pagenames
                left join {$prefix}_pageloads on {$prefix}_pageloads.pagename_id={$prefix}_pagenames.pagename_id
                where
                {$prefix}_pageloads.flag!='B'
                and {$prefix}_pageloads.stime>=$daterange
                $groupclause
                group by pagename_id
                order by cnt desc, maxstime desc";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

        while($row = $r->fetch_assoc()) {
            $this->results_rows[] = $row;
        }
        $r->close();
        return sizeof($this->results_rows);
    }

    public function fetchPageCTRData() : int
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
        $groupclause = $this->groupclause;

        $click_thrus = [];

        $q = "select
                referstring,
                {$prefix}_pageloads.ref_id,
                count(1) as cnt
                from
                {$prefix}_pageloads
                left join {$prefix}_referstrings on {$prefix}_referstrings.ref_id={$prefix}_pageloads.ref_id
                where
                {$prefix}_pageloads.flag='C'
                and {$prefix}_pageloads.stime>=$daterange
                group by {$prefix}_pageloads.ref_id
                order by cnt desc
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            extract($row);
            $q2 = "select
                    count(1) as cnt
                    from
                    {$prefix}_pageloads
                    left join {$prefix}_pagenames on {$prefix}_pagenames.pagename_id={$prefix}_pageloads.pagename_id
                    where
                    {$prefix}_pageloads.stime>=$daterange
                    and pagename='$referstring'
                    $groupclause
                    ";
            $r2 = $this->dbc->query($q2) or die($this->dbc->error . " query was $q2 in " . __FILE__ . " at line " . __LINE__);
            $hits = $r2->fetch_row()[0];
            $click_thrus[$ref_id] = array("referstring"=>$referstring, "total_clicks"=>$cnt, "page_hits"=>$hits);
            $r2->close();
        }
        $r->close();
        $q = "select
                {$prefix}_pageloads.ref_id,
                pagename,
                count(1) as cnt
                from
                {$prefix}_pageloads
                left join {$prefix}_pagenames on {$prefix}_pagenames.pagename_id={$prefix}_pageloads.pagename_id
                left join {$prefix}_referstrings on {$prefix}_referstrings.ref_id={$prefix}_pageloads.ref_id
                where
                {$prefix}_pageloads.flag='C'
                and {$prefix}_pageloads.stime>=$daterange
                $groupclause
                group by ref_id, pagename
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            extract($row);
            if(substr($pagename, 0, 1) == "@") $pagename = substr($pagename, 1);
            $click_thrus[$ref_id][$pagename] = $cnt;
        }
        $r->close();

        foreach($click_thrus as $row) {
            $this->results_rows[] = $row;
        }
        return sizeof($this->results_rows);
    }

    public function fetchSiteHitsData() : int
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;

        $q = "select
        {$prefix}_sites.site_id,
            site_name,
            count(1) as site_hits
            from
            {$prefix}_sites
            left join {$prefix}_pageloads on {$prefix}_pageloads.site_id={$prefix}_sites.site_id
            where
            {$prefix}_pageloads.stime>=$daterange
            group by site_id
            order by site_hits desc, site_name
            ";

        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            $this->results_rows[] = $row;
        }
        $r->close();
        return sizeof($this->results_rows);
    }

    public function fetchSearchHitsData() : int
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;

        $q = "select
        count(1) as bounces,
        {$prefix}_pagenames.pagename_id,
        {$prefix}_pagenames.pagename
        from
        searches
        left join {$prefix}_pageloads on {$prefix}_pageloads.dom_id=searches.dom_id
        left join {$prefix}_pagenames on {$prefix}_pageloads.pagename_id={$prefix}_pagenames.pagename_id
        left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
        where
        {$prefix}_pageloads.stime>=$daterange and
        ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R') and
        {$prefix}_visitors.session_time={$prefix}_visitors.last_time
        group by {$prefix}_pagenames.pagename_id
        ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $a = array();
        while($row = $r->fetch_assoc())
        {
            extract($row);
            $a["id-$pagename_id"] = $bounces;
        }
        $r->close();
        $q = "select
            count(1) as page_hits,
            max({$prefix}_pageloads.stime) as maxstime,
            {$prefix}_pagenames.pagename_id,
            {$prefix}_pagenames.pagename
            from
            searches
            left join {$prefix}_pageloads on {$prefix}_pageloads.dom_id=searches.dom_id
            left join {$prefix}_pagenames on {$prefix}_pageloads.pagename_id={$prefix}_pagenames.pagename_id
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            group by {$prefix}_pagenames.pagename_id
            order by page_hits desc, maxstime desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            extract($row);
            $bounces = $a["id-$pagename_id"] ?? 0;
            $bounce_rate = (floor(($bounces * 1000) / $page_hits)) / 10;
            $this->results_rows[] = compact("pagename", "pagename_id", "page_hits", "bounces", "bounce_rate");
        }
        $r->close();
        return sizeof($this->results_rows);
    }

    public function fetchInternalPagesData(string $domain_name) : int
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
        $groupclause = $this->groupclause;

        $q = "select dom_id from referdomains where domainstring='$domain_name'";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $dom_id = $r->fetch_row()[0] ?? 0;
        $r->close();
        $q = "select
            referstring,
            pagename,
            count(1) as page_hits
            from
            {$prefix}_pageloads
            left join {$prefix}_pagenames on {$prefix}_pagenames.pagename_id={$prefix}_pageloads.pagename_id
            left join {$prefix}_referstrings on {$prefix}_referstrings.ref_id={$prefix}_pageloads.ref_id
            where
            {$prefix}_pageloads.stime>=$daterange
            and {$prefix}_pageloads.dom_id=$dom_id
            and (flag!='B' AND flag!='C')
            $groupclause
            group by referstring, pagename
            order by referstring, page_hits desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            $this->results_rows[] = $row;
        }
        $r->close();
        return sizeof($this->results_rows);
    }

    public function fetchSitePagesData() : int
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
        $groupclause = $this->groupclause;

        $q = "select
            site_name,
            referstring,
            count(1) as site_hits
            from
            {$prefix}_sites
            left join {$prefix}_pageloads on {$prefix}_pageloads.site_id={$prefix}_sites.site_id
            left join {$prefix}_referstrings on {$prefix}_referstrings.ref_id={$prefix}_pageloads.ref_id
            where
            {$prefix}_pageloads.stime>=$daterange
            $groupclause
            group by referstring, site_name
            order by referstring, site_hits desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            $this->results_rows[] = $row;
        }
        $r->close();
        return sizeof($this->results_rows);
    }

    public function fetchPlatformProdData() : int
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
        $pgroupclause = $this->pgroupclause;

        $q = "select
            count(1) as bounce_count,
            platforms.platform_id,
            platforms.platform
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join platforms on {$prefix}_visitors.platform_id=platforms.platform_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R') and
            {$prefix}_visitors.session_time={$prefix}_visitors.last_time
            $pgroupclause
            group by platforms.platform_id
            order by bounce_count desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $bounces = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $bounces["id-$platform_id"] = $bounce_count;
        }
        $r->close();

        $q = "select
            count(1) as click_count,
            visitor.platform_id
            from
            (select
                {$prefix}_pageloads.stime,
                {$prefix}_visitors.visitor_id,
                platforms.platform_id
                from
                {$prefix}_pageloads
                left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
                left join platforms on {$prefix}_visitors.platform_id=platforms.platform_id
                where
                {$prefix}_pageloads.stime>=$daterange and
                ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
                $pgroupclause
                ) as visitor
            left join {$prefix}_pageloads on {$prefix}_pageloads.visitor_id=visitor.visitor_id
            where {$prefix}_pageloads.stime>=visitor.stime
            and flag='C'
            group by visitor.platform_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $clickthrus = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $clickthrus["id-$platform_id"] = $click_count;
        }
        $r->close();

        $q = "select
            count(1) as sponsor_clicks,
            visitor.platform_id
            from
            (select
                {$prefix}_pageloads.stime,
                {$prefix}_visitors.visitor_id,
                platforms.platform_id
                from
                {$prefix}_pageloads
                left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
                left join platforms on {$prefix}_visitors.platform_id=platforms.platform_id
                where
                {$prefix}_pageloads.stime>=$daterange and
                ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
                $pgroupclause
                ) as visitor
            left join {$prefix}_pageloads on {$prefix}_pageloads.visitor_id=visitor.visitor_id
            where {$prefix}_pageloads.stime>=visitor.stime
            and {$prefix}_pageloads.site_id!=0
            group by visitor.platform_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $sponsorclicks = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $sponsorclicks["id-$platform_id"] = $sponsor_clicks;
        }
        $r->close();

        $q = "select
            sum({$prefix}_visitors.last_time-{$prefix}_visitors.session_time) as time_on_site,
            platforms.platform_id
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join platforms on {$prefix}_visitors.platform_id=platforms.platform_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            {$prefix}_visitors.session_time>0 and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by platforms.platform_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $timeonsite = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $timeonsite["id-$platform_id"] = $time_on_site;
        }
        $r->close();

        $q = "select
            count(1) as hit_count,
            platforms.platform_id,
            platforms.platform
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join platforms on {$prefix}_visitors.platform_id=platforms.platform_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by platforms.platform_id
            order by hit_count desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            extract($row);
            $bounce_count = $bounces["id-$platform_id"] ?? 0;
            $click_count = $clickthrus["id-$platform_id"] ?? 0;
            $time_on_site = $timeonsite["id-$platform_id"] ?? 0;
            $sponsor_clicks = $sponsorclicks["id-$platform_id"] ?? 0;

            $this->results_rows[] = compact("platform", "hit_count", "bounce_count", "click_count", "time_on_site", "sponsor_clicks");
        }
        $r->close();
        return sizeof($this->results_rows);
    }

    public function fetchUseragentProdData() : int
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
        $pgroupclause = $this->pgroupclause;

        $q = "select
            count(1) as bounce_count,
            browsers.browser_id,
            browsers.browser
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R') and
            {$prefix}_visitors.session_time={$prefix}_visitors.last_time
            $pgroupclause
            group by browsers.browser_id
            order by bounce_count desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $bounces = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $bounces["id-$browser_id"] = $bounce_count;
        }
        $r->close();

        $q = "select
            count(1) as click_count,
            visitor.browser_id
            from
            (select
                {$prefix}_pageloads.stime,
                {$prefix}_visitors.visitor_id,
                browsers.browser_id
                from
                {$prefix}_pageloads
                left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
                left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
                where
                {$prefix}_pageloads.stime>=$daterange and
                ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
                $pgroupclause
                ) as visitor
                left join {$prefix}_pageloads on {$prefix}_pageloads.visitor_id=visitor.visitor_id
                where {$prefix}_pageloads.stime>=visitor.stime
                and flag='C'
                group by visitor.browser_id
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $clickthrus = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $clickthrus["id-$browser_id"] = $click_count;
        }
        $r->close();

        $q = "select
            count(1) as sponsor_clicks,
            visitor.browser_id
            from
            (select
                {$prefix}_pageloads.stime,
                {$prefix}_visitors.visitor_id,
                browsers.browser_id
                from
                {$prefix}_pageloads
                left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
                left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
                where
                {$prefix}_pageloads.stime>=$daterange and
                ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
                $pgroupclause
                ) as visitor
            left join {$prefix}_pageloads on {$prefix}_pageloads.visitor_id=visitor.visitor_id
            where {$prefix}_pageloads.stime>=visitor.stime
            and {$prefix}_pageloads.site_id!=0
            group by visitor.browser_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $sponsorclicks = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $sponsorclicks["id-$browser_id"] = $sponsor_clicks;
        }
        $r->close();

        $q = "select
            sum({$prefix}_visitors.last_time-{$prefix}_visitors.session_time) as time_on_site,
            browsers.browser_id
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            {$prefix}_visitors.session_time>0 and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by browsers.browser_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $timeonsite = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $timeonsite["id-$browser_id"] = $time_on_site;
        }
        $r->close();

        $q = "select
            count(1) as hit_count,
            browsers.browser_id,
            browsers.browser
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by browsers.browser_id
            order by hit_count desc
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $results = array();
        while($row = $r->fetch_assoc()) {
            extract($row);
            $bounce_count = $bounces["id-$browser_id"] ?? 0;
            $click_count = $clickthrus["id-$browser_id"] ?? 0;
            $time_on_site = $timeonsite["id-$browser_id"] ?? 0;
            $sponsor_clicks = $sponsorclicks["id-$browser_id"] ?? 0;

            $br = explode("/", $browser);
            $browser_name = $br[0];
            if(!isset($results[$browser_name])) $results[$browser_name] = array("hits"=>0, "clickthrus"=>0, "timeonsite"=>0, "bounces"=>0, "sponsorclicks"=>0);
            $results[$browser_name]["hits"] += $hit_count;
            $results[$browser_name]["clickthrus"] += $click_count;
            $results[$browser_name]["timeonsite"] += $time_on_site;
            $results[$browser_name]["bounces"] += $bounce_count;
            $results[$browser_name]["sponsorclicks"] += $sponsor_clicks;
        }
        $r->close();
        foreach($results as $key => $row) {
            $hit_count = $row["hits"];
            $bounce_count = $row["bounces"];
            $click_count = $row["clickthrus"];
            $time_on_site = $row["timeonsite"];
            $sponsor_clicks = $row["sponsorclicks"];
            $browser = $key;    //just for clarity

            $this->results_rows[] = compact("browser", "hit_count", "bounce_count", "click_count", "time_on_site", "sponsor_clicks");
        }
        return sizeof($this->results_rows);
    }

    public function fetchBrowserProdData() : int
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
        $pgroupclause = $this->pgroupclause;

        $q = "select
            count(1) as bounce_count,
            browsers.browser_id,
            browsers.browser
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R') and
            {$prefix}_visitors.session_time={$prefix}_visitors.last_time
            $pgroupclause
            group by browsers.browser_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $bounces = array();
        while($row = $r->fetch_assoc())
        {
            extract($row);
            $bounces["id-$browser_id"] = $bounce_count;
        }
        $r->close();

        $q = "select
            count(1) as click_count,
            visitor.browser_id
            from
            (select
                {$prefix}_pageloads.stime,
                {$prefix}_visitors.visitor_id,
                browsers.browser_id
                from
                {$prefix}_pageloads
                left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
                left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
                where
                {$prefix}_pageloads.stime>=$daterange and
                ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
                $pgroupclause
                ) as visitor
                left join {$prefix}_pageloads on {$prefix}_pageloads.visitor_id=visitor.visitor_id
                where {$prefix}_pageloads.stime>=visitor.stime
                and flag='C'
                group by visitor.browser_id
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $clickthrus = array();
        while($row = $r->fetch_assoc())
        {
            extract($row);
            $clickthrus["id-$browser_id"] = $click_count;
        }
        $r->close();

        $q = "select
            sum({$prefix}_visitors.last_time-{$prefix}_visitors.session_time) as time_on_site,
            browsers.browser_id
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            {$prefix}_visitors.session_time>0 and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by browsers.browser_id
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $timeonsite = array();
        while($row = $r->fetch_assoc())
        {
            extract($row);
            $timeonsite["id-$browser_id"] = $time_on_site;
        }
        $r->close();

        $q = "select
            count(1) as hit_count,
            browsers.browser_id,
            browsers.browser
            from
            {$prefix}_pageloads
            left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
            left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
            where
            {$prefix}_pageloads.stime>=$daterange and
            ({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
            $pgroupclause
            group by browsers.browser_id
            order by browser
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        
        while($row = $r->fetch_assoc())
        {
            extract($row);
            $bounce_count = $bounces["id-$browser_id"] ?? 0;
            $click_count = $clickthrus["id-$browser_id"] ?? 0;
            $time_on_site = $timeonsite["id-$browser_id"] ?? 0;

            $this->results_rows[] = compact("browser", "hit_count", "bounce_count", "click_count", "time_on_site");
        }
        $r->close();
        return sizeof($this->results_rows);
    }
}