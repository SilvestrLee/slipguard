<?php

it('returns a minimal health payload', function () {
    $this->getJson('/health')
        ->assertOk()
        ->assertExactJson([
            'name' => 'SlipGuard',
            'status' => 'ok',
        ]);
});
