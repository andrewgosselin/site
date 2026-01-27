<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ToolsController extends Controller
{
    public function index()
    {
        return Inertia::render('tools/Index');
    }

    public function json()
    {
        return Inertia::render('tools/JsonTool');
    }

    public function encoder()
    {
        return Inertia::render('tools/EncoderTool');
    }

    public function generator()
    {
        return Inertia::render('tools/GeneratorTool');
    }

    public function seoChecker()
    {
        return Inertia::render('tools/MetaViewerTool');
    }
}
