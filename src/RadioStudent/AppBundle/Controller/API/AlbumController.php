<?php

namespace RadioStudent\AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use RadioStudent\AppBundle\Entity\Album;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cgetAction(Request $request)
    {
        $search = $request->query->get('search', null);
        $sort   = $request->query->get('sort', null);
        $from   = $request->query->get('from', 0);
        $size   = $request->query->get('size', 10);

        if ($search) {
            $search = Album::fieldsToElastic(json_decode($search, 1));
        }

        $sort   = $sort? json_decode($sort): null;

        $repo = $this
            ->container
            ->get('search.repository.album');

        $data = $repo->search($search, $sort, $from, $size);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    public function getAction($id)
    {
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('RadioStudentAppBundle:Album');

        $album = $repo->find($id);

        $view = $this->view($album->getFlat('long'), 200);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $data = json_decode($request->getContent(), true)['data'];
            $album = $em->getRepository("RadioStudentAppBundle:Album")->create($data);

            return new JsonReponse($album->getId(), 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                "message" => $e->getMessage()
            ], 422);
        }
    }
}
