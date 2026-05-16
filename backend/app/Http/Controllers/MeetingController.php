<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Notification;
use App\Models\EducationalCenter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeetingController extends TemplateController
{
    protected $model = Meeting::class;
    protected $viewPath = 'meetings';
    protected $with = ['teacher', 'educationalCenter', 'group'];

    protected function getFormFields($model = null)
    {
        return [
            ['name' => 'name', 'label' => 'Nombre de la Charla', 'placeholder' => 'Ej: Dudas PHP', 'value' => $model->name ?? '', 'required' => true],
            ['name' => 'schedule', 'type' => 'time', 'label' => 'Horario', 'value' => $model->schedule ? substr($model->schedule, 0, 5) : '', 'required' => true],
            ['name' => 'description', 'type' => 'textarea', 'label' => 'Descripción', 'value' => $model->description ?? '', 'full' => true],
        ];
    }

    protected function rules($model = null)
    {
        return [
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
            'educational_center_id' => 'required|exists:educational_centers,id',
            'schedule' => 'required',
        ];
    }

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('center_id')) {
            $query->where('educational_center_id', $request->center_id);
        }
        
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('student_id')) {
            $student = \App\Models\User::find($request->student_id);
            if ($student) {
                $groupIds = $student->groupsAsStudent()->pluck('groups.id')->toArray();
                $centerId = $student->educational_center_id;
                
                $query->where(function($q) use ($groupIds, $centerId) {
                    $q->whereIn('group_id', $groupIds);
                    if ($centerId) {
                        $q->orWhere(function($sub) use ($centerId) {
                            $sub->whereNull('group_id')
                                ->where('educational_center_id', $centerId);
                        });
                    }
                });
            }
        }
        
        // Si el usuario pide por nombre de institución (desde el store de auth)
        if ($request->filled('institution_name')) {
            $query->whereHas('educationalCenter', function($q) use ($request) {
                $q->where('name', $request->institution_name);
            });
        }

        return $query;
    }
    
    /**
     * Endpoint para crear charlas desde la API
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $meeting = Meeting::create($request->all());
        
        $this->createMeetingNotifications($meeting);

        return response()->json($meeting->load($this->with), 201);
    }

    private function createMeetingNotifications(Meeting $meeting): void
    {
        $students = [];
        if ($meeting->group_id) {
            $meeting->load('group.students');
            $group = $meeting->group;
            if ($group && $group->students) {
                $students = $group->students;
            }
        } else {
            // General meeting: notify all students of the center
            $students = User::where('educational_center_id', $meeting->educational_center_id)
                            ->whereIn('role', ['Student', 'Admin']) // Include Admins for the admin center context
                            ->get();
        }

        $teacherName = $meeting->teacher->name . ' ' . ($meeting->teacher->last_name ?? '');

        $data = [
            'meeting_id' => $meeting->id,
            'meeting_name' => $meeting->name,
            'teacher_name' => $teacherName,
            'schedule' => $meeting->schedule,
        ];

        foreach ($students as $student) {
            $notification = Notification::create([
                'user_id' => $student->id,
                'type' => 'meeting',
                'data' => $data,
            ]);

            $this->broadcastNotification($student->id, $notification->toArray());
        }
    }

    public function apiShow($id)
    {
        $meeting = Meeting::with($this->with)->findOrFail($id);
        return response()->json($meeting);
    }
}
