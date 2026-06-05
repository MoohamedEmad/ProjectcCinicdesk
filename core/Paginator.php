<?php
class Paginator {
    private $totalItems;
    private $perPage;
    private $currentPage;
    private $totalPages;

    public function __construct($totalItems, $perPage, $currentPage = 1) {
        $this->totalItems = $totalItems;
        $this->perPage = $perPage;
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = ceil($totalItems / $perPage);
    }

    public function offset() {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function totalPages() {
        return $this->totalPages;
    }

    public function hasPrev() {
        return $this->currentPage > 1;
    }

    public function hasNext() {
        return $this->currentPage < $this->totalPages;
    }

    public function prevPage() {
        return $this->currentPage - 1;
    }

    public function nextPage() {
        return $this->currentPage + 1;
    }
}