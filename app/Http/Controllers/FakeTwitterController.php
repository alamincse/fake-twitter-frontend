<?php

namespace App\Http\Controllers;

use Inertia\Response;
use Inertia\Inertia;

class FakeTwitterController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('FakeTwitter/Login');
    }

    public function register(): Response
    {
        return Inertia::render('FakeTwitter/Register');
    }

    public function dashboard(): Response
    {
        return Inertia::render('FakeTwitter/Dashboard');
    }

    public function profile(): Response
    {
        return Inertia::render('FakeTwitter/Profile');
    }

    public function following(): Response
    {
        return Inertia::render('FakeTwitter/Following');
    }

    public function follower(): Response
    {
        return Inertia::render('FakeTwitter/Follower');
    }

    public function tweet(): Response
    {
        return Inertia::render('FakeTwitter/Tweet');
    }
}
