<?php

namespace App\Http\Controllers\Front;

use App\Models\User;
use App\Models\Course;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Notifications\UserNotice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\BaseController as BaseController;

class ContactController extends BaseController
{

        /**
     * @OA\Post(
     *     path="/api/contacts",
     *      tags={"Front Api Contact"},
     *     summary="Add New Contact",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="email", type="string", example="string"),
     *             @OA\Property(property="phone", type="string", example="string"),
     *             @OA\Property(property="subject", type="string", example="string"),
     *             @OA\Property(property="message", type="string", example="string"),
     *             @OA\Property(property="course_id", type="string", example="string"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     * )
     */
    public function store(Request $request)
    {
         //Validated
         $validate = Validator::make($request->all(),
         [
            'name' => 'nullable|string|max:255',
            'email' => 'required|string|max:255|email',
            'phone' => 'required|numeric|digits:11',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:500',
         ]);

         if($validate->fails()){
                return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
         }
         $request_data = $validate->validated();
         $users = User::get();
         $dataNotification = 'يوجد رسالة جديده من  '.$request->email.' يجب الاطلاع عليها !';
        if ($request->course_id){
            $course = Course::findOrFail($request->course_id);
            $dataNotification = "طلب للاشتراك في #".$course->id." ".$course->name;
            $request_data['message'] =  $dataNotification;
            $users = User::where('id','=',$course->user_id)->orWhere('user_id','=',$course->user_id)->orWhereRoleIs('manager')->get();
        }

        $contact = Contact::create($request_data);
        Notification::send($users, new UserNotice($dataNotification));
        return $this->sendResponse("Contact Created Successfully");
    }

}
