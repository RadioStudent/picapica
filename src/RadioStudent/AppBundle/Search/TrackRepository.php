<?php

namespace RadioStudent\AppBundle\Search;

use Elastica\Query;
use Elastica\Filter;

class TrackRepository extends BaseSearchRepository {

    public function search(
        $search = null,
        $from = 0,
        $size = 100,
        $sort = [
            '_score',
            'fid'
        ])
    {
        return parent::search($search, $from, $size, $sort);
    }
}