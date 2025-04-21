<?php

namespace App\Providers;

use App\Helpers\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LengthAwarePaginator::class, Paginator::class);
        $this->app->bind(LengthAwarePaginatorContract::class, Paginator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        Request::macro('appendMessage', function ($message) {
            $request = Request::instance();
            $pipeMessages = $request->attributes->get('_pipe_messages', []);
            // TODO: Parse message by its type, translate based on accepted language
            $pipeMessages[] = $message;
            $request->attributes->set('_pipe_messages', $pipeMessages);
        });
        Response::macro('appendMessage', function ($message) {
            Request::appendMessage($message);
        });
        Response::macro('allowNonApiResult', function () {
            $request = Request::instance();
            $request->headers->set('X-Internal-ApiResult', 'skip');
            return Response::getFacadeRoot();
        });
        Response::macro('apiResult', function (mixed $data = null, int $statusCode = 200, array $messages = [], mixed $metadata = null) {
            $res = [];
            $res['ok'] = $statusCode >= 200 && $statusCode < 300;
            $res['data'] = $data;

            $res['messages'] = [];
            $pipeMessages = Request::instance()->attributes->get('_pipe_messages');
            if ($pipeMessages && count($pipeMessages)) {
                $res['messages'] = $pipeMessages;
            }
            if (count($messages) > 0) {
                if (isset($res['messages'])) {
                    $res['messages'] = array_merge($res['messages'], $messages);
                } else {
                    $res['messages'] = $messages;
                }
            }

            if (!is_null($metadata) && config('app.debug')) {
                $res['metadata'] = $metadata;
            }

            return Response::json($res, $statusCode);
        });
    }
}
