<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\PageView;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $since = Carbon::now()->subDays(14);

        $views = PageView::query()
            ->select(DB::raw("date(created_at) as day"), DB::raw('count(*) as total'))
            ->where('created_at', '>=', $since)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $topPages = PageView::query()
            ->select('path', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $since)
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'stats' => [
                'views_today' => PageView::query()->whereDate('created_at', today())->count(),
                'views_week' => PageView::query()->where('created_at', '>=', now()->subDays(7))->count(),
                'messages' => ContactMessage::query()->whereNull('read_at')->count(),
                'posts' => Post::query()->count(),
                'pages' => Page::query()->count(),
            ],
            'views' => $views,
            'topPages' => $topPages,
            'latestMessages' => ContactMessage::query()->latest()->limit(5)->get(),
        ]);
    }
}
