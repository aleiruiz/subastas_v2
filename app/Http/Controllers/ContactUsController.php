<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactUsRequest;
use App\Mail\Guest\ContactUs;
use Illuminate\Support\Facades\Mail;


class ContactUsController extends Controller
{
    public function create()
    {
        $data['title'] = __('Contact');

        return view('frontend.global_access.contact_us', $data);
    }

    public function store(ContactUsRequest $request)
    {
        $parameters = $request->only('name', 'phone_number', 'email', 'subject', 'message');
        $adminMail = settings('admin_receive_email');

        if ($parameters) {
            Mail::to($adminMail)->send(new ContactUs($parameters));
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Message has been send successfully'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Unable to send message'));

    }
}
