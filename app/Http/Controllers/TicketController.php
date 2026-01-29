<?php

namespace App\Http\Controllers;

use App\DTOs\CreateTicketDTO;
use App\Enums\RolEnum;
use App\Http\Requests\addAgentTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketThreadResource;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AddAgentService;
use App\Services\CreateTicketService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller


{
    use AuthorizesRequests;
    public function index()
    {
        $ticket = Ticket::with('files', 'labels', 'user', 'agent')->latest()->paginate(10);
        return (TicketResource::collection($ticket));
    }
    public function store(StoreTicketRequest $request, CreateTicketService $service)
    {
        $validated = $request->validated();
        $user = $request->user()->id;
        $files = $request->file('files');
        $dto = new CreateTicketDTO(
            title: $validated['title'],
            userId: $user,
            priority: $validated['priority'],
            files: $files,
            labels: $validated['labels']
        );
        $ticket = $service->createTicket($dto);
        $ticket->load('files', 'labels', 'user');
        return (new TicketResource($ticket))
            ->additional(['message' => '¡Ticket creado con exito!'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $ticket->load([
            'files',
            'labels',
            'answers' => fn($query) => $query->latest(),
            'answers.user.rol',
            'answers.files'
        ]);
        $ticket->load(['user', 'agent']);
        return (new TicketThreadResource($ticket));
    }
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);
        $validated = $request->validated();
        $ticket->update($validated);
        $ticket->load('files', 'labels', 'user', 'agent');
        return (new TicketResource($ticket));
    }
    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);
        $ticket->files()->delete();
        $ticket->labels()->detach();
        $ticket->answers()->delete();
        $ticket->delete();

        return response()->noContent();
    }
    public function addAgent(Ticket $ticket, addAgentTicketRequest $request, AddAgentService $service)
    {
        $this->authorize('addAgent', $ticket);
        $validated = $request->validated();
        $ticket = $service->addAgent($ticket, $validated['agent_id']);
        $ticket->load('labels', 'files', 'user', 'agent');
        return (new TicketResource($ticket))
            ->additional(['message' => 'Agente añadido con exito'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
