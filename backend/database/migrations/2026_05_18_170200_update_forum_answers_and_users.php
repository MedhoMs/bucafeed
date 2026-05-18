<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Reassign all answers written by Teachers to Students.
        // Get all answers belonging to teachers
        $teacherAnswers = DB::table('answers')
            ->join('users', 'answers.user_id', '=', 'users.id')
            ->where('users.role', 'Teacher')
            ->select('answers.id as answer_id', 'answers.user_id', 'users.educational_center_id')
            ->get();

        foreach ($teacherAnswers as $answer) {
            // Find a student in the same educational center
            $student = DB::table('users')
                ->where('role', 'Student')
                ->where('educational_center_id', $answer->educational_center_id)
                ->first();

            if (!$student) {
                // Find any student in the database
                $student = DB::table('users')
                    ->where('role', 'Student')
                    ->first();
            }

            if ($student) {
                DB::table('answers')
                    ->where('id', $answer->answer_id)
                    ->update(['user_id' => $student->id]);
            }
        }

        // 2. Unmark all answers as 'is_useful' (Reconocida) and reset answer reputation to 0
        DB::table('answers')->update([
            'is_useful' => false,
            'reputation' => 0
        ]);

        // 3. Reset the reputation/points of all users who have answered questions to 0
        // Get distinct user_ids from answers table
        $userIdsWhoAnswered = DB::table('answers')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if (!empty($userIdsWhoAnswered)) {
            DB::table('users')
                ->whereIn('id', $userIdsWhoAnswered)
                ->update(['reputation' => 0]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No simple rollback since data was migrated and reset.
    }
};
