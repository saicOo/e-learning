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
            'teachers' => 'u',
            'assistants' => 'c,u,d,a',
            'students' => 'c,u,d',
            'courses' => 'c,u,d,a',
            'lessons' => 'c,u,d,a',
            'subscriptions' => 'c,d',
            'contacts' => 'r,d',
            'categories' => 'c,u,d',
            'quizzes' => 'c,d,a,rev',
            'questions' => 'c,d',
            'sessions' => 'r,c,u,d',
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
        'rev' => 'revision',
    ]
    // 'permission_structure' => [
    //     'cru_user' => [
    //         'profile' => 'c,r,u'
    //     ],
    // ],

];
// 'manager' => [
//     'teachers' => 'c,r,u,d',
//     'assistants' => 'c,r,u,d',
//     'students' => 'c,r,u,d',
//     'courses' => 'c,r,u,d,a',
//     'lessons' => 'c,r,u,d,a',
//     'subscriptions' => 'c,r,u,d',
//     'contacts' => 'r,d',
//     'categories' => 'c,r,u,d',
//     'quizzes' => 'c,r,u,d',
//     'questions' => 'c,r,u,d',
// ],
