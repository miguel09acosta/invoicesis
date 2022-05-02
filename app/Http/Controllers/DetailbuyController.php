<?php

namespace App\Http\Controllers;

use App\Models\Detailbuy;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DetailbuyController extends Controller
{
    const NAME_SUBJECT1 = 'Detailbuy';
    const NAME_SUBJECT2 = 'Detailbuys';

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

        $detailbuys = Detailbuy::where($arrayFilter)->get();
        return $this->customResponse(true, $detailbuys, $detailbuys? 200 : 400,
            $this::NAME_SUBJECT2.($detailbuys?' Consulta exitosa': ' No hay registros')
        );

    }

    public function store(Request $request)
    {
        if($request->massive) {
            $result = Detailbuy::insert($request->input('data', null));
            return response(['status' => $result ? 200 : 400, 'message' => $result?'Inserccion ok':'error'])
                ->header('Content-Type', 'application/json');
        }else {
            $detailbuy =  Detailbuy::create($request->input('data',null));
            $isSaved = $detailbuy->save();
            return $this->customResponse(false, $detailbuy, $isSaved? 200 : 400,
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

        $detailbuy = Detailbuy::where($arrayFilter)->first();
        return $this->customResponse(true, $detailbuy, $detailbuy? 200 : 400,
            $this::NAME_SUBJECT1.($detailbuy?' Consulta exitosa': ' No hay registros')
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

        $detailbuy = Detailbuy::find($id);
        if ($detailbuy){
            $affected = Detailbuy::where($arrayFilter)->update($request->input('data',null));
            $response = $this->customResponse(false, Detailbuy::find($id), $affected? 200 : 400,
                $affected.' '.$this::NAME_SUBJECT1.($affected?' Actualizado correctamente': ' No fue actualizado')
            );
            return $response;
        }else{
            return response(['status' => 400, 'message' => 'No existe registro'])
                ->header('Content-Type', 'application/json');
        }
    }

    public function destroy($id)
    {
        $detailbuy = Detailbuy::find($id);
        if ($detailbuy){
            $result = $detailbuy->delete();
            return $this->customResponse(false, $detailbuy, $result? 200 : 400,
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
                ['id' => $resultSet? $resultSet->id:'']
        ];
        return response($dataResponse)
            ->header('Content-Type', 'application/json');
    }

}
