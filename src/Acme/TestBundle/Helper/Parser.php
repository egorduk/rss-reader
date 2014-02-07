<?php

namespace Acme\TestBundle\Helper;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Parser {

	public function getText($data)
    {
		$dom = new \DOMDocument;
        $dom->strictErrorChecking = false;
		///$DOMDocument->loadXML($rss);
		//$this->document = $this->extractDOM($DOMDocument->childNodes);
        $dom->loadHTML($data);
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->evaluate('//table[@class="table01"]');
        //var_dump($dom->saveXML($nodes->item(0)));
        return $dom->saveXML($nodes->item(0));
    }
	

	
}

?>