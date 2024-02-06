<?php
namespace App\Traits;

use App\Models\User;

trait PermissionsUser
{
    private function createPermissionsUser(User $user, $role){
        switch ($role) {
            case 'teacher':
                return [
                    'teachers_update',
                    'courses_update',
                    'lessons_create',
                    'lessons_update',
                    'lessons_delete',
                    'questions_create',
                    'questions_delete',
                    'quizzes_create',
                    'quizzes_delete',
                    'quizzes_revision',
                    'sessions_read',
                    'contacts_read',
                ];
                break;
            case 'assistant':
                return [
                    'assistants_update',
                    'students_create',
                    'students_update',
                    'subscriptions_create',
                    'sessions_read',
                    'sessions_create',
                    'sessions_update',
                    'sessions_delete',
                    'contacts_read',
                ];
                break;

            default:
                # code...
                break;
        }
    }
}
