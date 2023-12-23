<?php
return [
     /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'manager' => [
            'teachers' => 'c,r,u,d',
            'assistants' => 'c,r,u,d',
            'students' => 'c,r,u,d',
            'courses' => 'c,r,u,d,a',
            'listens' => 'c,r,u,d,a',
            'subscriptions' => 'c,r,u,d',
            'contacts' => 'r,d',
            'categories' => 'c,r,u,d',
            'quizzes' => 'c,r,u,d',
            'questions' => 'c,r,d',
        ],
        'teacher' => [],
        'assistant' => [],
    ],
    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
        'a' => 'approve',
    ]
    // 'permission_structure' => [
    //     'cru_user' => [
    //         'profile' => 'c,r,u'
    //     ],
    // ],

];
