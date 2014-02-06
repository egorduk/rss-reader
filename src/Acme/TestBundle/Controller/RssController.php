<?php

namespace Acme\TestBundle\Controller;

use Acme\TestBundle\Form\AddForm;
use Acme\TestBundle\Form\ViewForm;
use Acme\TestBundle\Form\EditForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\BrowserKit\Response;
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
     * @Cache(expires="tomorrow", public="true", maxage="600")
     */
    public function readAction()
    {
        //error_log($kernel->getLog());

        $html = '';
        $rss = new Rss();
        $items = array();
        $names = array();

        $cacheDriver = new ApcCache();

        $sources = $this->getDoctrine()
            ->getRepository('AcmeTestBundle:Source')
            ->findByActive(1);

        if ($cacheDriver->contains('data'))
        {
            //return $cacheDriver->fetch('data');
            $response = $this->render('AcmeTestBundle:Rss:read.html.twig', array('html' => $cacheDriver->fetch('data')));
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

            foreach($items as $index=>$item)
            {
                $html .= '=======================================';
                $html .= '<br/><h2>' . $names[$index] . '</h2><br/>';
                $html .= '=======================================';

                foreach($item as $news)
                {
                    $html .= '<p><strong>'.$news['title'].'</strong><br/>'.$news['description'].'<br/></p>';
                }
            }

            $cacheDriver->save('data', $html, "900");

            $response = $this->render('AcmeTestBundle:Rss:read.html.twig', array('html' => $html));
            return $response;
        }


    }


    /**
     * @Template()
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

                $em->persist($source);
                $em->flush();

                return new RedirectResponse($this->generateUrl('setting_view'));
            }
        }
    }


    /**
     * @Template()
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

        /*if ($request->isMethod('POST'))
        {

        }*/

        $sources = $em->getRepository('AcmeTestBundle:Source')
            ->findAll();

        return array('count' => count($sources), 'sources' => $sources, 'formView' => $formView->createView());
    }

}
