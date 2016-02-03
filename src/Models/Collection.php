<?php

namespace Esensi\Core\Models;

use Esensi\Core\Traits\ParseMixedCollectionTrait;
use Illuminate\Database\Eloquent\Collection as BaseCollection;

/**
 * Specialized collection class based on Laravel's Illuminate\Database\Eloquent\Collection.
 * Provides extra utility methods into an Esensi/Collection.
 *
 * @package Esensi\Core
 * @author Daniel LaBarge <daniel@emersonmedia.com>
 * @author Diego Caprioli <diego@emersonmedia.com>
 * @copyright 2015 Emerson Media LP
 * @license https://github.com/esensi/core/blob/master/LICENSE.txt MIT License
 * @link http://www.emersonmedia.com
 */
class Collection extends BaseCollection
{
    /**
     * Allows parsing mixed strings into a collection.
     *
     * @see Esensi\Core\Traits\ParseMixedCollectionTrait
     */
    use ParseMixedCollectionTrait;
}
