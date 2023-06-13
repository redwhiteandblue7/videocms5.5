<?php
    require_once(OBJECTS_PATH . 'page.class.php');
    require_once(OBJECTS_PATH . 'domain.class.php');

class Controller
{
    private $uri;
    private $pagename;
    private $page;
    private $domain;

    public function __construct(array $uri, string $domain_name)
    {
        $this->uri = $uri;
        $this->domain = new Domain();
        if($this->domain->domain_name != $domain_name) {
            //domain doesn't exist so all we can do is send a 404 header
            header('HTTP/1.0 404 Not Found');
            exit();
        } elseif(!$this->domain->isVisible()) {
            //if domain is set to invisible we will display the under construction page if there is one
            $this->pagename = "underconstruction.html";
            return;
        } else {
            //the domain exists and is visible so now we need to work out what page to display

            //how many parts are there to the uri?
            $uri_size = sizeof($uri);
            if($uri_size == 1) {
                //no directory just the page name
                $pagename = $uri[0];
                if(preg_match("/^page[0-9]+\\.html$/", $pagename)) $pagename = "index.html";
            } elseif($uri[0] == "api") {
                //the api is being called
                $pagename = "api_handler.html";
            } elseif($uri_size == 2) {
                //one directory so figure out if it's a category or a post index
                $pagename = $uri[1];
                if(preg_match("/^page[0-9]+\\.html$/", $pagename)) $pagename = "index.html";
                $pagename = $uri[0] . "/" . $pagename;
                //this is for if we want to use post indexes in the url, not all domains will need this
                if(is_numeric($uri[0])) $pagename = "post.html";
            } else {
                //the only thing allowed here is the "visit" outbound link redirect, anything else is 404
                if(($uri[0] == "visit") && ($uri[2] == "")) {
                    $pagename = "visit_handler.html";
                } else {
                    //every possible page type has been tried and failed so we will display the 404 page if there is one
                    $pagename = "error.html";
                }
            }
        }
        $this->pagename = $pagename;
    }

    public function initPage()
    {
        $this->page = new Page();
        if($this->pagename == "visit_handler.html" || $this->pagename == "api_handler.html") {
            //we don't need to get the page from the database for the visit or api handler
            return;
        }
        $this->page->getPage($this->pagename);
        if(!$this->page->page_id) {
            //the page doesn't exist so we try something else, unless we were already trying to get the error page
            if($this->pagename == "error.html") {
                return;
            }

            if(sizeof($this->uri) == 2) {
                //if we get here we have more than one part to the uri so we will try to find a wildcard page that handles the second part as a page slug
                $this->page->getPage($this->uri[0] . "/*.html");
            }

            if(!$this->page->page_id) {
                //no luck so try to get the error page
                $this->page->getPage("error.html");
            }
        }
    }

    public function writePage()
    {
        $prefix = $this->domain->prefix();

        if($this->pagename == "api_handler.html") {
            $page_filename = $this->uri[1] . ".api.php";
            $path = INCLUDE_PATH . "domains/$prefix/apis/$page_filename";
            $class_name = ucfirst(substr($page_filename, 0, strpos($page_filename, "."))) . "Api";
            if(file_exists($path)) {
                require($path);
                $page_content = new $class_name();
            } else {
                //the api is in the database but the class file doesn't exist so we will use the default api class
                require(INCLUDE_PATH . "domains/$prefix/apis/default.api.php");
                $page_content = new DefaultApi();
            }
        } else {
            if(!$this->page->page_id) {
                //if whatever page we tried to get doesn't exist nothing we can do except send 404 header
                header('HTTP/1.0 404 Not Found');
                exit();
            }
    
            //if the page is set to redirect we will do that now
            if($dest_url = $this->page->vars()->dest_url) {
                if($dest_url == "410") {
                    header("HTTP/1.1 410 Gone");
                    exit();
                }
    
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: {$dest_url}");
                exit();
            }

            $page_filename = $this->page->vars()->page_filename;
            $path = INCLUDE_PATH . "domains/$prefix/$page_filename";
            $class_name = ucfirst(substr($page_filename, 0, strpos($page_filename, "."))) . "Page";

            //these class files are named ".html.php" to maintain compatibility with old code even though they are not html pages, 
            //but they do contain the class to build the html
            if(file_exists($path)) {
                require($path);
                $page_content = new $class_name();
            } else {
                //the page is in the database but the class file doesn't exist so we will use the default page class
                require(INCLUDE_PATH . "domains/defaultpage.html.php");
                $page_content = new DefaultPage();
            }
        }
        //pass in the objects and array the page will need
        $page_content->domain = $this->domain;
        $page_content->page = $this->page;
        $page_content->uri = $this->uri;
        $page_content->init();
        $page_content->process();
        $page_content->render();
    }
}