<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\Project;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch top 4 designers with most followers
        $topDesigners = Designer::orderBy('followers_count', 'desc')
            ->take(4)
            ->get();

        // Fetch 8 featured and latest projects
        $featuredProjects = Project::with(['designer', 'category'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('home', compact('topDesigners', 'featuredProjects'));
    }
}
