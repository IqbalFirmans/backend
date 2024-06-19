<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\Answer;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{
    public function get_response(Request $request)
    {
        $cek_login = User::where('login_tokens', $request->token)->first();

        if (!($cek_login && $request->token)) {
            return response()->json(['message' => 'Unauthorized User'], 401);
        }

        $response = Response::where('form_id', $request->form_id)
                            ->where('user_id', $request->user_id)->first();

        if (!$response) return response()->json(['message' => 'Form not found'], 404);

        if ($response->form->creator_id !== $cek_login->id) return response()->json(['message' => "You don't have access to this form"], 400);

        $data = [];
        foreach ($response->answers as $answer) {
            $data[$answer->question->question] = $answer->answer;
        }

        return response()->json(['responses' => $data]);
    }

    public function response(Request $request)
    {
        $cek_login = User::where('login_tokens', $request->token)->first();

        if (!($cek_login && $request->token)) {
            return response()->json(['message' => 'Unauthorized User'], 401);
        }

        $rules = [
            'form_id' => 'required|numeric',
            'responses' => 'required|array',
            'responses.*' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ]);
        }

        $form = Form::where('id', $request->form_id)->first();

        if ($cek_login->id !== $form->creator_id) {
            return response()->json(['message' => "You don't have access to answer this form"], 400);
        }

        // if (!$response || Carbon::now()->greaterThan($response->form->expired)) {
        //     return response()->json(['messages' => 'Form is already expired'], 400);
        // }
        if (!$form || $form->expired < now()) {
            return response()->json(['message' => 'Form is already expired'], 400);
        }

        $response = Response::create([
            'user_id' => $cek_login->id,
            'form_id' => $request->form_id
        ]);

        foreach ($request->responses as $key => $answer) {
            Answer::create([
                'response_id' => $response->id,
                'question_id' => $key,
                'answer' => $answer
            ]);
        }

        return response()->json(['message' => 'Answer successfully created'], 200);
    }
}
