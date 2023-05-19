<?php

namespace WeDevs\DokanPro\Withdraw;

/**
 * This class maps and calculate the minimum withdraw amount list.
 */
class Limiter {

    /**
     * @var int $minimum_value Minimum or starting value.
     */
    public $minimum_value;

    /**
     * @var int $increment_value Increment value.
     */
    public $increment_value = 10;

    /**
     * @var int $max_incremented_value Maximum incremented value.
     */
    public $max_incremented_value = 100;

    /**
     * @var int[][] $increment_values Increment value map.
     */
    public $increment_values = [
        1 => [ 1, 10 ],
        2 => [ 10, 100 ],
        3 => [ 100, 1000 ],
        4 => [ 1000, 100000 ],
        5 => [ 5000, 100000 ],
        6 => [ 10000, 100000 ],
        7 => [ 50000, 500000 ],
    ];

    public function __construct( int $minimum_value )
    {
        $this->minimum_value = $minimum_value;
    }

    /**
     * Get the increment value list.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_list() {
        $this->set_increment_value();

        return $this->get_values();
    }

    /**
     * Set the increment value dynamically.
     * This method will set the increment value based on the minimum value.
     * If the minimum value is less than 100, then the increment value will be 10.
     * If the minimum value is less than 1000, then the increment value will be 100.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function set_increment_value() {
        $index = strlen( $this->minimum_value );

        if ( array_key_exists( $index, $this->increment_values ) ) {
            $this->increment_value       = $this->increment_values[ $index ][0];
            $this->max_incremented_value = $this->increment_values[ $index ][1];
        }
    }

    /**
     * Get the increment values.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_values() {
        $result = [];

        for( ; $this->minimum_value <= $this->max_incremented_value; $this->minimum_value += $this->increment_value) {
            $result[] = floatval( $this->minimum_value );
        }

        return $result;
    }
}
