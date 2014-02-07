<?php

namespace Acme\TestBundle\Controller;

use Acme\TestBundle\Form\AddForm;
use Acme\TestBundle\Form\ViewForm;
use Acme\TestBundle\Form\EditForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Acme\TestBundle\Helper\Rss;
use Doctrine\ORM\QueryBuilder;
use Acme\TestBundle\Entity\Source;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Doctrine\Common\Cache\ApcCache;


// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class RssController extends Controller
{

    /**
     * @Template()
     */
    public function indexAction()
    {
    }


    /**
     * @Template()
     *
     * Read rss from active sources, save data in cache, create tag cloud with viewing limit 70 tags
     */
    public function readAction()
    {
        $text = '';
        $rss = new Rss();
        $items = array();
        $names = array();

        $cacheDriver = new ApcCache();

        $sources = $this->getDoctrine()
            ->getRepository('AcmeTestBundle:Source')
            ->findByActive(1);

        if ($cacheDriver->contains('text'))
        {
            $textFromCache = $cacheDriver->fetch('text');
            $cloudFromCache = $cacheDriver->fetch('cloud');
            $response = $this->render('AcmeTestBundle:Rss:read.html.twig', array('text' => $textFromCache, 'cloud' => $cloudFromCache));
            return $response;
        }
        else
        {
            if (count($sources))
            {
                foreach($sources as $source)
                {
                    $rss->load($source->getUrl());
                    $items[] = $rss->getItems();
                    $names[] = $source->getName();
                }
            }

            $clearStr = '';

            foreach($items as $index=>$item)
            {
                $text .= '<br/><h2><span id="feedName">' . $names[$index] . '</span></h2><br/>';

                foreach($item as $news)
                {
                    $text .= '<strong>'.$news['title'].'</strong><br/>'.$news['description'].'<br/>';

                    $str = preg_replace("/[[:punct:]^]/", "", $news['title']);
                    $str = preg_replace("/[[:digit:]^]/", "", $str);
                    $str = str_replace(array(' in ', ' the ', ' a ', ' and ', ' or ', ' on ', ' no ', ' not ', ' of ', ' at ' , ' an ', ' to ', ' by '), ' ', $str);
                    $clearStr .= $str . ' ';
                }
            }

            $arr = array();
            $arr = explode(" ", $clearStr);
            $arrWord = array();
            $arrWord = array_count_values($arr);
            $cloud = '';
            $countLowWorld = 0;

            foreach($arrWord as $index=>$a)
            {
                if ($a > 5)
                {
                    $size = 20;
                }
                else if($a > 10)
                {
                    $size = 30;
                }
                else if($a > 15)
                {
                    $size = 40;
                }
                else
                {
                    $size = 10;

                    if ($countLowWorld > 70)
                    {
                        continue;
                    }
                    else
                    {
                        $countLowWorld++;
                    }
                }

                $cloud .= "<span style='font-size: ".$size."pt'>" . $index . "</span> ";
            }

            $cacheDriver->save('text', $text, "120");
            $cacheDriver->save('cloud', $cloud, "120");

            $response = $this->render('AcmeTestBundle:Rss:read.html.twig', array('text' => $text, 'cloud' => $cloud));
            return $response;
        }
    }


    /**
     * @Template()
     *
     * Add new source
     */
    public function addAction(Request $request)
    {
        $formAdd = $this->createForm(new AddForm());
        $formAdd->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            if ($formAdd->get('Add')->isClicked()) //Add new source
            {
                $postData = $request->request->get('formAdd');
                $newName = $postData['fieldName'];
                $newUrl = $postData['fieldUrl'];

                $source = new Source();
                $source->setName($newName);
                $source->setUrl($newUrl);
                $source->setActive(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($source);
                $em->flush();

                return new RedirectResponse($this->generateUrl('setting_view'));
            }
        }

        return array('formAdd' => $formAdd->createView());
    }


    /**
     * @Template()
     *
     * Edit selected source
     */
    public function editAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $editId = $request->request->get('sourceId');

            $em = $this->getDoctrine()->getManager();

            if (isset($editId))
            {
                $source = $em->getRepository('AcmeTestBundle:Source')
                    ->find($editId);

                $element['name'] = $source->getName();
                $element['url'] = $source->getUrl();
                $element['sourceId'] = $editId;

                $formEdit = $this->createForm(new EditForm(), $element);

                return array('formEdit' => $formEdit->createView());
            }
            else
            {
                $postData = $request->request->get('formEdit');
                $editName = $postData['fieldName'];
                $editUrl = $postData['fieldUrl'];
                $editId = $postData['fieldSourceId'];

                $source = $em->getRepository('AcmeTestBundle:Source')
                    ->find($editId);
                $source->setName($editName);
                $source->setUrl($editUrl);
                $source->setActive(0);

                $em->persist($source);
                $em->flush();

                return new RedirectResponse($this->generateUrl('setting_view'));
            }
        }
    }


    /**
     * @Template()
     *
     * View all sources with edit/delete functions
     */
    public function viewAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if($request->isXmlHttpRequest())
        {
            $editId = $request->request->get('editId');

            if (isset($editId))
            {
                $source = $em->getRepository('AcmeTestBundle:Source')->find($editId);

                $response = new Response(json_encode(array('name' => $source->getName(), 'url' => $source->getUrl())));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $arrDeleteInd = (array)json_decode($request->request->get('arrDeleteInd'));

            if (count($arrDeleteInd))
            {
                foreach($arrDeleteInd as $deleteId)
                {
                    $source = $em->getRepository('AcmeTestBundle:Source')
                        ->find($deleteId);

                    $em->remove($source);
                }

                $em->flush();

                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $arrSaveInd = (array)json_decode($request->request->get('arrSaveInd'));

            if (count($arrSaveInd) && ($arrSaveInd[0] != -1))
            {
                $sources = $em->getRepository('AcmeTestBundle:Source')
                    ->findAll();

                foreach($sources as $source)
                {
                    $source->setActive(0);
                    $em->persist($source);
                }

                $em->flush();

                foreach($arrSaveInd as $saveId)
                {
                    foreach($sources as $source)
                    {
                        if ($source->getId() == $saveId)
                        {
                            $source->setActive(1);
                            $em->persist($source);

                            break;
                        }
                    }
                }

                $em->flush();

                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else if (count($arrSaveInd) && ($arrSaveInd[0] == -1))
            {
                $sources = $em->getRepository('AcmeTestBundle:Source')
                    ->findAll();

                foreach($sources as $source)
                {
                    $source->setActive(0);
                    $em->persist($source);
                }

                $em->flush();

                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $loadActive = $request->request->get('loadActive');

            if (isset($loadActive))
            {
                $sources = $em->getRepository('AcmeTestBundle:Source')
                    ->findByActive(1);

                $arrLoadActive = array();

                foreach($sources as $source)
                {
                    $arrLoadActive[] = $source->getId();
                }

                $response = new Response(json_encode(array('arrLoadActive' => $arrLoadActive)));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

        }

        $formView = $this->createForm(new ViewForm());
        $formView->handleRequest($request);

        $sources = $em->getRepository('AcmeTestBundle:Source')
            ->findAll();

        return array('count' => count($sources), 'sources' => $sources, 'formView' => $formView->createView());
    }

}
