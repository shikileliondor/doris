<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Models\Question;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    /**
     * Store an anonymous question. Nothing about the sender is kept.
     */
    public function store(StoreQuestionRequest $request): JsonResponse
    {
        Question::create($request->validated());

        return response()->json(['sent' => true], 201);
    }
}
