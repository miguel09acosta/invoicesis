<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    const NAME_SUBJECT1 = 'User';
    const NAME_SUBJECT2 = 'Users';

    /*
    public function __construct()
    {
        $this->middleware('auth');
    }
    */


    public function index(Request $request)
    {
        $filter = $request->filter;
        if ($filter){
            $keysFilter = explode(',' , $filter);
            $values = explode(',' , $request->valuefilter);
            $arrayFilter = array_combine($keysFilter,$values);
        }else{
            $arrayFilter = [];
        }

        $users = User::where($arrayFilter)->get();
        return $this->customResponse(true, $users, $users? 200 : 400,
            $this::NAME_SUBJECT2.($users?' Consulta exitosa': ' No hay registros')
        );

    }

    public function store(Request $request)
    {
        if($request->massive) {
            $result = User::insert($request->input('data', null));
            return response(['status' => $result ? 200 : 400, 'message' => $result?'Inserccion ok':'error'])
                ->header('Content-Type', 'application/json');
        }else {
            $arraySet = $request->input('data',null);
            $arraySet['password'] = Hash::make($arraySet['password']);
            $user =  User::create($arraySet);
            $isSaved = $user->save();
            return $this->customResponse(false, $user, $isSaved? 200 : 400,
                $this::NAME_SUBJECT1.($isSaved?' Creado correctamente': ' No fue creado')
            );
        }
    }

    public function show($id,Request $request)
    {
        if(Auth::check())
            return Auth::user();
        else
            return 'logeado';
        $filter = $request->filter;
        if ($filter){
            $keysFilter = explode(',' , $filter);
            $values = explode(',' , $request->valuefilter);
            $arrayFilter = array_combine($keysFilter,$values);
        }else{
            $arrayFilter = ['id' => $id];
        }

        $user = User::where($arrayFilter)->first();
        return $this->customResponse(true, $user, $user? 200 : 400,
            $this::NAME_SUBJECT1.($user?' Consulta exitosa': ' No hay registros')
        );
    }

    public function update($id,Request $request)
    {
        $filter = $request->filter;
        if ($filter && $request->massive){
            $keysFilter = explode(',' , $filter);
            $values = explode(',' , $request->valuefilter);
            $arrayFilter = array_combine($keysFilter,$values);
        }else{
            $arrayFilter = ['id' => $id];
        }

        $affected = User::where($arrayFilter)->update($request->input('data',null));
        $response = $this->customResponse(false, User::find($id), $affected? 200 : 400,
            $affected.' '.$this::NAME_SUBJECT1.($affected?' Actualizado correctamente': ' No fue actualizado')
        );

        return $response;
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if ($user){
            $result = $user->delete();
            return $this->customResponse(false, $user, $result? 200 : 400,
                $result.' '.$this::NAME_SUBJECT1.($result?' Eliminado correctamente': ' No fue Eliminado')
            );
        }else{
            return response(['status' => 400, 'message' => 'No existe registro'])
                ->header('Content-Type', 'application/json');
        }
    }

    protected function customResponse($showData,$resultSet,$codeStatus,$msg)
    {
        $dataResult = $resultSet? $resultSet->toArray(): 'No existen datos con ese id';
        $dataResponse = [
            'status' => $codeStatus==200? 'success':'error',
            'code' => $codeStatus,
            'message' => $msg,
            'records' => $showData? $dataResult :
                ['id' => $resultSet->id]
        ];
        return response($dataResponse)
            ->header('Content-Type', 'application/json');
    }

}
