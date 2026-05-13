<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SchoolOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        echo "🏢 Organización Escolar (delegada a módulos)...\n";

        $this->call([
            TagsSeeder::class,
            CyclesSeeder::class,
            CentersSeeder::class,
            UsersAndGroupsSeeder::class,
            MeetingsSeeder::class,
            EventsSeeder::class,
            ForumQuestionsSeeder::class,
        ]);
    }
}
