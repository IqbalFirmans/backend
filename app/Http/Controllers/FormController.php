<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\Option;
use App\Models\Question;
use App\Models\FormDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{

    public function getAll(Request $request)
    {
        if (!auth()->guard('sanctum')->check()) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        $forms = Form::withCount('respondens')->get();

        $formattedForms = $forms->map(function ($form) {
            return [
                'id' => $form->id,
                'name' => $form->name,
                'description' => $form->description,
                'respondens_count' => $form->respondens_count,
                'expired' => $form->expired
            ];
        });

        return response()->json(['forms' => $formattedForms]);

        // return response()->json(['forms' => Form::all()]);
    }

    public function getMyForm(Request $request)
    {
        $user = User::where('login_tokens', $request->token)->first();

        if (!($user && $request->token)) {
            return response()->json(['message' => 'Unauthorized User'], 401);
        }

        $form = Form::where('creator_id', $user->id)->withCount('respondens')->get();

        return response()->json(['MyForm' => $form]);
    }

    public function show(Request $request, $id)
    {
        $cek_login = User::where('login_tokens', $request->token)->first();

        if (!($cek_login && $request->token)) {
            return response()->json(['messages' => 'Unauthorized User'], 401);
        }

        $form = Form::where('id', $id)->with('respondens', 'questions')->first();

        if (!$form) {
            return response()->json(['message' => 'Form not found'], 404);
        }

        // validasi akses pengguna terhadap form
        if ($cek_login->id !== $form->creator_id) {
            return response()->json(['message' => "You don't have any access to this form"], 400);
        }

        foreach ($form->form_domain as $domain) {
            $domains[] = $domain->domain;
        }

        foreach ($form->respondens as $responden) {
            $respondens[] = [
                'name' => $responden->user->name,
                'email' => $responden->user->email
            ];
        }

        // Mengganti data dalam $form dengan hasil yang telah dimanipulasi
        $form->respondens = $respondens;
        $form->form_domain = $domains;

        // mengambil properti yang diperlukan dari $form
        $formData = [
            'name' => $form->name,
            'description' => $form->description,
            'domain' => $domains,
            'expired' => $form->expired,
            'respondens' => $respondens,
            'questions' => $form->questions
        ];

        return response()->json(['form' => $formData], 200);
    }

    public function request(Request $request)
    {
        $cek_login = User::where('login_tokens', $request->token)->first();

        if (!($cek_login && $request->token)) {
            return response()->json(['messages' => 'Unauthorized User'], 401);
        }

        $rules_form = [
            'name' => 'required',
            'description' => 'required',
            'type' => 'required|in:public,private',
            'expired' => 'required|date',
            'questions' => 'required|array',
            'questions.*.question' => 'required',
            'questions.*.description' => 'required',
            'questions.*.type' => 'required|in:text,number,checkbox,select,textarea',
            'questions.*.is_required' => 'required|boolean',
            'questions.*.options' => 'required_if:questions.*.type,checkbox,select'
        ];


        if ($request->type === 'private') {
            $rules_form['domains'] = 'required|array';
        }

        $validation = Validator::make($request->all(), $rules_form);

        if ($validation->fails()) {
            $errors = [];

            foreach ($validation->errors()->messages() as $field => $messages) {
                $errors[$field] = $messages[0];
            }

            return response()->json(['messages' => 'Invalid field', 'errors' => $errors], 400);
        }

        $form = Form::create([
            'name' => $request->name,
            'creator_id' => $cek_login->id,
            'description' => $request->description,
            'type' => $request->type,
            'expired' => $request->expired
        ]);

        if ($request->type === 'private') {

            foreach ($request->domains as $domain) {
                FormDomain::create([
                    'form_id' => $form->id,
                    'domain' => $domain
                ]);
            }
        }

        foreach ($request->questions as $question) {
            $questions = Question::create([
                'form_id' => $form->id,
                'question' => $question['question'],
                'description' => $question['description'],
                'type' => $question['type'],
                'is_required' => $question['is_required']
            ]);

            if ($question['type'] == 'checkbox' || $question['type'] = 'select') {
                $options = explode(",", $question['options']);

                foreach ($options as $option) {
                    Option::create([
                        'question_id' => $questions->id,
                        'option' => $option
                    ]);
                }
            }
        }

        return response()->json(['messages' => 'Form successfully created'], 200);
    }
}
