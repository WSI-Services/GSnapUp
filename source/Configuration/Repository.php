<?php

namespace WSIServices\GSnapUp\Configuration;

use \Illuminate\Support\Arr;
use \Illuminate\Config\Repository as IlluminateRepository;

class Repository extends IlluminateRepository {

    /**
     * Remove one or more options from the repository
     *
     * @param  array|string $keys  Option key or array of keys in repository to remove
     * @return void
     */
    public function remove($keys) {
        Arr::forget($this->items, $keys);
    }

    /**
     * Set all of the repository items for the application
     *
     * @param  array|null $items  Array of items to set repository to or null to clear all items
     * @return void
     */
    public function setAll(array $items = []) {
        $this->items = $items;
    }

}
