<?php

namespace Tests\Unit;

use App\Http\Helpers;
use PHPUnit\Framework\TestCase;

class HelpersDateTest extends TestCase
{
    public function test_add_days_to_date_accepts_string_value(): void
    {
        $this->assertSame('2026-01-06', Helpers::addDaysToDate('2026-01-01', '5'));
    }

    public function test_add_days_to_date_accepts_int_value(): void
    {
        $this->assertSame('2026-01-06', Helpers::addDaysToDate('2026-01-01', 5));
    }

    public function test_add_months_to_date_accepts_string_value(): void
    {
        $this->assertSame('2026-03-01', Helpers::addMonthsToDate('2026-01-01', '2'));
    }

    public function test_add_months_to_date_accepts_int_value(): void
    {
        $this->assertSame('2026-03-01', Helpers::addMonthsToDate('2026-01-01', 2));
    }
}
