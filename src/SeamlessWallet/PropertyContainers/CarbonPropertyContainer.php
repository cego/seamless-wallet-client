<?php

namespace Cego\SeamlessWallet\PropertyContainers;

use Carbon\Carbon;
use Nbj\PropertyContainer;

class CarbonPropertyContainer extends PropertyContainer
{
    /**
     * List of all properties that should be converted to Carbon instances
     *
     * @var array
     */
    protected array $dateProperties = [];

    /**
     * Converts the data to Carbon instances if the property is marked as a date property
     *
     * @inheritDoc
     */
    public function get($property)
    {
        $value = parent::get($property);

        if ($value && $this->isDateProperty($property)) {
            return Carbon::parse($value);
        }

        return $value;
    }

    /**
     * Returns true if the property is a date property, and false otherwise
     *
     * @param mixed $property
     *
     * @return bool
     */
    protected function isDateProperty($property): bool
    {
        return in_array($property, $this->dateProperties, false);
    }
}
