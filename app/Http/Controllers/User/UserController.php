<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\VerifyUser;
use Illuminate\Support\Facades\Auth;
use App\Notifications\WelcomeEmailNotification;


class UserController extends Controller
{
    //
    function create(Request $request){

        $request->validate([
           'name' => 'required',
           'email' => 'required|email|unique:users,email',
           'password' => 'required|min:5|max:30',
           'confirm_password' =>  'required|min:5|max:30|same:password'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = \Hash::make($request->password);
        $save = $user->save();
        $last_id = $user->id;

        $token = $last_id.hash('sha256', \Str::random(120));
        $verifyURL = route('user.verify', ['token' => $token, 'service' => 'Email_verification']);

        VerifyUser::create([
            'user_id' => $last_id,
            'token' => $token,
        ]);

        $message = 'Dear <b>' . $request->name. '</b>';
        $message .= 'Thanks for signing up, we just need you to verify your email to complete setting up your account';

        $mail_data = [
            'recipient' => $request->email,
            'fromEmail' => $request->email,
            'fromName' => $request->name,
            'subject' => 'Email Verification',
            'body' => $message,
            'actionLink' => $verifyURL
        ];

        \Mail::send('email-template', $mail_data, function($message) use ($mail_data){
           $message->to($mail_data['recipient'])
               ->from($mail_data['fromEmail'], $mail_data['fromName'])
               ->subject($mail_data['subject']);
        });

        if($save){
            return redirect()->back()->with('success', 'You need to verify your account. We have send you an activation link, please check your email');
        }else{
            return redirect()->back()->with('fail', 'Something went wrong, failed to register');
        }
    }

    public function verify(Request $request){

        $token = $request->token;
        $verifyUser = VerifyUser::where('token', $token)->first();
        if(!is_null($verifyUser)){
            $user = $verifyUser->user;

            if(!$user->email_verified){
                $verifyUser->user->email_verified = 1;
                $verifyUser->user->save();

                return redirect()->route('user.login')->with('info', 'Your email is verified successfully. You can now login')->with('verifiedEmail', $user->email);

            }else{
                return redirect()->route('user.login')->with('info', 'Your email is already verified. You can now login')->with('verifiedEmail', $user->email);
            }
        }

    }

    function check(Request $request){
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:5|max:30',
        ],[
            'email.exists' => 'This email is not exist on users table'
        ]);

        $creds = $request->only('email', 'password');
        if(Auth::guard('web')->attempt($creds)){
            return redirect()->route('user.home');
        }else{
            return redirect()->route('user.login')->with('fail', 'Incorrect credentials');
        }
    }

    function edit(Request $request){

        $user = User::find(base64_decode($request->id));

        return view('dashboard.user.edit', compact('user'));
    }

    function update(Request $request){

        $request->validate([
            'name' => 'required',
            'password' => 'required|min:5|max:30',
            'confirm_password' =>  'required|min:5|max:30|same:password'
        ]);

        $user = User::find(base64_decode($request->id));
        $user->name = $request->name;
        //check if password changed
        $hashed_password = \Hash::make($request->password);
        if($user->password !== $hashed_password){
            //if password change then let user know thru email
            $user->password = $hashed_password;

            $last_id = $user->id;

            $token = $last_id.hash('sha256', \Str::random(120));
            $verifyURL = route('user.verify', ['token' => $token, 'service' => 'Password_change']);

            VerifyUser::create([
                'user_id' => $last_id,
                'token' => $token,
            ]);

            $message = 'Dear <b>' . $request->name. '</b> ';
            $message .= 'We wanted to let you know that your password has changed.';

            $mail_data = [
                'recipient' => $user->email,
                'fromEmail' => $user->email,
                'fromName' => $request->name,
                'subject' => 'Your Password has changed',
                'body' => $message,
                'actionLink' => $verifyURL
            ];

            \Mail::send('email-template', $mail_data, function($message) use ($mail_data){
                $message->to($mail_data['recipient'])
                    ->from($mail_data['fromEmail'], $mail_data['fromName'])
                    ->subject($mail_data['subject']);
            });
        }
        $save = $user->save();

        if($save){
            return redirect()->route('user.home')->with('success', 'You updated user successfully');
        }else{
            return redirect()->route('user.home')->with('fail', 'Something went wrong, failed to update');
        }
    }

    function logout(Request $request){
        Auth::guard('web')->logout();
        return redirect('/');
    }
}
