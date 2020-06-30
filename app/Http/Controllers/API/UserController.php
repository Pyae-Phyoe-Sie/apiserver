<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use App\Models\RoleAndPermission;


class UserController extends Controller
{


    public $successStatus = 200;
    public $invalidStatus = 201;

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['user'] = $user;
            return response()->json(['success' => $success], $this->successStatus);
        }
        else{
            $success['msg'] = 'Invalid username or password!';
            return response()->json(['success'=> $success], 201);
        }
    }


    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'role' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        DB::beginTransaction();

        try {
            $user = User::create($input);

            if ($user->role == 1) {
                $permission = new RoleAndPermission;
                $permission->user_id = $user->id;
                $permission->url = "api/create";
                $permission->save();
                
                $permission = new RoleAndPermission;
                $permission->user_id = $user->id;
                $permission->url = "api/updateTicketInfo";
                $permission->save();
            } else {
                $permission = new RoleAndPermission;
                $permission->user_id = $user->id;
                $permission->url = "api/requestTicketInfo";
                $permission->save();

                $permission = new RoleAndPermission;
                $permission->user_id = $user->id;
                $permission->url = "api/approveTicket";
                $permission->save();
            }
            
            DB::commit();
            // all good
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            $success['msg'] =  "Something is error";
            return response()->json(['success'=>$success], $this->invalidStatus);
        }
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;


        return response()->json(['success'=>$success], $this->successStatus);
    }


    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }
}