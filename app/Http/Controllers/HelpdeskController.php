<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Support\HelpdeskData;
use App\Support\TicketPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpdeskController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('login', ['heroStats' => $this->heroStats()]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withInput()->withErrors([
            'email' => 'Las credenciales no son correctas.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function dashboard(Request $request)
    {
        $days = (int) $request->query('range', 14);
        $days = in_array($days, [7, 14, 30], true) ? $days : 14;

        $query = Ticket::query();
        if (auth()->user()->role === 'usuario') {
            $query->where('user_id', auth()->id());
        }

        $tickets = TicketPresenter::collection($query->get());
        $stats = HelpdeskData::stats($tickets);
        $trend = HelpdeskData::trend($tickets, $days);
        $recent = collect($tickets)->sortByDesc('updatedAt')->take(6)->values()->all();

        return view('dashboard', [
            'active' => 'dashboard',
            'role' => auth()->user()->role,
            'title' => 'Panel',
            'subtitle' => 'Resumen general del servicio de soporte',
            'days' => $days,
            'stats' => $stats,
            'statCards' => $this->statCards($stats),
            'trend' => $trend,
            'recent' => $recent,
        ]);
    }

    private function heroStats(): array
    {
        return [
            'tickets' => Ticket::count(),
            'technicians' => User::where('role', 'tecnico')->count(),
            'categories' => Category::count(),
        ];
    }

    private function statCards(array $stats): array
    {
        $deltaTone = $stats['monthDelta'] > 0 ? 'up' : ($stats['monthDelta'] < 0 ? 'down' : 'flat');
        $deltaIcon = $stats['monthDelta'] > 0 ? 'trending-up' : ($stats['monthDelta'] < 0 ? 'trending-down' : 'minus');
        $deltaText = ($stats['monthDelta'] > 0 ? '+' : '').$stats['monthDelta'].'% mes';

        return [
            [
                'tone' => 'accent',
                'icon' => 'inbox',
                'value' => (string) $stats['total'],
                'label' => 'Total de solicitudes',
                'foot' => $stats['createdThisMonth'].' creadas este mes',
                'badge' => ['tone' => $deltaTone, 'icon' => $deltaIcon, 'text' => $deltaText],
            ],
            [
                'tone' => 'warning',
                'icon' => 'clock',
                'value' => (string) $stats['active'],
                'label' => 'Solicitudes activas',
                'foot' => $stats['open'].' abiertas · '.$stats['inProgress'].' en progreso · '.$stats['waiting'].' en espera',
                'badge' => $stats['criticalActive'] > 0
                    ? ['tone' => 'down', 'icon' => 'alert-triangle', 'text' => $stats['criticalActive'].' críticas']
                    : ['tone' => 'up', 'icon' => 'check', 'text' => 'Al día'],
            ],
            [
                'tone' => 'success',
                'icon' => 'check-circle',
                'value' => (string) $stats['resolved'],
                'label' => 'Solicitudes resueltas',
                'foot' => 'Tasa de resolución del '.$stats['rate'].'%',
                'badge' => ['tone' => 'up', 'icon' => 'check-circle', 'text' => $stats['rate'].'%'],
            ],
            [
                'tone' => 'info',
                'icon' => 'timer',
                'value' => $stats['avgHours'].' h',
                'label' => 'Tiempo medio de resolución',
                'foot' => 'Desde la creación hasta la resolución',
                'badge' => $stats['unassigned'] > 0
                    ? ['tone' => 'flat', 'icon' => 'user-x', 'text' => $stats['unassigned'].' sin asignar']
                    : ['tone' => 'flat', 'icon' => 'check', 'text' => 'Todo asignado'],
            ],
        ];
    }
}
