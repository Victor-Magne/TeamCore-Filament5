<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Parâmetros de Recursos Humanos
    |--------------------------------------------------------------------------
    | Valores de negócio usados nos cálculos de assiduidade, horas e salários.
    | Altere aqui para ajustar as regras sem tocar no código dos serviços.
    */

    // Duração padrão da jornada diária em minutos (8 horas = 480 min)
    'default_daily_work_minutes' => 480,

    // Duração padrão da pausa de almoço em minutos
    'default_lunch_minutes' => 60,

    // Dias úteis médios por mês para cálculo do valor/hora
    'working_days_per_month' => 22,

    // Tolerância de atraso em minutos antes de ser registado como falta parcial
    'delay_tolerance_minutes' => 15,

    // Limite de atraso em minutos acima do qual passa a ser falta injustificada total
    'full_absence_threshold_minutes' => 60,

    // Número de atrasos parciais consecutivos que convertem numa falta total
    'consecutive_delays_limit' => 3,

    // Multiplicador do valor/hora para horas extraordinárias (50% de acréscimo)
    'extra_hours_multiplier' => 1.5,
];
