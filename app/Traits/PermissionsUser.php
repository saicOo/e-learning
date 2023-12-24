<?php
namespace App\Traits;

use App\Models\User;

trait PermissionsUser
{
    private function createPermissionsUser(User $user, $role){
        switch ($role) {
            case 'teacher':
                return [
                    'students_read',
                    'subscriptions_read',
                    'assistants_read',
                    'courses_create',
                    'courses_read',
                    'courses_update',
                    'lessons_create',
                    'lessons_read',
                    'lessons_update',
                    'categories_read',
                    'questions_read',
                    'questions_create',
                    'questions_update',
                    'quizzes_read',
                    'quizzes_update',
                    'quizzes_delete',
                ];
                break;
            case 'assistant':
                return [
                    'students_create',
                    'students_read',
                    'students_update',
                    'subscriptions_create',
                    'subscriptions_read',
                    'subscriptions_update',
                    'categories_read',
                    'quizzes_read',
                    'questions_read',
                ];
                break;

            default:
                # code...
                break;
        }
    }
}
