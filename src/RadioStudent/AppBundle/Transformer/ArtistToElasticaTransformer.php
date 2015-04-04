<?php

namespace RadioStudent\AppBundle\Transformer;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use RadioStudent\AppBundle\Entity\Artist;

class ArtistToElasticaTransformer implements ModelToElasticaTransformerInterface
{

    /**
     * Transforms an object into an elastica object having the required keys.
     *
     * @param object $object the object to convert
     * @param array $fields the keys we want to have in the returned array
     *
     * @return \Elastica\Document
     **/
    public function transform($object, array $fields)
    {
        /** @var Artist $artist */
        $artist = $object;

        $identifier = $artist->getId();

        $values = [
            'id'               => $artist->getId(),
            'name'             => $artist->getName(),
            'autocompleteName' => ($artist->getName() == $artist->getCorrectName())? $artist->getName(): null,
        ];

        $document = new Document($identifier, $values);

        return $document;
    }
}