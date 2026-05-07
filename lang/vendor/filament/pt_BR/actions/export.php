<?php

return [

    'label' => 'Exportar :label',

    'modal' => [

        'heading' => 'Exportar :label',

        'form' => [

            'columns' => [

                'label' => 'Colunas',

                'actions' => [

                    'select_all' => [
                        'label' => 'Selecionar tudo',
                    ],

                    'deselect_all' => [
                        'label' => 'Desselecionar tudo',
                    ],

                ],

                'form' => [

                    'is_enabled' => [
                        'label' => ':column ativada',
                    ],

                    'label' => [
                        'label' => ':column rótulo',
                    ],

                ],

            ],

        ],

        'actions' => [

            'export' => [
                'label' => 'Exportar',
            ],

        ],

    ],

    'notifications' => [

        'completed' => [

            'title' => 'Exportação concluída',

            'actions' => [

                'download_csv' => [
                    'label' => 'Descarregar .csv',
                ],

                'download_xlsx' => [
                    'label' => 'Descarregar .xlsx',
                ],

            ],

        ],

        'max_rows' => [
            'title' => 'A exportação é demasiado grande',
            'body' => 'Não pode exportar mais de 1 linha de cada vez.|Não pode exportar mais de :count linhas de cada vez.',
        ],

        'no_columns' => [
            'title' => 'Nenhuma coluna selecionada',
            'body' => 'Por favor selecione pelo menos uma coluna para exportar.',
        ],

        'started' => [
            'title' => 'Exportação iniciada',
            'body' => 'A sua exportação começou e 1 linha será processada em segundo plano. Receberá uma notificação com o link de descarregamento quando estiver concluída.|A sua exportação começou e :count linhas serão processadas em segundo plano. Receberá uma notificação com o link de descarregamento quando estiver concluída.',
        ],

    ],

    'file_name' => 'export-:export_id-:model',

];
