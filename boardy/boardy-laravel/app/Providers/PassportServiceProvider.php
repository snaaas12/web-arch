<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Contracts\AuthorizationViewResponse;

class PassportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AuthorizationViewResponse::class,
            function () {
                return new class implements AuthorizationViewResponse {

                    protected array $parameters = [];

                    public function withParameters(array $parameters = []): static
                    {
                        $this->parameters = $parameters;

                        return $this;
                    }

                    public function toResponse($request)
                    {
                        return response()->view(
                            'passport.authorize',
                            $this->parameters
                        );
                    }
                };
            }
        );
    }

    public function boot(): void
    {
        //
    }
}
