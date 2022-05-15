<?php

namespace Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\I18n\Adapter\Json;
use Phalcon\I18n\Handler\NativeArray;
use Phalcon\I18n\Interpolator\AssocArray;
use Phalcon\I18n\Loader\Files;

class HandlerTest extends Unit
{
    public function getShiftVariants(): array
    {
        return [
            [0, [],               'global.a4.b2.c1'],
            [1, ['global'],       'a4.b2.c1'],
            [2, ['global', 'a4'], 'b2.c1'],
        ];
    }

    /**
     * @dataProvider getShiftVariants
     */
    public function testCheckKeysShifting(int $shiftLevel, array $shiftKeys, string $tKey): void
    {
        $handler = new NativeArray(new AssocArray('{{', '}}'), [
            'content' => Files::load(Json::class, FIXTURES . '/locale/de')->toArray(),
            'flatten' => ['shift' => $shiftLevel],
        ]);
        if ($shiftLevel > 0) {
            call_user_func_array([$handler, 'shiftKeys'], $shiftKeys);
        }
        self::assertTrue($handler->has($tKey));
    }
}
