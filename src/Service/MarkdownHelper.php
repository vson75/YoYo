<?php


namespace App\Service;


use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class MarkdownHelper
{
    private $cache;
    private $markdown;
    public function __construct(AdapterInterface $cache, MarkdownParserInterface $markdown)
    {
        $this->cache = $cache;
        $this->markdown = $markdown;
    }

    public function parse(string $source):string
    {
        $item = $this->cache->getItem('markdown_'.md5($source));

        // check if the requested item is found in the cache. To check it, we use methode isHit()
        if (!$item->isHit()){
            $sourceContent = $this->markdown->transformMarkdown($source);
            $item->set($sourceContent);
            $this->cache->save($item);
        }
        return $item->get();
    }
}