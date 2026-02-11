<?php

namespace App\Http\Controllers;

use App\DTOs\CreateAnswerDTO;
use App\DTOs\UpdateAnswerDTO;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Ticket;
use App\Services\Answers\CreateAnswerService;
use App\Services\Answers\UpdateAnswerService;
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
     * Crear una respuesta.
     *
     * Almacena una nueva respuesta asociada a un ticket específico.
     *
     * @group Gestión de Tickets
     * @authenticated
     *
     * @urlParam ticket integer required El ID del ticket al que pertenece la respuesta.
     * @bodyParam body string required El contenido de la respuesta. Example: "He revisado el caso y necesitamos más información."
     * @bodyParam files array optional Archivos adjuntos. Example: [archivo1.png]
     * @bodyParam files.* file optional Archivo adjunto (png, jpeg, jpg, pdf, docx, xlsx, máx 10MB).
     *
     * @apiResource App\Http\Resources\AnswerResource
     * @apiResourceModel App\Models\Answer
     */
    public function store(StoreAnswerRequest $request, Ticket $ticket, CreateAnswerService $service)
    {
        // Correcto
        $this->authorize('create', [Answer::class, $ticket]);
        $validated = $request->validated();
        $user = $request->user();
        $files = $request->file('files');
        $dto = new CreateAnswerDTO(
            ticket_id: $ticket->id,
            user_id: $user->id,
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
     * Actualizar una respuesta.
     *
     * Actualiza el contenido de una respuesta existente. Solo el autor puede editarla.
     *
     * @group Gestión de Tickets
     * @authenticated
     *
     * @urlParam ticket integer required El ID del ticket.
     * @urlParam answer integer required El ID de la respuesta.
     * @bodyParam body string required El contenido actualizado. Example: "Corrección: El problema persiste."
     * @bodyParam files array optional Archivos adjuntos.
     * @bodyParam files.* file optional Archivo adjunto.
     *
     * @apiResource App\Http\Resources\AnswerResource
     * @apiResourceModel App\Models\Answer
     *
     * @response 403 scenario="No autorizado" {
     *   "message": "This action is unauthorized."
     * }
     *
     * @response 404 scenario="No encontrado" {
     *   "message": "No query results for model [App\\Models\\Answer]"
     * }
     */
    public function update(UpdateAnswerRequest $request, Ticket $ticket, Answer $answer, UpdateAnswerService $service)
    {
        $this->authorize('update', $answer);
        $files = $request->file('files');
        $dto = new UpdateAnswerDTO(
            body: $request->validated()['body'],
            files: $files,
        );
        $answer = $service->updateAnswer($answer, $dto);
        return (new AnswerResource($answer->load('user', 'files')))
            ->additional(['message' => '¡Answer editada con exito!'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Eliminar respuesta.
     *
     * Elimina una respuesta específica.
     *
     * @group Gestión de Tickets
     * @authenticated
     * @urlParam ticket integer required El ID del ticket.
     * @urlParam answer integer required El ID de la respuesta.
     *
     * @response 204 scenario="Eliminado con éxito" {}
     */
    public function destroy(Ticket $ticket, Answer $answer)
    {
        $this->authorize('delete', $answer);
        $answer->delete();

        return response()
            ->noContent();
    }
}
