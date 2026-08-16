<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\RoleMiddleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use stdClass;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    private RoleMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new RoleMiddleware;
    }

    /**
     * A minimal double satisfying the only contract the middleware relies on
     * (`hasAnyRole(array): bool`) — no Eloquent/DB involvement needed.
     */
    private function fakeUser(array $roles): object
    {
        return new class($roles)
        {
            public function __construct(private readonly array $roles) {}

            public function hasAnyRole(array $roleSlugs): bool
            {
                return (bool) array_intersect($this->roles, $roleSlugs);
            }
        };
    }

    /**
     * @return array{0: Closure, 1: stdClass} the $next closure and a state object
     *                                         whose `called` flag flips to true once $next runs
     */
    private function nextSpy(Response $response): array
    {
        $state = new stdClass;
        $state->called = false;

        $next = function (Request $request) use ($state, $response) {
            $state->called = true;

            return $response;
        };

        return [$next, $state];
    }

    public function test_handle_returns_401_and_never_calls_next_when_unauthenticated(): void
    {
        $request = Request::create('/admin');
        $request->setUserResolver(fn () => null);
        [$next, $state] = $this->nextSpy(new Response('should not be reached'));

        $response = $this->middleware->handle($request, $next, 'admin');

        $this->assertFalse($state->called);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(['message' => 'Unauthenticated.'], json_decode($response->getContent(), true));
    }

    public function test_handle_returns_403_and_never_calls_next_when_user_lacks_any_required_role(): void
    {
        $request = Request::create('/admin');
        $request->setUserResolver(fn () => $this->fakeUser(['user']));
        [$next, $state] = $this->nextSpy(new Response('should not be reached'));

        $response = $this->middleware->handle($request, $next, 'admin');

        $this->assertFalse($state->called);
        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(['message' => 'This action is unauthorized.'], json_decode($response->getContent(), true));
    }

    public function test_handle_calls_next_and_returns_its_value_unchanged_when_user_has_a_required_role(): void
    {
        $request = Request::create('/admin');
        $request->setUserResolver(fn () => $this->fakeUser(['manager']));
        $marker = new Response('ok');
        [$next, $state] = $this->nextSpy($marker);

        $response = $this->middleware->handle($request, $next, 'admin|manager');

        $this->assertTrue($state->called);
        $this->assertSame($marker, $response);
    }

    public function test_pipe_separated_roles_are_matched_as_or_not_and(): void
    {
        // Has only one of the two listed roles — must still pass, since any one match is enough.
        $requestWithSecondRole = Request::create('/admin');
        $requestWithSecondRole->setUserResolver(fn () => $this->fakeUser(['manager']));
        [$next, $state] = $this->nextSpy(new Response('ok'));

        $response = $this->middleware->handle($requestWithSecondRole, $next, 'admin|manager');

        $this->assertTrue($state->called);
        $this->assertSame(200, $response->getStatusCode());

        // Has neither of the two listed roles — must still be rejected (OR, not "accept anything").
        $requestWithNoMatchingRole = Request::create('/admin');
        $requestWithNoMatchingRole->setUserResolver(fn () => $this->fakeUser(['guest']));
        [$next2, $state2] = $this->nextSpy(new Response('ok'));

        $response2 = $this->middleware->handle($requestWithNoMatchingRole, $next2, 'admin|manager');

        $this->assertFalse($state2->called);
        $this->assertSame(403, $response2->getStatusCode());
    }
}
