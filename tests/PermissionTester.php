<?php

namespace Tests;

use App\User;
use Tests\TestCase;

class PermissionTester {
    
    private TestCase    $case;
    
    public function __construct(TestCase $case) {
        $this->case = $case;
    }
    
    public function testExceptions(Permission $permission) {
            $response = $this->case->userRequest()
                ->json($permission->getMethod(), $permission->getUrl(), $permission->getData());

            $this->case->assertStatus($response, 400);
            $response->assertSimilarJson([
                'error' => $permission->getErrorMessage()
            ]);
    }
    
    public function testMissingPermission(Permission $permission) {
        User::first()->roles()->detach();

        $response = $this->case->userRequest()
            ->json($permission->getMethod(), $permission->getUrl(), $permission->getData());

        $this->case->assertStatus($response, 403);
        $response->assertSimilarJson([
            'error' => $permission->getErrorMessage()
        ]);
    }    
}