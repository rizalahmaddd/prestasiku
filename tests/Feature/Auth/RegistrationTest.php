<?php

namespace Tests\Feature\Auth;

test('public registration is closed', function () {
    $this->get('/register')->assertNotFound();
});
