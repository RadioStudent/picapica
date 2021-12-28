<?php

namespace App\Controller\API;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Artist;

/**
 * Class ArtistController
 * @package App\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Artist")
 */
class ArtistController extends AbstractFOSRestController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction(Request $request)
    {
        $search = $request->query->get('search', null);
        $sort   = $request->query->get('sort', null);
        $from   = $request->query->get('from', 0);
        $size   = $request->query->get('size', 10);

        if ($search) {
            $search = Artist::fieldsToElastic(json_decode($search, 1));
        }

        $sort   = $sort? json_decode($sort): null;

        $repo = $this
            ->container
            ->get('search.repository.artist');

        $data = $repo->search($search, $sort, $from, $size);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    public function getAction($id)
    {
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('App:Artist');

        $artist = $repo->find($id);

        $view = $this->view($artist->getFlat('long'), 200);

        return $this->handleView($view);
    }
}
