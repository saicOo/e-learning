<?php

namespace App\Rules;

use App\Models\Subscription;
use Illuminate\Contracts\Validation\Rule;

class ValidSubscription implements Rule
{
    protected $studentId;
    protected $courseId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($studentId,$courseId)
    {
        $this->studentId = $studentId;
        $this->courseId = $courseId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $currentDate = now();
        $subscription = Subscription::where('student_id', $this->studentId)
            ->where('course_id', $this->courseId)
            ->where('end_date', '>=', $currentDate)
            ->first();

        return $subscription == null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The student has an active subscription for this course.';
    }
}
