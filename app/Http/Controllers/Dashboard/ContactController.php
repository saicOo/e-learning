<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class ContactController extends BaseController
{

    public function __construct()
    {
        $this->middleware(['permission:contacts_read'])->only('index','update');
        $this->middleware(['permission:contacts_delete'])->only('destroy');
    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/contacts",
     *      tags={"Dashboard Api Contacts"},
     *     summary="get all Contacts",
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name or email or phone contacts",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        $contacts = Contact::when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')->OrWhere('email','Like','%'.$request->search.'%')
            ->OrWhere('phone','Like','%'.$request->search.'%');
        })->latest('created_at')->get();

        return $this->sendResponse("",['contacts' => $contacts]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/contacts/{contact_id}",
     *      tags={"Dashboard Api Contacts"},
     *     summary="Read Message",
     *     @OA\Parameter(
     *         name="contact_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function update(Contact $contact){
        $contact->update([
            "is_read"=> 1
        ]);
        return $this->sendResponse("The message has been read successfully");
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/contacts/{contact_id}",
     *      tags={"Dashboard Api Contacts"},
     *     summary="Delete Contact",
     *     @OA\Parameter(
     *         name="contact_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }


}
