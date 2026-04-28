<?php

/**
 * Ficheiro do Modelo Country.
 *
 * Este modelo define os países suportados pela aplicação.
 * Armazena informações básicas de identificação e códigos telefónicos internacionais.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Country extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Campos preenchíveis.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',      // Nome do país (ex: Portugal)
        'code',      // Código ISO (ex: PT)
        'phonecode'  // Indicativo telefónico (ex: 351)
    ];

    /**
     * Relacionamento: Distritos/Estados.
     *
     * Obtém todos os distritos ou regiões administrativas associadas a este país.
     */
    public function states()
    {
        return $this->hasMany(State::class);
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
