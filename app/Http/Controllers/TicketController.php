<?php

namespace App\Http\Controllers;

use App\DTOs\CreateTicketDTO;
use App\Enums\Priority;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\CreateTicketService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
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
        return (new TicketResource($ticket))
        ->additional(['message' => '¡Ticket creado con exito!'])
        ->response()
        ->setStatusCode(Response::HTTP_CREATED);
    }
}
