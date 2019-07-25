<?php

namespace RadioStudent\AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use RadioStudent\AppBundle\Entity\Track;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TrackController
 * @package RadioStudent\AppBundle\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Track")
 */
class TrackController extends FOSRestController
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
            $search = Track::fieldsToElastic(json_decode($search, 1));
        }

        $sort = Track::fieldsToElastic($sort && !empty(json_decode($sort, true)) ? json_decode($sort, true) : $this->getDefaultSort($search), 'sort');

        $repo = $this
            ->container
            ->get('search.repository.track');

        $data = $repo->search($search, $sort, $from, $size);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    public function getAction($id)
    {
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('RadioStudentAppBundle:Track');

        $track = $repo->find($id)->getFlat('long');

        $view = $this->view($track, 200);

        return $this->handleView($view);
    }

    protected function getDefaultSort($search)
    {
        foreach ($search as $filter) {
            if (isset($filter['artist.id']) || isset($filter['album.id'])) {
                return [
                    'fid.raw' => 'asc'
                ];
            }
        }

        return [
            '_score' => 'desc',
            'fid.raw'    => 'asc'
        ];
    }
}
