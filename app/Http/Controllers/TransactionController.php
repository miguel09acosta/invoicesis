<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TransactionController extends Controller
{
    const NAME_SUBJECT1 = 'Transaction';
    const NAME_SUBJECT2 = 'Transactions';

    
    public function __construct()
    {
        $this->middleware('auth:api');
        //$this->middleware('auth:api', ['except' => ['index']]);
    }


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

        $transactions = Transaction::where($arrayFilter)->get();
        return $this->customResponse(true, $transactions, $transactions? 200 : 400,
            $this::NAME_SUBJECT2.($transactions?' Consulta exitosa': ' No hay registros')
        );

    }

    public function store(Request $request)
    {
        if($request->massive) {
            $result = Transaction::insert($request->input('data', null));
            return response(['status' => $result ? 200 : 400, 'message' => $result?'Inserccion ok':'error'])
                ->header('Content-Type', 'application/json');
        }else {
            $arraySet = $request->input('data',null);
            $transaction =  Transaction::create($arraySet);
            $isSaved = $transaction->save();
            return $this->customResponse(false, $transaction, $isSaved? 200 : 400,
                $this::NAME_SUBJECT1.($isSaved?' Creado correctamente': ' No fue creado')
            );
        }
    }

    public function show($id,Request $request)
    {
        $filter = $request->filter;
        if ($filter){
            $keysFilter = explode(',' , $filter);
            $values = explode(',' , $request->valuefilter);
            $arrayFilter = array_combine($keysFilter,$values);
        }else{
            $arrayFilter = ['id' => $id];
        }

        $transaction = Transaction::where($arrayFilter)->first();
        return $this->customResponse(true, $transaction, $transaction? 200 : 400,
            $this::NAME_SUBJECT1.($transaction?' Consulta exitosa': ' No hay registros')
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
        $dataReq = $request->input('data',null);
        $affected = Transaction::where($arrayFilter)->update($dataReq);
        $response = $this->customResponse(false, Transaction::find($id), $affected? 200 : 400,
            $affected.' '.$this::NAME_SUBJECT1.($affected?' Actualizado correctamente': ' No fue actualizado')
        );

        return $response;
    }

    public function destroy($id)
    {
        $transaction = Transaction::find($id);
        if ($transaction){
            $result = $transaction->delete();
            return $this->customResponse(false, $transaction, $result? 200 : 400,
                $result.' '.$this::NAME_SUBJECT1.($result?' Eliminado correctamente': ' No fue Eliminado')
            );
        }else{
            return response(['status' => 400, 'message' => 'No existe registro'], 400)
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
        return response($dataResponse, $codeStatus==200? 200 : 400)
            ->header('Content-Type', 'application/json');
    }

}
