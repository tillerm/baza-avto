<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsNotManager
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isManager()) {
            return redirect()->route('crm')->with('flash', [
                'banner' => 'У менеджеров нет доступа к этому разделу.',
                'bannerStyle' => 'danger',
            ]);
        }

        return $next($request);
    }
}
