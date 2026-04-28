<?php

/**
 * Ficheiro do Modelo State.
 *
 * Este modelo representa os Distritos (Portugal) ou Estados/Regiões
 * administrativas de um país. Serve como nível intermédio na hierarquia
 * de localização geográfica.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class State extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Campos preenchíveis.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',       // Nome do distrito ou estado
        'country_id'  // ID do país ao qual pertence
    ];

    /**
     * Relacionamento: País.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Relacionamento: Cidades.
     *
     * Lista todas as cidades que pertencem a este distrito/estado.
     */
    public function cities()
    {
        return $this->hasMany(City::class);
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
