<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    //
    public function __construct()
    {
        # 未登录用户可以访问的控制器
        $this->middleware('auth',[
            'except' =>['show','create','store','index','confirmEmail']
        ]);
        //未登录用户才能访问注册页面,登入后不能
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }


    public function create()
    {
        # code...
        return view('users.create');
    }

    public function show(User $user)
    {
        # code...
        return view('users.show',compact('user'));
    }

    public function store(Request $request)
    {
        # code...
         $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

         $user = User::create([
            'name' => $request-> name,
            'email' => $request ->email,
            'password'=>bcrypt($request->password),
         ]) ;

         $this->sendEmailConfirmationTo($user);
         session()->flash('success','验证邮件已经发送到你的注册邮箱上,请注意查收.');
         return redirect('/');



    }

    public function edit(User $user)
    {
        # code...
         $this->authorize('update', $user);
        return view('users.edit',compact('user'));
    }

    public function update(User $user, Request $request)
    //引用User类的$user变量和Request的$request变量为该方法的参数
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

         $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password){
            $data['password'] =bcrypt($request->password);
        }

        $user->update($data);

        session()->flash('sucess','个人资料更新成功!');

        return redirect()->route('users.show', $user->id);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

        public function destroy(User $user)
        {
            # code...
            $this->authorize('destroy',$user);
            $user->delete();
            session()->flash('session','成功删除用户!');
            return back();
        }

    public function sendEmailConfirmationTo($user)
    {
        # code...
        $view = 'email.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册BellaWeibo应用!请确认你的邮箱.";

        Mail::send($view,$data,function ($message) use ($to,$subject)
        {
            # code...
            $message->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
        # code...
        $user = User::where('activation_token',$token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success','恭喜你,激活成功了!');
        return redirect()->route('users.show',[$user]);
    }

}