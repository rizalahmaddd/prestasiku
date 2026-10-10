<?php

test('public pages send the baseline security headers', function () {
    $this->get('/login')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'")
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeaderMissing('Strict-Transport-Security');
});

test('hsts is only sent over https', function () {
    $this->get('https://localhost/login')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
});

test('api responses send the security headers too', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});
