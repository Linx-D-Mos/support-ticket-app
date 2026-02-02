<?php

namespace App\Http\Controllers;

use App\DTOs\CreateTicketDTO;
use App\DTOs\UpdateTicketDTO;
use App\Enums\Status;
use App\Http\Requests\addAgentTicketRequest;
use App\Http\Requests\AssignaAgentRequest;
use App\Http\Requests\AssignAgentRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketThreadResource;
use App\Models\Ticket;
use App\Services\AddAgentService;
use App\Services\AssignAgentService;
use App\Services\CreateTicketService;
use App\Services\UpdateTicketService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class TicketController extends Controller


{
    use AuthorizesRequests;
    /**
     * Listar Tickets
     * 
     * Obtiene una lista paginada de tickets. Permite filtrar por estado, prioridad y búsqueda de texto.
     * Los tickets se retornan ordenados por los más recientes primero.
     *
     * @group Gestión de Tickets
     * @authenticated
     * @queryParam status string Filtra por estado del ticket. Valores permitidos: open, in_progress, resolved, closed. Example: open
     * @queryParam priority string Filtra por prioridad del ticket. Valores permitidos: low, medium, high, urgent. Example: high
     * @queryParam search string Busca texto en el título o descripción del ticket. Example: impresora
     * @queryParam page integer Número de página para la paginación. Example: 1
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Mi impresora no funciona",
     *       "priority": "high",
     *       "status": "open",
     *       "customer": {
     *         "id": 5,
     *         "name": "Juan Pérez"
     *       },
     *       "agent": {
     *         "id": 2,
     *         "name": "Agente Soporte"
     *       },
     *       "last_reply_at": "2024-06-01T10:30:00Z",
     *       "resolve_at": null,
     *       "close_at": null
     *     }
     *   ],
     *   "links": {
     *     "first": "http://example.com/api/tickets?page=1",
     *     "last": "http://example.com/api/tickets?page=5",
     *     "prev": null,
     *     "next": "http://example.com/api/tickets?page=2"
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 5,
     *     "per_page": 10,
     *     "to": 10,
     *     "total": 50
     *   }
     * }
     */
    public function index(Request $request)
    {
        $tickets = Ticket::with(['files', 'labels', 'user', 'agent'])
        ->status($request->query('status'))
        ->priority($request->query('priority'))
        ->search($request->query('search'))
        ->latest()
        ->paginate(10);
        return (TicketResource::collection($tickets));
    }

    /**
     * Crear un nuevo ticket.
     *
     * Devuelve un ticket creado. Recibe los datos esenciales para la creación de un ticket.
     *
     * @group Gestión de Tickets
     * @authenticated
     *
     * @bodyParam title string required El título del ticket. Example: "Mi impresora no funciona"
     * @bodyParam priority string required Prioridad del ticket. Valores permitidos: low, medium, high, urgent. Example: "high"
     * @bodyParam labels array required Etiquetas asociadas al ticket. Example: ["hardware", "impresora"]
     * @bodyParam labels.* string required Nombre de la etiqueta. Example: "hardware"
     * @bodyParam files array optional Archivos adjuntos. Example: [archivo1.png, archivo2.pdf]
     * @bodyParam files.* file optional Archivo adjunto (png, jpeg, jpg, pdf, docx, xlsx, máx 10MB)
     *
     * @response 201 scenario="Ticket creado con éxito" {
     *   "data": {
     *     "id": 1,
     *     "title": "Mi impresora no funciona",
     *     "priority": "high",
     *     "status": "open",
     *     "customer": {
     *       "id": 5,
     *       "name": "Juan Pérez"
     *     },
     *     "agent": null,
     *     "last_reply_at": null,
     *     "resolve_at": null,
     *     "close_at": null
     *   },
     *   "message": "¡Ticket creado con exito!"
     * }
     *
     * @response 422 scenario="Datos inválidos" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "title": ["El campo título es obligatorio."],
     *     "priority": ["La prioridad es obligatoria."],
     *     "labels": ["Debe proporcionar al menos una etiqueta."]
     *   }
     * }
     */
    public function store(StoreTicketRequest $request, CreateTicketService $service)
    {
        $validated = $request->validated();
        $user_id = $request->user()->id;
        $files = $request->file('files');
        $dto = new CreateTicketDTO(
            title: $validated['title'],
            userId: $user_id,
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
    
    
    /**
     * Mostrar un ticket con su hilo de respuestas.
     *
     * Devuelve la información detallada de un ticket, incluyendo archivos, etiquetas, respuestas y usuarios relacionados.
     *
     * @group Tickets
     * @authenticated
     *
     * Recupera la información de un ticket específico junto con su hilo de respuestas.
     *
     * @urlParam ticket int required El ID del ticket a mostrar. Example: 1
     *
     * @response 200 scenario="Ticket encontrado" {
     *   "data": {
     *     "id": 1,
     *     "title": "Mi impresora no funciona",
     *     "priority": "high",
     *     "status": "open",
     *     "customer": {
     *       "id": 5,
     *       "name": "Juan Pérez"
     *     },
     *     "agent": null,
     *     "answers": [
     *       {
     *         "id": 10,
     *         "body": "¿Has intentado reiniciar la impresora?",
     *         "user": {
     *           "id": 2,
     *           "name": "Agente Soporte",
     *           "rol": "agent"
     *         },
     *         "files": []
     *       }
     *     ],
     *     "files": [],
     *     "labels": ["hardware", "impresora"]
     *   }
     * }
     *
     * @response 403 scenario="No autorizado" {
     *   "message": "This action is unauthorized."
     * }
     *
     * @response 404 scenario="Ticket no encontrado" {
     *   "message": "No query results for model [App\\Models\\Ticket] 9999"
     * }
     *
     * @responseField id int ID del ticket
     * @responseField title string Título del ticket
     * @responseField priority string Prioridad del ticket
     * @responseField status string Estado del ticket
     * @responseField customer object Información del cliente
     * @responseField agent object Información del agente asignado
     * @responseField answers array Respuestas del ticket
     * @responseField files array Archivos adjuntos
     * @responseField labels array Etiquetas asociadas
     */
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

    
    /**
     * Actualizar un ticket existente.
     *
     * Permite modificar los datos de un ticket. Solo usuarios autorizados pueden realizar esta acción.
     *
     * @group Gestión de Tickets
     * @authenticated
     * @urlParam ticket int required ID del ticket. Example: 1
     * @bodyParam title string optional Nuevo título del ticket. Example: "Impresora no imprime"
     * @bodyParam priority string optional Nueva prioridad. Valores permitidos: low, medium, high, urgent. Example: "medium"
     * @bodyParam labels array optional Etiquetas asociadas al ticket. Example: ["hardware", "impresora"]
     * @bodyParam labels.* string optional Nombre de la etiqueta. Example: "hardware"
     * @bodyParam files array optional Archivos adjuntos. Example: [archivo1.png, archivo2.pdf]
     * @bodyParam files.* file optional Archivo adjunto (png, jpeg, jpg, pdf, docx, xlsx, máx 10MB)
     * @response 200 scenario="Ticket actualizado con éxito" {
     *   "data": {
     *     "id": 1,
     *     "title": "Impresora no imprime",
     *     "priority": "medium",
     *     "status": "open",
     *     "customer": {
     *       "id": 5,
     *       "name": "Juan Pérez"
     *     },
     *     "agent": null,
     *     "last_reply_at": null,
     *     "resolve_at": null,
     *     "close_at": null
     *   }
     * }
     * @response 403 scenario="No autorizado" {
     *   "message": "This action is unauthorized."
     * }
     * @response 404 scenario="Ticket no encontrado" {
     *   "message": "No query results for model [App\\Models\\Ticket]"
     * }
     * @response 422 scenario="Datos inválidos" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "priority": ["La prioridad debe ser: low, medium, high, urgent."]
     *   }
     * }
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket,UpdateTicketService $service)
    {
        $this->authorize('update', $ticket);
        $validated = $request->validated();
        $dto = new UpdateTicketDTO(
            ticket_id: $ticket->id,
            user_id: $request->user()->id,
            title: $validated['title'] ?? $ticket->title,
            priority: $validated['priority'] ?? $ticket->priority->value,
            labels: $validated['labels'] ?? null,
        );
        $ticket = $service->updateTicket($dto);
        return (new TicketResource($ticket))
        ->additional(['message' => 'Ticket actualizado con exito'])
        ->response()
        ->setStatusCode(Response::HTTP_OK);
    }


    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);
        $ticket->delete();

        return response()->noContent();
    }
    public function addAgent(Ticket $ticket, addAgentTicketRequest $request, AddAgentService $service)
    {
        $this->authorize('addAgent', $ticket);
        $ticket = $service->addAgent($ticket, $request->agent_id);
        return (new TicketResource($ticket->load('labels', 'files', 'user', 'agent')))
            ->additional(['message' => 'Agente añadido con exito'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Resolver un ticket.
     *
     * Cambia el estado del ticket a "resuelto" y registra la fecha de resolución.
     * Solo usuarios autorizados pueden realizar esta acción.
     *
     * @group Gestión de Tickets
     * @authenticated
     * @urlParam ticket int required ID del ticket. Example: 1
     * 
     * @response 200 scenario="Ticket resuelto con éxito" {
     *   "data": {
     *     "id": 1,
     *     "title": "Mi impresora no funciona",
     *     "priority": "high",
     *     "status": "resolved",
     *     "customer": {
     *       "id": 5,
     *       "name": "Juan Pérez"
     *     },
     *     "agent": null,
     *     "last_reply_at": null,
     *     "resolve_at": "2024-06-01T12:00:00Z",
     *     "close_at": null
     *   },
     *   "message": "¡Ticket resuelto con exito!"
     * }
     * 
     * @response 403 scenario="No autorizado" {
     *   "message": "This action is unauthorized."
     * }
     * 
     * @response 404 scenario="Ticket no encontrado" {
     *   "message": "No query results for model [App\\Models\\Ticket]"
     * }
     * 
     * @responseField id int ID del ticket
     * @responseField title string Título del ticket
     * @responseField priority string Prioridad del ticket
     * @responseField status string Estado del ticket
     * @responseField resolve_at string Fecha de resolución
     */
    public function resolve(Ticket $ticket)
    {
        $this->authorize('resolve', $ticket);
        $ticket->update(
            [
                'status' => Status::RESOLVED,
                'resolve_at' => now(),
            ]
        );
        return (new TicketResource($ticket->load('labels', 'files', 'user', 'agent')))
            ->additional(['message' => '¡Ticket resuelto con exito!'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    /**
     * Cerrar un ticket.
     *
     * Cambia el estado del ticket a "cerrado" y registra la fecha de cierre.
     * Solo usuarios autorizados pueden realizar esta acción.
     *
     * @group Gestión de Tickets
     * @authenticated
     * @urlParam ticket int required ID del ticket. Example: 1
     * 
     * @response 200 scenario="Ticket cerrado con éxito" {
     *   "data": {
     *     "id": 1,
     *     "title": "Mi impresora no funciona",
     *     "priority": "high",
     *     "status": "closed",
     *     "customer": {
     *       "id": 5,
     *       "name": "Juan Pérez"
     *     },
     *     "agent": null,
     *     "last_reply_at": null,
     *     "resolve_at": "2024-06-01T12:00:00Z",
     *     "close_at": "2024-06-02T14:30:00Z"
     *   },
     *   "message": "¡Ticket cerrado con exito!"
     * }
     * 
     * @response 403 scenario="No autorizado" {
     *   "message": "This action is unauthorized."
     * }
     * 
     * @response 404 scenario="Ticket no encontrado" {
     *   "message": "No query results for model [App\\Models\\Ticket]"
     * }
     * 
     * @responseField id int ID del ticket
     * @responseField title string Título del ticket
     * @responseField priority string Prioridad del ticket
     * @responseField status string Estado del ticket
     * @responseField close_at string Fecha de cierre
     */
    public function close(Ticket $ticket)
    {
        $this->authorize('close', $ticket);
        $ticket->update(
            [
                'status' => Status::CLOSED,
                'close_at' => now(),
            ]
        );
        return (new TicketResource($ticket->load('labels', 'files', 'user', 'agent')))
            ->additional(['message' => '¡Ticket cerrado con exito!'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
    public function assign(Ticket $ticket,AssignAgentRequest $request, AssignAgentService $service){
        $this->authorize('assign', $ticket);
        $ticket = $service->assignAgent($ticket, $request->validated(['agent_id']));
        return (new TicketResource($ticket))
        ->additional(['message' => '¡cambio de agent exitoso!'])
        ->response()
        ->setStatusCode(Response::HTTP_OK);
    }
}
