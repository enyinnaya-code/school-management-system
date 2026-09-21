<?php

namespace App\Services;

use App\Models\SchoolClass;
use Illuminate\Support\Collection;

/**
 * Groups classes into levels (JSS1A, JSS1B, JSS1C => JSS1).
 *
 * There is no level column, so the level is read from the class name:
 * everything up to and including the grade number ("JSS1A" => "JSS1",
 * "BASIC 1A -DESTINY KIDS" => "BASIC 1"). Names without a number drop a
 * trailing arm letter ("PRE-NURSERY A" => "PRE-NURSERY").
 */
class ClassLevels
{
    public static function keyFor(string $className): string
    {
        $className = trim($className);

        if (preg_match('/^(.*?\d+)/', $className, $match)) {
            return trim($match[1]);
        }

        return trim(preg_replace('/\s+[A-Za-z]$/', '', $className));
    }

    /**
     * All classes grouped by level key, keeping section then creation order.
     *
     * @return Collection<string, Collection<int, SchoolClass>>
     */
    public function groups(): Collection
    {
        return SchoolClass::orderBy('section_id')
            ->orderBy('id')
            ->get()
            ->groupBy(fn(SchoolClass $class) => self::keyFor($class->name));
    }
}
