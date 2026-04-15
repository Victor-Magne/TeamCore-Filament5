<?php

return [
    /**
     * Ativar validação de licenças antes de descontar horas
     *
     * Se ativado, o sistema verificará se existe uma licença (leave) ou férias (vacation)
     * já registada antes de descontar horas do banco por falta injustificada.
     *
     * Se desativado, o desconto ocorre sempre que solicitado.
     */
    'validate_leaves_before_deduction' => env('HOUR_BANK_VALIDATE_LEAVES', true),

    /**
     * Tipos de licenças que não devem descontar horas
     *
     * Licenças justificadas não geram desconto do banco de horas.
     * Faltas injustificadas sim.
     */
    'justified_leave_types' => [
        'sick_leave',       // Baixa Médica
        'parental',         // Licença Parental
        'marriage',         // Licença de Casamento
        'bereavement',      // Nojo / Falecimento
        'justified_absence', // Falta Justificada
    ],

    /**
     * Tipos de licenças que DEVEM descontar horas
     *
     * Faltas injustificadas descontam do banco de horas.
     */
    'unjustified_leave_types' => [
        'unjustified', // Falta Injustificada
    ],

    /**
     * Jornada diária padrão em minutos (8 horas)
     */
    'daily_work_hours' => 480, // 8 * 60
];
