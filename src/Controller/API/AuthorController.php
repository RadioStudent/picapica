<?php

namespace App\Controller\API;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthorController
 * @package App\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Author")
 */
class AuthorController extends AbstractFOSRestController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction(Request $request)
    {
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('App:Author');

        //TODO: access control?
        $authors = $repo->findAll();

        $data = [];
        foreach ($authors as $author) {
            $data[] = $author->getFlat();
        }

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    public function getAction($id)
    {
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('App:Author');

        $term = $repo->find($id);

        $view = $this->view($term->getFlat('long'), 200);

        return $this->handleView($view);
    }

    public function postAction(Request $request)
    {

    }

    public function putAction($id, Request $request)
    {

    }
}
