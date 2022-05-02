<?php

namespace App\Http\Controllers;

use App\Models\AdminBook;
use App\Models\Buy;
use App\Http\Controllers\Controller;
use App\Models\Detailbuy;
use Illuminate\Http\Request;

class BuyController extends Controller
{
    const NAME_SUBJECT1 = 'Buy';
    const NAME_SUBJECT2 = 'Buys';

    public function __construct()
    {
        $this->middleware('auth:api');
        //$this->middleware('auth:api', ['except' => ['index']]);
    }


    public function index(Request $request)
    {
        $idUser = auth()->user()->id;

        $buyInProccess = Buy::where([['id_users', $idUser],['state','1']])->first();
        $detailBuys = Detailbuy::with('adminBook')->where(['id_buys' => $buyInProccess->id])->get();
        return ['buy' => $buyInProccess, 'detailbuys' => $detailBuys, 'code' => 200];
    }

    public function store(Request $request)
    {
        $data = $request->input('data', null);
        $delete = isset($data['delete']);

        $idbook = $data['id_book'];
        $idUser = auth()->user()->id;
        $adminBook = AdminBook::where(['id' => $idbook])->first();
        $total = 0.0;

        if (!$adminBook)
            return ['msg' => 'No existe Libro'];

        elseif ($adminBook->number_copies != 0) {
            $buyInProccess = Buy::where([['id_users', $idUser],['state','1']])->first();

            if (!$buyInProccess)
                $buyInProccess =  Buy::create(['id_users' => $idUser, 'state' => 1]);

            $detailBuy = Detailbuy::where([['id_buys',$buyInProccess->id],['id_books',$idbook]])->first();
            if ($detailBuy){
                $detailBuy->quantity += ($delete? -1 : 1);
                $detailBuy->subtotal = $adminBook->price * $detailBuy->quantity;
                $detailBuy->quantity == 0? $detailBuy->delete() : $detailBuy->save();
            } else {
                $detailBuy = Detailbuy::create(['id_buys' => $buyInProccess->id, 'id_books' => $idbook , 'quantity' => 1, 'subtotal' => $adminBook->price]);
            }

            $adminBook->number_copies -= ($delete? -1 : 1); $adminBook->save();

            $detailBuys = Detailbuy::with('adminBook')->where(['id_buys' => $buyInProccess->id])->get();
            foreach ($detailBuys as $detail) {
                $total += $detail->subtotal;
            }

            $buyInProccess->total = $total; $buyInProccess->save();

            return ['buy' => $buyInProccess, 'detailbuys' => $detailBuys, 'code' => 200];
        }
        return ['code' => 400, 'msg' => 'No se encuentra el libro (Agotado)'];
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

        $buy = Buy::where($arrayFilter)->first();
        return $this->customResponse(true, $buy, $buy? 200 : 400,
            $this::NAME_SUBJECT1.($buy?' Consulta exitosa': ' No hay registros')
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

        $buy = Buy::find($id);
        if ($buy){
            $affected = Buy::where($arrayFilter)->update($request->input('data',null));
            $response = $this->customResponse(false, Buy::find($id), $affected? 200 : 400,
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
        $buy = Buy::find($id);
        if ($buy){
            $result = $buy->delete();
            return $this->customResponse(false, $buy, $result? 200 : 400,
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
