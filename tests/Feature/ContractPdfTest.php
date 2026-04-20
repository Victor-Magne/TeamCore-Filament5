<?php

use App\Models\Contract;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['app.key' => 'base64:u8p67uT/n+J6Nq/E6L3v4oZ8u68tX6E1l2m3n4o5p6q=']);
});

test('contract pdf routes are protected by auth', function () {
    $employee = Employee::factory()->create();
    $contract = Contract::where('employee_id', $employee->id)->first();

    $response = $this->get(route('contracts.pdf.single', $contract), [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(401);
});

test('contract pdf routes are protected by policies', function () {
    $employee = Employee::factory()->create();
    $user = User::where('employee_id', $employee->id)->first();
    $contract = Contract::where('employee_id', $employee->id)->first();

    // No permissions granted yet
    $this->actingAs($user)
        ->get(route('contracts.pdf.single', $contract))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('contracts.pdf.all'))
        ->assertForbidden();
});

test('authorized user can download single contract pdf', function () {
    $employee = Employee::factory()->create();
    $user = User::where('employee_id', $employee->id)->first();
    $contract = Contract::where('employee_id', $employee->id)->first();

    // Mock permissions since they might not be in DB
    $user = Mockery::mock($user)->makePartial();
    $user->shouldReceive('can')->with('View:Contract')->andReturn(true);

    $response = $this->actingAs($user)
        ->get(route('contracts.pdf.single', $contract));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    // Log headers to debug if needed
    // dump($response->headers->all());
    $this->assertTrue(
        str_contains($response->headers->get('Content-Disposition'), 'attachment; filename=contrato_') ||
        str_contains($response->headers->get('Content-Disposition'), 'attachment; filename="contrato_')
    );
});

test('authorized user can download bulk contract pdf', function () {
    $employee1 = Employee::factory()->create();
    $user = User::where('employee_id', $employee1->id)->first();
    $contract1 = Contract::where('employee_id', $employee1->id)->first();

    $employee2 = Employee::factory()->create();
    $contract2 = Contract::where('employee_id', $employee2->id)->first();

    $ids = implode(',', [$contract1->id, $contract2->id]);

    // Mock permissions
    $user = Mockery::mock($user)->makePartial();
    $user->shouldReceive('can')->with('ViewAny:Contract')->andReturn(true);

    $response = $this->actingAs($user)
        ->get(route('contracts.pdf.bulk', ['ids' => $ids]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $this->assertTrue(
        str_contains($response->headers->get('Content-Disposition'), 'attachment; filename=contratos_relatorio_') ||
        str_contains($response->headers->get('Content-Disposition'), 'attachment; filename="contratos_relatorio_')
    );
});
