<?php

namespace App\Http\Controllers\Api;

use App\Subtitle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SubtitleController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function getAll(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = Subtitle::where('id','like', '%'.$request->search.'%')
                    ->orWhere('name','like', '%'.$request->search.'%')
                    ->orWhere('year','like', '%'.$request->search.'%')
                    ->orWhere('category','like', '%'.$request->search.'%')
                    ->orWhere('status','like', '%'.$request->search.'%')->with('category');
                    
        $subtitles = $query->paginate(100);

        return response()->json(['success'=>$subtitles], $this->successStatus);
    }

    public function find($id){

        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $subtitle = Subtitle::with('category')->find($id);

        if($subtitle){
            return response()->json(['success'=>$subtitle], $this->successStatus);
        }else{
            return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);
        }
    }

    public function store(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }
        
        $validator = $this->validateSubtitle($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        $input = $request->all();
        $input['status'] = 'PENDENTE';
        $input['author'] = $request->user()->id;
        $subtitle = Subtitle::create($input); 

        if($subtitle){
            return response()->json(['success'=>['Cadastro efetuado com sucesso']], $this->successStatus); 
        }
        return response()->json(['error'=> ['Ocorreu um problema inesperado por favor tente novamente!']], $this->errorStatus);
    }

    public function update(Request $request) 
    {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateSubtitle($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        
        $subtitle = Subtitle::find($request->id);

        if($subtitle){

            $subtitle->update($request->all());

            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }

        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }

    private function validateSubtitle($request){
        return Validator::make($request->all(), [ 
            'name' => 'required|string', 
            'year' => 'required|integer', 
            'url' => 'required|string', 
            'image' => 'nullable', 
            'status' => 'nullable', 
            'author' => 'nullable', 
            'category' => 'required'
        ]);
    }

}
