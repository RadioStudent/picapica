<?php

namespace App\Controller\API;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Album;

/**
 * Class AlbumController
 * @package App\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Album")
 */
class AlbumController extends AbstractFOSRestController
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
            ->getRepository('App:Album');

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
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $data = json_decode($request->getContent(), true);

            $album = null;
            if ($data['id']) {
                $album = $em->getRepository("App:Album")->update($data);
            } else {
                $album = $em->getRepository("App:Album")->create($data);
            }

            $em->flush();

            // Poindeksirajmo novosti
            $albumPersister = $this->container->get('fos_elastica.object_persister.picapica.album');
            $artistPersister = $this->container->get('fos_elastica.object_persister.picapica.artist');
            $trackPersister = $this->container->get('fos_elastica.object_persister.picapica.track');

            $albumPersister->replaceOne($album);
            $artistPersister->replaceMany($album->getArtists()->toArray());
            $trackPersister->replaceMany($album->getTracks()->toArray());

            return new JsonResponse($album, 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                ["error" => [
                    "message" => $e->getMessage()
                ]]
            ], 422);
        }
    }
}
