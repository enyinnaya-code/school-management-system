<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            if (
                Auth::check() &&
                !$request->ajax() &&
                !$request->wantsJson() &&
                in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            ) {
                $user = Auth::user();
                $method = $request->method();
                $path = $request->path();

                $action = match($method) {
                    'POST'         => 'Created',
                    'PUT', 'PATCH' => 'Updated',
                    'DELETE'       => 'Deleted',
                    default        => 'Accessed',
                };

                $input = $request->except([
                    'password', 
                    'password_confirmation', 
                    '_token', 
                    '_method'
                ]);

                // Sanitize long values
                foreach ($input as $key => $value) {
                    if (is_string($value) && strlen($value) > 200) {
                        $input[$key] = substr($value, 0, 200) . '...';
                    }
                }

                DB::table('activity_log')->insert([
                    'log_name'    => 'default',
                    'description' => "{$action}: /{$path}",
                    'causer_type' => 'App\\Models\\User',
                    'causer_id'   => $user->id,
                    'properties'  => json_encode([
                        'method'     => $method,
                        'url'        => $request->fullUrl(),
                        'ip'         => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'input'      => $input,
                    ]),
                    'event'      => strtolower($action),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Never block the user because of a logging error
        }

        return $response;
    }
}