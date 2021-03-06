<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Laravel\Passport\HasApiTokens;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\UserData;
use App\Models\Document;
use Storage;
use App\Mail\sendMail;
use Illuminate\Support\Facades\Mail;

class ApiController extends Controller
{
    public function register(Request $request)
    {
    	//Validate data
        $data = $request->only('name', 'email','type', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $user = User::create([
        	'name' => $request->name,
        	'email' => $request->email,
            'type' => $request->type,
        	'password' => Hash::make($request->password)
        ]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }
 
    public function authenticate(Request $request)
    {
        // return response()->json(['hello']);
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['success'=>false,'error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        
        $user = null;
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
            
        } catch (JWTException $e) {
            return response()->json($e);
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                    'error' => $e,
                ], 500);
        }
       
 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token
        ]);
    }
    
    public function is_registered(Request $request){
        $user = JWTAuth::authenticate($request->token);

        $data = UserData::where('user_id',$user->id)->first();

        $register = true;
        if(!$data) $register=false;
        return response()->json(['is_registered' => $register]);
    }
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
 
        $user = JWTAuth::authenticate($request->token);
        
        return response()->json(['user' => $user]);
    }

    public function update_user_data(Request $request){
        $user = JWTAuth::authenticate($request->token);
        $user_data = UserData::where('user_id',$user->id)->first();

        if(!$user_data ) $user_data = new UserData;
        $user_data->user_id = $user->id;
        $data = $request->except(['_token','token']);
        // return response()->json($data,200);
        $arr= [];
        foreach($data as $key => $value){
            $user_data->$key = $value;
            $arr[]=$key;
        }
        // return response()->json($arr,200);
        if($user_data->save()) {
            $user_data->email = $user->email;
            return response()->json(['success'=>true,'data' => $user_data],200);
        }
        else return response()->json(['success'=>false],200);

    }

    public function get_user_data(Request $request){
        $user = JWTAuth::authenticate($request->token);
        if(!$user) return reponse()->json(['user not found!'],200);
        $user_data = UserData::where('user_id',$user->id)->first();
        if(!$user_data) $user_data = new \StdClass();
        $user_data->email = $user->email;
        $user_data->name = $user->name;
        return response()->json($user_data,200);
    }

    public function fetch_students(){
        $students = User::where('type','student')->get();
        return response()->json($students,200);
    }
    public function upload_profile_image(Request $request){
        $user = JWTAuth::authenticate($request->token);
        // $path = Storage::putFile('public/profile_images', $request->file('image'));
        
        $image = $request->file('image');
        $fileName = $image->getClientOriginalName();
        $image->move(public_path('/images'),$fileName);
        // $path_elements = explode('/',$path);
        // $fileName = $path_elements[count($path_elements)-1];
        $image_url = url('/images').'/'.$fileName;
        $user_data = UserData::where('user_id',$user->id)->first();
        if(!$user_data) $user_data = new UserData;
        $user_data->user_id = $user->id;
        $user_data->profile_image = $image_url;
        $user_data->save();
        // $path = $request->file('image')->store('profile_images');
        return response()->json(['url' => $image_url ],200);
    }

    public function store_document(Request $request){
        $user = JWTAuth::authenticate($request->token);
        if(!$user) return response()->json(['user not found']);
        $doc = new Document;
        $doc->name = $request->name;
        $doc->type = $request->type;
        $doc->hash = $request->hash;
        $doc->user_id = $user->id;
        $doc->save();

        return response()->json(['document saved successfully!']);
    }
    public function get_documents(Request $request,$user_id){
        $docs = Document::where('user_id',$user_id)->get();
        return response()->json($docs);
    }
    
    public function verify_document(Request $request, $doc_id){
        $user = JWTAuth::authenticate($request->get('token'));

        if($user->type != 'institute')
        return response()->json(['please log in with a institute account!']);

        $doc = Document::find($doc_id);
        $doc->is_verified = true;
        $doc->save();

        return response()->json(['Document Verified!']);

    }

    public function reject_document(Request $request, $doc_id){
        $user = JWTAuth::authenticate($request->get('token'));

        if($user->type != 'institute')
        return response()->json(['please log in with a institute account!']);

        $doc = Document::find($doc_id);
        $doc->is_rejected = true;
        $doc->save();

        return response()->json(['Document Verified!']);

    }

    public function delete_document(Request $request, $doc_id){
        $user = JWTAuth::authenticate($request->get('token'));

        if($user->type != 'institute')
        return response()->json(['please log in with a institute account!']);

        $doc = Document::find($doc_id);
        $doc->delete();

        return response()->json(['Document Deleted!']);
    }

    public function send_mail(Request $request,$student_id){
        $user = User::find($student_id);
        $ins = JWTAuth::authenticate($request->token);

        if(!$ins) return response()->json(['Please log in with a institute Account !!']);

        Mail::to($user->email)->send(new SendMail($request->content));
        return response()->json(['Mail Sent Successfully!']);
    }
}