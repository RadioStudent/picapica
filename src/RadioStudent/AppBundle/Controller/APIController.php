<?php

namespace RadioStudent\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class APIController extends Controller
{
    /**
     * @Route("/api/test")
     * @Template()
     */
    public function indexAction()
    {
        $artistsRepo = $this->getDoctrine()->getRepository('RadioStudentAppBundle:Artist');
        $artists = $artistsRepo->findBy(['id' => [46, 14215]]);

        $relations = $artists[0]->getArtistRelations();
        var_dump($relations[0]->getArtist()->getName());
        var_dump($relations[0]->getRelatedArtist()->getName());
        var_dump($relations[1]->getRelatedArtist()->getName());

        return $this->render(
            '::index.html.twig',
            [
                'data' => $artists
            ]
        );
    }
}
