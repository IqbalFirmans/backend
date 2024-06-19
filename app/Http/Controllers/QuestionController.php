<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function show(Request $request,$id) {

        $cek_login = User::where('login_tokens', $request->token)->first();

        if (!($cek_login && $request->token)) {
            return response()->json(['messages' => 'Unauthorized User'], 401);
        }

        $question = Question::where('id', $id)->with('options')->first();

        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        $form = $question->form;

        if ($cek_login->id !== $form->creator_id) {
            return response()->json(['message' => "You don't have access to this form"], 400);
        }

        $answers = $question->answers;

        $responses = [];
        foreach ($answers as $answer) {
            $responses[$answer->response->user->email] = $answer->answer;
        }

        return response()->json(["questions " => $question, "responses" => $responses], 200);
    }
}
