<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\User;
use App\Models\EducationalCenter;
use App\Models\Event;
use App\Models\Question;
use App\Models\BannedWord;

class AdminController extends Controller
{
    //
    public function index()
    {
        $data = [
            'totalUsers'        => User::count(),
            'countTeachers'     => User::where('role', 'teacher')->count(),
            'countStudents'     => User::where('role', 'student')->count(),
            'countEI'           => User::where('role', 'EI')->count(),
            'countOthers'       => User::whereNotIn('role', ['teacher', 'student', 'admin', 'EI'])->count(),
            'totalSchools'      => EducationalCenter::count(),
            'totalEvents'       => Event::count(),
            'totalQuestions'    => Question::count(),
            'latestUsers'       => User::latest()->take(6)->get(),
            'latestQuestions'   => Question::latest()->take(5)->get(),
            'pendingAiReviews'  => 0, //provisional
            'usersWithoutSchool'=> User::where('role', 'student')->whereNull('educational_center_id')->count(),
            'topUsers'          => User::orderBy('reputation', 'desc')->take(3)->get(),
            'bannedWords'       => BannedWord::all(),
            'countAdmins'       => User::where('role', 'admin')->count(),
            'totalRoles'        => Rol::count(),
        ];

        return view('admin.index', $data);
    }


    
    public function users()
    {
        
        $users = User::all();
        $countAdmins = User::where('role', 'admin')->count();

        $data = [
            'users' => $users,
            'roles_disponibles' => Rol::all()->mapWithKeys(function ($r) {
                return [$r->code ?? $r->name => $r->name];
            })->toArray(),
            'niveles_disponibles' => EducationalCenter::$niveles_disponibles
        ];

        return view('users.index', $data);
    }


}



