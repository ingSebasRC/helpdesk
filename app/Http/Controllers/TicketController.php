<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Support\HelpdeskData;
use App\Support\TicketPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
            'priority' => (string) $request->query('priority', ''),
            'category' => (string) $request->query('category', ''),
            'technician' => (string) $request->query('technician', ''),
            'sort' => (string) $request->query('sort', 'updatedAt'),
            'direction' => (string) $request->query('direction', 'desc'),
        ];

        $tickets = Ticket::query();
        if (auth()->user()->role === 'usuario') {
            $tickets->where('user_id', auth()->id());
        }

        $items = HelpdeskData::filter(TicketPresenter::collection($tickets->get()), $filters);
        $perPage = 10;
        $page = max(1, (int) $request->query('page', 1));
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values()->all(),
            $items->count(),
            $perPage,
            $page,
            ['path' => route('tickets.index')]
        );
        $paginator->withQueryString();

        return view('tickets.index', array_merge($this->formData(), [
            'tickets' => $paginator,
            'filters' => $filters,
            'showCreate' => $request->query('new') === '1' || old('title') !== null,
        ]));
    }

    public function create()
    {
        return view('tickets.create', $this->formData());
    }

    public function store(Request $request)
    {
        $role = auth()->user()->role;
        $data = $request->validate($this->rules(false, $role === 'usuario'));

        $ticket = Ticket::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $role === 'usuario' ? 'Abierto' : $data['status'],
            'priority' => $data['priority'],
            'user_id' => $role === 'usuario' ? auth()->id() : $data['user_id'],
            'category_id' => $data['category_id'],
            'assigned_to' => $role === 'admin' && ! empty($data['technician_id'])
                ? $data['technician_id']
                : null,
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Solicitud creada correctamente.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        return view('tickets.show', array_merge($this->formData(), [
            'title' => 'Detalle de solicitud',
            'subtitle' => 'Seguimiento y actualización de la solicitud',
            'ticket' => TicketPresenter::toArray($ticket),
        ]));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        if (auth()->user()->role === 'tecnico') {
            abort(403);
        }

        return view('tickets.edit', array_merge($this->formData(), [
            'title' => 'Editar solicitud',
            'subtitle' => 'Actualiza la información de la solicitud',
            'ticket' => TicketPresenter::toArray($ticket),
        ]));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $role = auth()->user()->role;
        $data = $request->validate($this->rules(true));

        if ($role === 'usuario') {
            $data = array_intersect_key($data, array_flip(['title', 'description', 'priority', 'category_id']));
        } elseif ($role === 'tecnico') {
            $data = array_intersect_key($data, array_flip(['status']));
        }

        foreach (['title', 'description', 'status', 'priority', 'category_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $ticket->{$field} = $data[$field];
            }
        }

        if ($role === 'admin') {
            if (array_key_exists('user_id', $data)) {
                $ticket->user_id = $data['user_id'];
            }
            if (array_key_exists('technician_id', $data)) {
                $ticket->assigned_to = $data['technician_id'] ?: null;
            }
        }

        $ticket->save();

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Solicitud actualizada correctamente.');
    }

    public function destroy(Ticket $ticket)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('status', 'Solicitud eliminada correctamente.');
    }

    public function addNote(Request $request, Ticket $ticket)
    {
        if (! in_array(auth()->user()->role, ['admin', 'tecnico'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'notify' => ['nullable', 'boolean'],
        ]);

        $notes = $ticket->notes ?? [];
        $notes[] = [
            'message' => $data['message'],
            'author' => auth()->user()->name,
            'role' => auth()->user()->role,
            'notify' => $request->boolean('notify'),
            'createdAt' => now()->toIso8601String(),
        ];

        $ticket->notes = $notes;
        $ticket->save();

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Nota de seguimiento agregada correctamente.');
    }

    public function export(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $filters = $request->only(['q', 'status', 'priority', 'category', 'technician', 'sort', 'direction']);
        $tickets = HelpdeskData::filter(TicketPresenter::collection(Ticket::query()->get()), $filters);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="solicitudes-helpdesk.csv"',
            'Cache-Control' => 'no-store',
        ];

        return response()->stream(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Código', 'Título', 'Estado', 'Prioridad', 'Categoría', 'Solicitante', 'Técnico', 'Creado', 'Actualizado']);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket['code'],
                    $ticket['title'],
                    $ticket['status'],
                    $ticket['priority'],
                    HelpdeskData::categoryName($ticket['categoryId']),
                    HelpdeskData::userName($ticket['userId']),
                    HelpdeskData::technicianName($ticket['technicianId']),
                    Carbon::parse($ticket['createdAt'])->format('d/m/Y H:i'),
                    Carbon::parse($ticket['updatedAt'])->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function authorizeTicket(Ticket $ticket): void
    {
        if (auth()->user()->role === 'usuario' && (string) $ticket->user_id !== (string) auth()->id()) {
            abort(403);
        }
    }

    private function formData(): array
    {
        return [
            'active' => 'tickets',
            'title' => 'Solicitudes',
            'subtitle' => 'Gestiona y da seguimiento a los tickets',
            'role' => auth()->user()->role,
            'statuses' => HelpdeskData::STATUSES,
            'priorities' => HelpdeskData::PRIORITIES,
            'categories' => Category::orderBy('name')->get()
                ->map(fn (Category $category) => ['id' => (string) $category->id, 'name' => $category->name])
                ->all(),
            'users' => User::orderBy('name')->get()
                ->map(fn (User $user) => ['id' => (string) $user->id, 'name' => $user->name])
                ->all(),
            'technicians' => HelpdeskData::technicians(),
        ];
    }

    private function rules(bool $partial = false, bool $limited = false): array
    {
        $prefix = $partial ? 'sometimes|' : '';
        $userIds = User::pluck('id')->implode(',');
        $categoryIds = Category::pluck('id')->implode(',');

        $rules = [
            'title' => $prefix.'required|string|min:5|max:150',
            'description' => $prefix.'required|string|min:10',
            'user_id' => $prefix.'required|in:'.$userIds,
            'category_id' => $prefix.'required|in:'.$categoryIds,
            'technician_id' => $prefix.'nullable|in:'.$userIds,
            'priority' => $prefix.'required|in:'.implode(',', HelpdeskData::PRIORITIES),
            'status' => $prefix.'required|in:'.implode(',', HelpdeskData::STATUSES),
        ];

        if ($limited) {
            unset($rules['user_id'], $rules['technician_id'], $rules['status']);
        }

        return $rules;
    }
}
