<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminBook;
use App\Http\Controllers\Controller;
use App\Models\AdminCategorie;
use Illuminate\Http\Request;

class AdminBookController extends Controller
{
    const NAME_SUBJECT1 = 'AdminBook';
    const NAME_SUBJECT2 = 'AdminBooks';

    /*
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['recomended']]);
        //$this->middleware('auth:api', ['except' => ['index']]);
    }
    */

    public function index(Request $request)
    {
        $hasBooksCopies = $request->exhausted? ['number_copies','=','0'] : ['number_copies','>','0'];
        $filter = $request->filter;
        $query = $request->keyword;

        if ($filter){
            $keysFilter = explode(',' , $filter);
            $values = explode(',' , $request->valuefilter);
            $arrayFilter = array_combine($keysFilter,$values);
        }else{
            $arrayFilter = [];
        }

        if ($query){
            $query = empty($arrayFilter)? ["tittle","like","%$query%"] : [$arrayFilter,["tittle","like","%$query%"]];
            $adminBooks = AdminBook::where(...$query)->where(...$hasBooksCopies)->get();
        }else{
            $adminBooks = AdminBook::where($arrayFilter)->where(...$hasBooksCopies)->get();
        }

        return $this->customResponse(true, $adminBooks, $adminBooks? 200 : 400,
            $this::NAME_SUBJECT2.($adminBooks?' Consulta exitosa': ' No hay registros')
        );
    }

    public function store(Request $request)
    {
        if($request->massive) {
            $result = AdminBook::insert($request->input('data', null));
            return response(['status' => $result ? 200 : 400, 'message' => $result?'Inserccion ok':'error'])
                ->header('Content-Type', 'application/json');
        }else {
            $adminBook =  AdminBook::create($request->input('data',null));
            $isSaved = $adminBook->save();
            return $this->customResponse(false, $adminBook, $isSaved? 200 : 400,
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

        $adminBook = AdminBook::where($arrayFilter)->first();
        return $this->customResponse(true, $adminBook, $adminBook? 200 : 400,
            $this::NAME_SUBJECT1.($adminBook?' Consulta exitosa': ' No hay registros')
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

        $adminBook = AdminBook::find($id);
        if ($adminBook){
            $affected = AdminBook::where($arrayFilter)->update($request->input('data',null));
            $response = $this->customResponse(false, AdminBook::find($id), $affected? 200 : 400,
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
        $adminBook = AdminBook::find($id);
        if ($adminBook){
            $result = $adminBook->delete();
            return $this->customResponse(false, $adminBook, $result? 200 : 400,
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

    public function recomended(Request $request)
    {
        //enviar objeto de libros recomendados y las categorias como books1: {,,,,} books2 {,,,,} categories {,,}
        if (auth()->check()){
            $user = auth()->user();
            $arrayPreferences = $user->preferences;
            $arrayPreferences = $arrayPreferences == null? [1,2]: $arrayPreferences;
        } else {
            $arrayPreferences = [1,2];
        }

        $cat1 = AdminCategorie::find($arrayPreferences[0]);
        $cat2 = AdminCategorie::find($arrayPreferences[1]);
        $books1 = AdminBook::where(['categories_id' => $arrayPreferences[0]])->take(4)->get();
        $books2 = AdminBook::where(['categories_id' => $arrayPreferences[1]])->take(4)->get();
        $booksNew = AdminBook::orderBy('created_at','DESC')->take(4)->get();

        return ['category1' => $cat1, 'category2' => $cat2, 'listRec1' => $books1, 'listRec2' => $books2, 'listNews' => $booksNew];
    }

}
