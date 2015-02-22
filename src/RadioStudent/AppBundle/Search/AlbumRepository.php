<?php

namespace RadioStudent\AppBundle\Search;

use Elastica\Query;
use Elastica\Filter;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class AlbumRepository {

    /**
     * @var TransformedFinder
     */
    private $finder;

    /**
     * @param TransformedFinder $finder
     */
    public function __construct(TransformedFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param null $search
     * @param null $page
     * @param int $itemsPerPage
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function search($search = null, $page = null, $itemsPerPage = 10)
    {
        $booleanFilter   = new Filter\Bool();
        $multiMatchQuery = new Query\MultiMatch();

        if ($search) {
            $multiMatchQuery
                ->setQuery($search)
                ->setFields(['name^2', 'artists.name^1', 'tracks.name^1']);
        } else {
            $multiMatchQuery = null;
        }
        $filteredQuery = new Query\Filtered(
            $multiMatchQuery,
            $booleanFilter
        );
        $query = new Query($filteredQuery);
        $query
            ->setSort([
                '_score',
            ]);

        $result = $this->finder->findPaginated($query);
        if ($page) {
            $result
                ->setMaxPerPage($itemsPerPage)
                ->setCurrentPage($page);
        }
        return $result;
    }

    /**
     * @param $search
     * @param int $limit
     *
     * @return array
     */
    public function quickSearch($search, $limit = 10)
    {
        $matchQuery = new Query\Match();

        $matchQuery->setFieldQuery('name', $search);

        $query = new Query($matchQuery);
        $query->setSort(['_score']);
        $query->setSize($limit);

        return $this->finder->find($query);
    }
}