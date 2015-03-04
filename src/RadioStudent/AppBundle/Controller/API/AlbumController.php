<?php

namespace RadioStudent\AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AlbumController
 * @package RadioStudent\AppBundle\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Album")
 */
class AlbumController extends FOSRestController
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

        $search = $search? json_decode($search): null;
        $sort   = $sort? json_decode($sort): null;

        $repo = $this
            ->container
            ->get('search.repository.album');

        $data = $repo->search($search, $sort, $from, $size);

        $view = $this
            ->view($data, 200)
            ->setSerializationContext(
                SerializationContext::create()->setGroups(["albums"])
            );

        return $this->handleView($view);
    }

    public function getAction($id)
    {
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('RadioStudentAppBundle:Album');

        $album = $repo->find($id);

        $view = $this
            ->view($album, 200)
            ->setSerializationContext(
                SerializationContext::create()->setGroups(["album"])
            );

        return $this->handleView($view);
    }
}