<?php

namespace App\Http\Controllers;

use App\DTOs\CreateAnswerDTO;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Ticket;
use App\Services\CreateAnswerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use AuthorizesRequests;
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnswerRequest $request, Ticket $ticket, CreateAnswerService $service)
    {
        // Correcto
        $this->authorize('create', [Answer::class, $ticket]);
        $validated = $request->validated();
        $user = $request->user()->id;
        $files = $request->file('files');
        $dto = new CreateAnswerDTO(
            ticket_id: $ticket->id,
            user_id: $user,
            body: $validated['body'],
            files: $files,
        );
        $answer = $service->CreateAnswer($dto);
        $ticket->update(['last_reply_at' => now()]);
        return (new AnswerResource($answer))
            ->additional(['message' => '¡Respuesta creada con éxito!'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
