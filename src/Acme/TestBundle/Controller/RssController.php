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
        $res = $this->getDoctrine()
            ->getRepository('AcmeTestBundle:Source')
            ->findOneById(1);

        $rss = new Rss();
        $rss->load($res->getUrl());
        $items = $rss->getItems();

        $html = '';

        foreach($items as $index => $item)
        {
            $html .= '<p><strong>'.$item['title'].'</strong><br/>'.$item['description'].'<br/></p>';
        }

        return array('html' => $html);
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

            if (count($arrSaveInd))
            {
                foreach($arrSaveInd as $saveId)
                {
                    $source = $em->getRepository('AcmeTestBundle:Source')
                        ->find($saveId);

                    $source->setActive(1);

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
