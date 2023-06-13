<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

//Class to get a screenshot of a website and save it to the screenshots folder on server
class ScreenshotGet extends ApiAction
{
    public function process() : bool
    {
        if(isset($this->get_vars->url))
        {
            $url = $this->get_vars->url;
            $encoded = urlencode($url);
            $api = str_replace("_SITEURL_", $encoded, SCREENSHOT_API);

            $domainname = parse_url($url, PHP_URL_HOST);
            if(substr($domainname, 0, 4) == "www.") $domainname = substr($domainname, 4);
            $domainname = str_replace(".", "-", $domainname) . "-" . time();
//            $date = date("d-M-y-H-i-s");
            $filename = "images/screenshots/$domainname.png";
            $filepath = $this->dbo->domain_vars->public_path . $filename;
            $filename = "/" . $filename;

            $ch = curl_init($api);
            $fp = fopen($filepath, "w");

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

            curl_exec($ch);
            if(curl_error($ch)) {
//                fwrite($fp, curl_error($ch));
                $this->return_text = "Error: " . curl_error($ch);
            } else {
                $this->return_text = $filename;
            }
            curl_close($ch);
            fclose($fp);

            if(!getimagesize($filepath)) {
                $this->return_text = "Error: Screenshot API returned a non-image";
            }

//            $image = file_get_contents($api);
//            file_put_contents($filepath, $image);
//            $this->return_text = $filename;
        }
        return true;
    }
}