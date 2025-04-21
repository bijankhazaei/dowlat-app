<?php

namespace App\Http\Middleware;

use App\Core\Helpers\CurrentUser;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiResult
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->expectsJson()) {
            return $next($request);
        }
        try {
            $res = $next($request);
            $res->headers->set('X-Powered-By', 'Zeenome');
            $nonApiResultAllowed = $res->headers->get('X-Internal-ApiResult') === 'skip';

            foreach ($res->headers->all() as $key => $value) {
                if (str_starts_with($key, 'X-Internal')) {
                    $res->headers->remove($key);
                }
            }
            if ($nonApiResultAllowed) {
                return $res;
            }
            if (!$this->isApiResult($res->getContent())) {
                if (($res->getStatusCode() >= 200 && $res->getStatusCode() < 300)
                    // || (($res->getStatusCode() < 200 || $res->getStatusCode()) >= 300 && CurrentUser::isInRole('ako'))
                ) {
                    $json_data = $this->getJsonOrFalse($res->getContent());
                    if ($json_data == false) {
                        return response()->apiResult(statusCode: $res->getStatusCode());
                    } else {
                        return response()->apiResult(metadata: $json_data, statusCode: $res->getStatusCode());
                    }
                }
                return response()->apiResult(statusCode: $res->getStatusCode());
            }
            return $res;
        } catch (\Throwable $e) {
            return response()->apiResult(statusCode: 500);
        }
    }

    private function getJsonOrFalse($val)
    {
        try {
            return json_decode($val);
        } catch (\Throwable $e) {
            return false;
        }
    }
    private function isApiResult($val)
    {
        try {
            $res = json_decode($val, true);
            if (
                !in_array('ok', array_keys($res))
            ) {
                throw new \Exception();
            }
            foreach (array_keys($res) as $key) {
                if ($key != "ok" && $key != "messages" && $key != "data" && $key != "metadata") {
                    throw new \Exception();
                }
            }
            // if (in_array('messages', array_keys($res))) {
            //     if (!is_array($res['messages'])) {
            //         throw new \Exception();
            //     }
            //     foreach ($res['messages'] as $error) {
            //         if (!is_string($error)) {
            //             throw new \Exception();
            //         }
            //     }
            // }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
