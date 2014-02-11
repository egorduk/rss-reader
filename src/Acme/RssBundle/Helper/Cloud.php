<?php

namespace Acme\RssBundle\Helper;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Cloud {

    protected $countFilterWord = 0;

	public function filterStr($str)
    {
        $arrayDeleteWord = array(' in ',
            ' the ',
            ' a ',
            ' and ',
            ' or ',
            ' on ',
            ' no ',
            ' not ',
            ' of ',
            ' at ' ,
            ' an ',
            ' to ',
            ' by ',
            ' with ',
            ' is ',
            ' are ',
            ' as ',
            ' its ',
            ' us ',
            ' we ',
            ' he ',
            ' she ',
            ' all ',
            ' have ',
            ' has ',
            ' but ',
            ' more ',
            ' for ',
            ' up ',
            '$',
            ' it ');

        $arrayDeletePunct = array(' - ', ',', '.', ':', ' -- ', ' ... ', '!', ';');

        //$str = preg_replace("/[[:punct:]]/", " ", $str);
        $str = preg_replace("/[[:digit:]^]/", " ", $str);
        $str = str_replace($arrayDeletePunct, " ", $str);
        $str = str_ireplace($arrayDeleteWord, " ", $str);
        $str = preg_replace("/\s[A-Z]\s || \s[A-Z] || [A-Z]\s || \s[a-z]\s/", "", $str);

        return $str;
    }

    public function buildCloud($str)
    {
        $arrayBuff = explode(" ", $str);
        $arrayWord = array_count_values($arrayBuff);
        $cloud = '';

        foreach($arrayWord as $word => $count)
        {
            if ($word == "")
            {
                continue;
            }

            if ($this->countFilterWord < 80)
            {
                $this->countFilterWord++;
            }
            else
            {
                break;
            }

            $size = $count * 8;

            if ($size > 30)
            {
                $size = 20;
            }

            $cloud .= '<span style="font-size:' . $size . 'pt"><a href=' . 'filter/' . $word . '>' . $word . '</a></span> ';
        }

        return $cloud;
    }
}

?>