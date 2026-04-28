<?php

/**
 * Ficheiro do Modelo ActivityLog.
 *
 * Este modelo serve como um wrapper sobre a tabela 'activity_log' gerida pelo pacote Spatie ActivityLog.
 * Permite consultar e manipular os registos de auditoria do sistema, rastreando quem fez o quê,
 * em que modelo e quando.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'activity_log';

    /**
     * Campos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'log_name',          // Nome do log (ex: Employee, Contract)
        'description',       // Descrição da acção realizada (ex: created, updated)
        'subject_type',      // Classe do modelo que sofreu a acção
        'subject_id',        // ID do registo que sofreu a acção
        'event',             // Tipo de evento (created, updated, deleted)
        'causer_type',       // Classe do utilizador que realizou a acção
        'causer_id',         // ID do utilizador que realizou a acção
        'attribute_changes', // JSON contendo o que mudou (valores antigos vs novos)
        'properties',        // Propriedades adicionais guardadas no log
    ];

    /**
     * Define as conversões de tipos para os atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attribute_changes' => 'json', // Converte automaticamente de JSON para array PHP e vice-versa
            'properties' => 'json',        // Útil para guardar metadados extras de forma estruturada
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relacionamento Polimórfico: Objecto sujeito ao log.
     *
     * Permite obter o modelo (ex: Employee, User) sobre o qual a acção foi realizada.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    /**
     * Relacionamento Polimórfico: Causador da acção.
     *
     * Permite obter o modelo do utilizador (normalmente User) que despoletou o evento.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo('causer');
    }
}
