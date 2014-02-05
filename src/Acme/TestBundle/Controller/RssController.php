<?php

namespace Acme\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Acme\TestBundle\Helper\Rss;
use Doctrine\ORM\QueryBuilder;
use Acme\TestBundle\Entity\Source;
use Acme\TestBundle\Form\ControlForm;


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
    public function settingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if($request->isXmlHttpRequest())
        {
            $editId = $request->request->get('editId');

            //$em = $this->getDoctrine()->getManager();
            $source = $em->getRepository('AcmeTestBundle:Source')->find($editId);

            $response = new Response(json_encode(array('name' => $source->getName(), 'url' => $source->getUrl())));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $form = $this->createForm(new ControlForm());
        $form->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            if ($form->get('Add')->isClicked()) //Add new source
            {
                $postData = $request->request->get('controlForm');
                $newName = $postData['fieldName'];
                $newUrl = $postData['fieldUrl'];

                $source = new Source();
                $source->setName($newName);
                $source->setUrl($newUrl);

                $em->persist($source);
                $em->flush();
            }
            else if ($form->get('Delete')->isClicked()) //Delete selected source
            {
                $postData = $request->request->get('controlForm');
                $deleteId = $postData['sourceId'];

                $source = $em->getRepository('AcmeTestBundle:Source')
                          ->find($deleteId);

                $em->remove($source);
                $em->flush();
            }
            else if ($form->get('Edit')->isClicked())   //Edit selected source
            {
                $postData = $request->request->get('controlForm');
                $editId = $postData['sourceId'];
                $editName = $postData['fieldName'];
                $editUrl = $postData['fieldUrl'];

                $source = $em->getRepository('AcmeTestBundle:Source')
                    ->find($editId);

                $source->setName($editName);
                $source->setUrl($editUrl);

                $em->persist($source);
                $em->flush();
            }

            return new RedirectResponse($this->generateUrl('actionForm'));

            /*if ($form->isValid())
            {
            }*/
        }

        $sources = $em->getRepository('AcmeTestBundle:Source')
            ->findAll();

        return array('count' => count($sources), 'sources' => $sources, 'form' => $form->createView());
    }

    /*public function ajaxAction(Request $request)
    {
        $data = $request->request->get('request');

    }*/

}
