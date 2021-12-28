<?php

namespace App\Search;

use Elastica\Query;
use Elastica\Filter;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\HybridResult;

use App\Entity\BaseEntity;

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
        $boolQuery = new Query\BoolQuery();

        $highlightFields = [];

        if ($search == null) {
            $boolQuery->addMust(new Query\MatchAll());

        } else {
            foreach ($search as $fields) {

                if (count($fields) == 1) {
                    $q = new Query\Match();

                    $field = key($fields);
                    $q->setField($field, current($fields));

                    $highlightFields[] = $field;

                } else {
                    $q = new Query\BoolQuery();
                    foreach ($fields as $field=>$value) {
                        $mq = new Query\Match();
                        $mq->setField($field, $value);
                        $q->addShould($mq);

                        $highlightFields[] = $field;
                    }
                }

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

        if (($n = count($highlightFields)) > 0) {
            $query->setHighlight([
                'fields' => array_combine($highlightFields, array_fill(0, $n, new \stdClass()))
            ]);
        }

        $result = $this->getFlatHybridResults($query);

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
                'name.autocomplete' => ($std = new \stdClass()),
                'fid.autocomplete'  => $std,
            ]])
            ->setSort(['_score'])
            ->setSize($limit);

        $result = $this->getFlatHybridResults($query);

        return $result;
    }

    /**
     * @param Query $query
     *
     * @return array
     */
    private function getFlatHybridResults(Query $query)
    {
        $result = $this->finder->findHybrid($query);

        $combined = array_map([$this, 'combineFlatHybrid'], $result);

        return $combined;
    }

    /**
     * @param HybridResult $hr
     *
     * @return array
     */
    private function combineFlatHybrid(HybridResult $hr)
    {
        /** @var BaseEntity $t */
        $t = $hr->getTransformed();

        $r = $hr->getResult();

        return $t->getFlat() + [
            "elastica_score"      => $r->getScore(),
            "elastica_highlights" => $r->getHighlights()
        ];
    }

}
