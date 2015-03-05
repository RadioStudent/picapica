<?php

namespace RadioStudent\AppBundle\Search;

use Elastica\Query;
use Elastica\Filter;
use FOS\ElasticaBundle\Finder\TransformedFinder;

abstract class BaseSearchRepository {

    /**
     * @var TransformedFinder
     */
    protected $finder;

    /**
     * @param TransformedFinder $finder
     */
    public function __construct(TransformedFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param array $search
     * @param int $from
     * @param int $size
     * @param array $sort
     *
     * @return array
     */
    public function search($search = null, $sort = null, $from = 0, $size = 100)
    {
        $boolQuery = new Query\Bool();

        if ($search == null) {
            $boolQuery->addMust(new Query\MatchAll());

        } else {
            foreach ($search as $field) {
                $q = new Query\Match();
                $q->setField(key($field), current($field));

                $boolQuery->addMust($q);
            }
        }

        $query = new Query($boolQuery);

        if ($sort == null) {

        } else {
            foreach ($sort as $k => $v) {
                if (!is_numeric($k)) {
                    $v = [$k => $v];
                }
                $query->addSort($v);
            }
        }

        $query
            ->setFrom($from)
            ->setSize($size);

        $result = $this->getFlattenedHybridResults($query);

        return $result;
    }

    /**
     * @param $search
     * @param array $fields
     * @param int $limit
     *
     * @return array
     */
    public function autoComplete($search, $fields = ["name.autocomplete"], $limit = 10)
    {
        $matchQuery = new Query\MultiMatch();

        $matchQuery->setFields($fields);
        $matchQuery->setQuery($search);

        $query = new Query($matchQuery);
        $query
            ->setHighlight(['fields' => [
                'name.autocomplete' => new \stdClass(),
                'fid.autocomplete' => new \stdClass(),
            ]])
            ->setSort(['_score'])
            ->setSize($limit);

        $result = $this->getFlattenedHybridResults($query);

        return $result;
    }

    private function getFlattenedHybridResults($query)
    {
        $result = $this->finder->findHybrid($query);

        return array_map(
            function($e) {
                $t = $e->getTransformed();
                $r = $e->getResult();
                return $t->getFlat() + [
                    "elastica_score" => $r->getScore(),
                    "elastica_highlights" => $r->getHighlights()
                ];
            },
            $result
        );
    }

}