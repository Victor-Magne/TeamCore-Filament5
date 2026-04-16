<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'city_id',
        'unit_id',
        'designation_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'date_of_birth',
        'nif',
        'nss',
        'address',
        'zip_code',
        'date_hired',
        'date_dismissed',
        'vacation_balance',
        'reports_to_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_hired' => 'date',
        'date_dismissed' => 'datetime',
        'vacation_balance' => 'integer',
    ];

    /**
     * Relacionamento: Cidade do funcionário.
     * Necessário para o Select::make('city_id')->relationship('city', 'name')
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(LeaveAndAbsence::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function hourBanks(): HasMany
    {
        return $this->hasMany(HourBank::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    /**
     * Relacionamento: Supervisor direto
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reports_to_id');
    }

    /**
     * Relacionamento: Subordinados diretos
     */
    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'reports_to_id');
    }

    /**
     * Obtém o saldo atual do banco de horas (mês atual)
     */
    public function getCurrentHourBankBalance(): ?HourBank
    {
        $monthYear = now()->format('Y-m');

        return $this->hourBanks()->where('month_year', $monthYear)->first();
    }

    /**
     * Obtém o saldo total do banco de horas (acumulado de todos os meses)
     */
    public function getTotalHourBankBalance(): int
    {
        return $this->hourBanks()->sum('balance');
    }
}
