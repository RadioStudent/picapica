<?php

namespace App\Controller\API;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Genre;

/**
 * Class LabelController
 * @package App\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Genre")
 */
class GenreController extends AbstractFOSRestController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cgetAction(Request $request)
    {
        $search = $request->query->get('query', null);

        $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('App:Genre');

        $data = $repo->createQueryBuilder('h')
              ->where('h.name LIKE :search')
              ->setParameter('search', '%' . $search . '%')
              ->getQuery()
              ->getResult();

        $out = array_map(function ($h) {
            return $h->getFlat();
        }, $data);

        $view = $this->view($out, 200);

        return $this->handleView($view);
    }

}
