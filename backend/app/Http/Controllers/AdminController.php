<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\User;
use App\Models\EducationalCenter;
use App\Models\Event;
use App\Models\Question;
use App\Models\BannedWord;
use App\Models\Cycle;

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
            'latestQuestions'   => Question::with(['user', 'answers.user'])->latest()->take(5)->get(),
            'pendingAiReviews'  => 0, //provisional
            'usersWithoutSchool'=> User::where('role', 'student')->whereNull('educational_center_id')->count(),
            'topUsers'          => User::orderBy('reputation', 'desc')->take(3)->get(),
            'bannedWords'       => BannedWord::all(),
            'countAdmins'       => User::where('role', 'admin')->count(),
            'totalRoles'        => Rol::count(),
        ];

        return view('admin.index', $data);
    }


    
    public function users(Request $request)
    {
        $query = User::with(['student.cycle', 'teacher', 'groupsAsStudent.cycle', 'groupsAsTeacher.subjectsWithTeachers']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('dni', 'like', "%$search%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('institution')) {
            $query->where('educational_center_id', $request->institution);
        }

        if ($request->filled('level')) {
             $query->where('education_level', $request->level);
        }

        if ($request->filled('cycle')) {
            $cycleId = $request->cycle;
            $query->where(function($q) use ($cycleId) {
                $q->whereHas('student', function($sq) use ($cycleId) {
                    $sq->where('cycle_id', $cycleId);
                })->orWhereHas('groupsAsTeacher', function($tq) use ($cycleId) {
                    $tq->where('cycle_id', $cycleId);
                });
            });
        }

        $users = $query->paginate(10);
        $countAdmins = User::where('role', 'admin')->count();

        $data = [
            'users' => $users,
            'roles_disponibles' => Rol::all()->mapWithKeys(function ($r) {
                return [$r->code ?? $r->name => $r->name];
            })->toArray(),
            'niveles_disponibles' => EducationalCenter::$niveles_disponibles,
            'centros' => EducationalCenter::orderBy('name')->pluck('name', 'id')->toArray(),
            'ciclos_disponibles' => Cycle::orderBy('name')->pluck('name', 'id')->toArray()
        ];

        return view('users.index', $data);
    }

    /**
     * Obtiene la lista de instituciones (nombres manuales + centros educativos).
     */
    protected function getInstitucionesExistentes()
    {
        return EducationalCenter::pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }


}
