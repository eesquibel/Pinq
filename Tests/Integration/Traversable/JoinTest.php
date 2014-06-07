<?php

namespace Pinq\Tests\Integration\Traversable;

class JoinTest extends TraversableTest
{
    protected function _testReturnsNewInstanceOfSameTypeWithSameScheme(\Pinq\ITraversable $traversable)
    {
        return $traversable
                ->join([])
                    ->on(function ($i) {})
                    ->to(function ($k) {});
    }

    /**
     * @dataProvider theImplementations
     */
    public function testThatExecutionIsDeferred(\Pinq\ITraversable $traversable, array $data)
    {
        $this->assertThatExecutionIsDeferred(function (callable $function) use ($traversable) {
            return $traversable->join([])->on($function)->to($function);
        });

        $this->assertThatExecutionIsDeferred(function (callable $function) use ($traversable) {
            return $traversable->join([])->onEquality($function, $function)->to($function);
        });
    }

    /**
     * @dataProvider everything
     */
    public function testJoinOnTrueProducesACartesianProduct(\Pinq\ITraversable $traversable, array $data)
    {
        $traversable = $traversable
                ->join($data)
                    ->on(function () { return true; })
                    ->to(function ($outerValue, $innerValue) { return [$outerValue, $innerValue]; });
                    
        $cartesianProduct = [];

        foreach ($data as $outerValue) {
            foreach ($data as $innerValue) {
                $cartesianProduct[] = [$outerValue, $innerValue];
            }
        }

        $this->assertMatchesValues($traversable, $cartesianProduct);
    }

    /**
     * @dataProvider everything
     */
    public function testJoinWillRewindCorrectly(\Pinq\ITraversable $traversable, array $data)
    {
        $traversable = $traversable
                ->join([0 => 0])
                    ->on(function () { return true; })
                    ->to(function ($outerValue, $innerValue) { return $outerValue; });
        
        for ($count = 0; $count < 2; $count++) {
            $newData = [];
            foreach ($traversable as $value) {
                $newData[] = $value;
            }
            $this->assertSame(array_values($data), $newData);
        }
    }

    /**
     * @dataProvider everything
     */
    public function testJoinOnFalseProducesEmpty(\Pinq\ITraversable $traversable, array $data)
    {
        $traversable = $traversable
                ->join($data)
                    ->on(function () { return false; })
                    ->to(function ($outerValue, $innerValue) {
                        return [$outerValue, $innerValue];
                    });

        $this->assertMatches($traversable, []);
    }

    /**
     * @dataProvider oneToTen
     */
    public function testJoinOnProducesCorrectResult(\Pinq\ITraversable $traversable, array $data)
    {
        $traversable = $traversable
                ->join([1, 2, 3, '4', '5'])
                    ->on(function ($outer, $inner) { return $outer === $inner; })
                    ->to(function ($outer, $inner) {
                        return $outer . '-' . $inner;
                    });

        $this->assertMatchesValues($traversable, ['1-1', '2-2', '3-3']);
    }

    /**
     * @dataProvider oneToTen
     */
    public function testJoinOnEqualityProducesCorrectResult(\Pinq\ITraversable $traversable, array $data)
    {
        $traversable = $traversable
                ->join([1, 2, 3, '4', '5'])
                    ->onEquality(function ($outer) { return $outer; }, function ($inner) { return $inner; })
                    ->to(function ($outer, $inner) {
                        return $outer . '-' . $inner;
                    });

        $this->assertMatchesValues($traversable, ['1-1', '2-2', '3-3']);
    }

    /**
     * @dataProvider oneToTen
     */
    public function testJoinWithTransformProducesCorrectResult(\Pinq\ITraversable $traversable, array $data)
    {
        $traversable = $traversable
                ->join(range(10, 20))
                    ->onEquality(function ($outer) { return $outer * 2; }, function ($inner) { return $inner; })
                    ->to(function ($outer, $inner) {
                        return $outer . ':' . $inner;
                    });

        $this->assertMatchesValues(
                $traversable,
                [
                    '5:10',
                    '6:12',
                    '7:14',
                    '8:16',
                    '9:18',
                    '10:20'
                ]);
    }

    /**
     * @dataProvider oneToTen
     */
    public function testEqualityJoinOnKeysReturnsTheCorrectResult(\Pinq\ITraversable $traversable, array $data)
    {
        $traversable = $traversable
                ->indexBy(function ($value) { return $value; })
                ->join(array_fill_keys(range(15, 30), null))
                    ->onEquality(function ($o, $key) { return $key * 3; }, function ($i, $key) { return $key; })
                    ->to(function ($o, $i, $outerKey, $innerKey) {
                        return $outerKey . ':' . $innerKey;
                    });

        $this->assertMatchesValues(
                $traversable,
                [
                    '5:15',
                    '6:18',
                    '7:21',
                    '8:24',
                    '9:27',
                    '10:30'
                ]);
    }

    /**
     * @dataProvider oneToTen
     */
    public function testJoinOnKeysAndValuesReturnsTheCorrectResult(\Pinq\ITraversable $traversable, array $data)
    {
        $traversable = $traversable
                ->indexBy(function ($value) { return $value; })
                ->join(range(0, 5, 0.5))
                    ->on(function ($outerValue, $innerValue, $outerKey) { return (double)($outerKey / 2) === $innerValue; })
                    ->to(function ($outerValue, $innerValue, $outerKey) {
                        return $outerValue . ':' . $innerValue;
                    });

        $this->assertMatchesValues(
                $traversable,
                [
                    '1:0.5',
                    '2:1',
                    '3:1.5',
                    '4:2',
                    '5:2.5',
                    '6:3',
                    '7:3.5',
                    '8:4',
                    '9:4.5',
                    '10:5',
                ]);
    }
}
