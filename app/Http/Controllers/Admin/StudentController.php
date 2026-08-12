<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = User::role('student')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->trim()->toString();
                $query->where(fn ($studentQuery) => $studentQuery
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', compact('students'));
    }
}
