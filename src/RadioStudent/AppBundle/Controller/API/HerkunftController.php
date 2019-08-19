<?php

namespace RadioStudent\AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use RadioStudent\AppBundle\Entity\Herkunft;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class HerkunftController
 * @package RadioStudent\AppBundle\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Herkunft")
 */
class HerkunftController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cgetAction(Request $request)
    {
        $search = $request->query->get('query', null);

        $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('RadioStudentAppBundle:Herkunft');

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
