<?php
class DomDocumentParser
{
	//class variable to store info
	private $doc;

	// constructor is the first peice of code called when create object
	// public so can call outside class

	public function __construct($url)
	{
		// echo "URL: $url";
		//options used when requesting page
		//method with which retreive data with --> GET
		//user-agent is who visited the website
		$options = array(
			'http' => array('method' => "GET", 'header' => "User-Agent: google_duck_test/0.1\n")
		);
		$context = stream_context_create($options);
		$this->doc = new DomDocument();
		//@ supresses warnings
		//warnings have to do with html5
		@$this->doc->loadHTML(file_get_contents($url, false, $context));
	}

	public function getlinks()
	{
		// doc is of type DomDocument
		return $this->doc->getElementsByTagName("a");
	}

	public function getTitleTags()
	{
		return $this->doc->getElementsByTagName("title");
	}
	public function getMetaTags()
	{
		return $this->doc->getElementsByTagName("meta");
	}

	public function getImages()
	{
		return $this->doc->getElementsByTagName("img");
	}
}
