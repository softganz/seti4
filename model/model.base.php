<?php
/**
 * Model    :: Common Model
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2026-11-05
 * Modified :: 2026-11-07
 * Version  :: 2
 *
 */

declare(strict_types=1);

class Model {}

/**
 * Items container for multi-row results model
 * @uses ItemsModel::setItems(array $items): static
 */
class ItemsModel extends Model {
	public protected(set) int $count;
	public protected(set) array $items {
		set(array $value) {
			$this->items = $value;
			$this->count = count($value);
		}
	}

	function __construct(array $items = []) {
		$this->items = $items;
	}

	/**
	 * Set items from outside the class (property hooks cannot write protected(set) from outside)
	 *
	 * @param array $items
	 * @return static
	 */
	public function setItems(array $items): static {
		$this->items = $items;
		return $this;
	}
}
