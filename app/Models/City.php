<?php

/**
 * Ficheiro do Modelo City.
 *
 * Este modelo armazena a lista de cidades disponíveis no sistema.
 * É utilizado para padronizar os dados de morada dos funcionários e
 * permitir a filtragem por localização.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class City extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Campos preenchíveis.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',      // Nome da cidade
        'state_id'   // ID do distrito/estado ao qual a cidade pertence
    ];

    /**
     * Relacionamento: Distrito/Estado.
     *
     * Liga a cidade ao seu distrito correspondente.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Relacionamento: Funcionários.
     *
     * Lista todos os funcionários que residem nesta cidade.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Configuração do log de actividades.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
