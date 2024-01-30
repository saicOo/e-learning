<?php

namespace App\Http\Controllers\Front;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

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
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email',
            'phone' => 'required|numeric|digits:11',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:500',
         ]);

         if($validate->fails()){
                         return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);

         }

         $contact = Contact::create($validate->validated());
        return $this->sendResponse("Contact Created Successfully");
    }

}
