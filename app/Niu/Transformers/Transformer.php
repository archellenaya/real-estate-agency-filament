<?php

/**
 * Created by PhpStorm.
 * User: niumark
 * Date: 24/05/2015
 * Time: 16:04
 */

namespace App\Niu\Transformers;


abstract class Transformer
{
	/**
	 * Transforms a collection
	 *
	 * @param $items
	 *
	 * @return array
	 */
	public function transformCollection(array $items)
	{
		return array_map([$this, 'transform'], $items);
	}

	public abstract function transform($item);
}
