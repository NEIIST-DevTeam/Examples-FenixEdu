<?php
class Controller {
    private $beforehead;
    private $head;
    private $beforecontent;
    private $aftercontent;

    //TODO change this to allow user tags for better templating
    public function __construct($filename) {
        $this->loadTemplate($filename, "<!-- @{CONTENT} -->");
    }

    private function loadTemplate($fileName, $contentTag) {
        $file=fopen($fileName,"r") or die ("Failed to open template!");
	    $step = 0;
        $text=fread($file,filesize($fileName));
        fclose($file);
        $exp = explode("<head>", $text, 2);
        if(count($exp) < 2) die("Invalid template: Missing head!");
        $this->beforehead = $exp[0];
        $text = $exp[1];
        $exp = explode("</head>", $text, 2);
        if(count($exp) < 2) die("Invalid template: Incomplete head!");
        $this->head = $exp[0];
        $text = $exp[1];
        $exp = explode($contentTag, $text, 2);
        if(count($exp) < 2) die("Invalid template: Missing content tag!");
        $this->beforecontent = $exp[0];
        $this->aftercontent = $exp[1];
    }

    public function getPageTop() {
        return $this->beforehead . "<head>" . $this->head . "</head>" . $this->beforecontent . "\n<!-- Begin content -->\n";
    }

    public function printPageTop() {
        echo($this->getPageTop());
    }

    public function getPageBottom() {
        return "<!-- End content -->\n" . $this->aftercontent;
    }

    public function printPageBottom() {
        echo($this->getPageBottom());
    }

    public function addScript($filename) {
        $this->head = $this->head . '<script type="text/javascript" src="' . $filename . '"></script>';
    }

    public function addCSS($filename) {
        $this->head = $this->head . '<link rel="stylesheet" type="text/css" href="' . $filename . '" />';
    }

    public function addToHead($element) {
        $this->head = $this->head . $element;
    }

    public function setTitle($title) {
        $exp = explode("<title>", $this->head, 2);
        if(count($exp) < 2) {
            $this->head = $this->head . "<title>" . $title . "</title>";
        } else {
            $before = $exp[0];
            $exp = explode("</title>", $exp[1]);
            $after = $exp[1];
            $this->head = $before . "<title>" . $title . "</title>" . $after;
        }
    }
}
?>
