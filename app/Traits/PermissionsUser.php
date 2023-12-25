<?php
namespace App\Traits;

use App\Models\User;

trait PermissionsUser
{
    private function createPermissionsUser(User $user, $role){
        switch ($role) {
            case 'teacher':
                return [
                    'assistants_update',
                    'assistants_delete',
                    'assistants_create',
                    'assistants_approve',
                    'teachers_update',
                    'students_create',
                    'students_update',
                    'courses_create',
                    'courses_update',
                    'lessons_create',
                    'lessons_update',
                    'questions_create',
                    'questions_delete',
                    'quizzes_create',
                    'quizzes_delete',
                ];
                break;
            case 'assistant':
                return [
                    'assistants_update',
                    'students_create',
                    'students_update',
                    'subscriptions_create',
                    'subscriptions_update',
                ];
                break;

            default:
                # code...
                break;
        }
    }
}
