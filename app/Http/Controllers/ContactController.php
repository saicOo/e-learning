<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
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
             return response()->json([
                 'success' => false,
                 'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                 'message' => 'validation error',
                 'errors' => $validate->errors()
             ], 200);
         }

         $course = Contact::create($validate->validated());

         return response()->json([
             'status' => true,
             'message' => 'Contact Created Successfully',
         ], 200);
    }

}
