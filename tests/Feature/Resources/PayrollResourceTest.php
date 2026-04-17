<?php

use App\Models\Payroll;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('payroll model can be created with factory', function () {
    $payroll = Payroll::factory()->create();

    expect($payroll)->toBeInstanceOf(Payroll::class);
    expect($payroll->employee_id)->toBeGreaterThan(0);
});

it('payroll status can be updated', function () {
    $payroll = Payroll::factory()->create(['status' => 'pending']);

    $payroll->update(['status' => 'paid']);

    expect($payroll->refresh()->status)->toBe('paid');
});

it('payroll can be soft deleted', function () {
    $payroll = Payroll::factory()->create();
    $id = $payroll->id;

    $payroll->delete();

    expect(Payroll::find($id))->toBeNull();
    expect(Payroll::withTrashed()->find($id))->not->toBeNull();
});
