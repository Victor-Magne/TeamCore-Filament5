<?php

return [
    'tree' => [
        'empty_label' => 'Nenhum dado',
    ],

    'action' => [
        'create_child_node' => 'Criar sub-unidade',

        'delete_failed_title' => 'Falha ao eliminar',
        'delete_failed_body_has_child' => 'Existem sub-unidades associadas. Elimine-as primeiro.',

        'move_node' => 'Mover',
        'move_node_success' => 'Unidade movida com sucesso',
        'move_node_failed' => 'Falha ao mover unidade',
        'move_node_failed_body_depth' => 'Falha ao mover: o nível destino não pode exceder o nível máximo permitido (:level).',

        'fix_nestedset' => 'Corrigir árvore',
        'fix_nestedset_success' => 'Árvore corrigida com sucesso',
    ],

    'field' => [
        'parent_select_field' => 'Unidade superior',
        'parent_select_field_placeholder' => 'Selecione a unidade superior',
        'parent_select_field_empty_label' => 'Nenhuma unidade superior encontrada',
    ],
];
