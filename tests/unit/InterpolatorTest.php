<?php

namespace Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\I18n\Interpolator\AssocArray;

class InterpolatorTest extends Unit
{
    protected AssocArray $interpolator;

    protected function _before()
    {
        parent::_before();
        $this->interpolator = new AssocArray();
    }

    public function testShouldHandleDefaultPlaceholders(): void
    {
        $translated = $this->interpolator->replacePlaceholders('%p1% and %p2%', [
            'p1' => 'A',
            'p2' => 'B',
        ]);
        self::assertSame('A and B', $translated);
    }

    public function testShouldHandleCustomPlaceholders(): void
    {
        $this->interpolator->defineBrackets('{{', '}}');
        $translated = $this->interpolator->replacePlaceholders('{{p1}} and {{p2}}', [
            'p1' => 'A',
            'p2' => 'B',
        ]);
        self::assertSame('A and B', $translated);
    }
}
