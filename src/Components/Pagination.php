<?php

namespace Lib\Serializer\Components;

/**
 * Для пагинации внутри листа
 * Class Pagination
 * @package Kluatr\Serializer\Components
 */
class Pagination
{
    protected $sort;
    protected $limit = 20;
    protected $page  = 0;

    public function __construct(SortBy $sort, int $limit,int $page)
    {
        $this->sort = $sort;
        if ($page > 0) {
            $this->page = --$page;
        }
        if ($limit > 0) {
            $this->limit = $limit;
        }
    }

    /**
     * @return SortBy
     */
    public function getSort(): SortBy
    {
        return $this->sort;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param SortBy $sort
     */
    public function setSort(SortBy $sort): void
    {
        $this->sort = $sort;
    }

}