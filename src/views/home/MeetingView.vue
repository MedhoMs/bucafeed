<script setup>
    import { ref } from 'vue';
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import Meeting from '../../components/Meeting.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations()

    const meetings = ref([
        {
            id: 1,
            name: "Dudas PHP",
            teacher: "Juanra",
            schedule: "10:00",
            description: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Nostrum, enim obcaecati debitis temporibus officia vitae quam laudantium unde quo illum nisi accusantium in, eos praesentium dicta fuga nesciunt itaque quia."
        },
        {
            id: 2,
            name: "Dudas HTML",
            teacher: "Jose",
            schedule: "10:30",
            description: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Nostrum, enim obcaecati debitis temporibus officia vitae quam laudantium unde quo illum nisi accusantium in, eos praesentium dicta fuga nesciunt itaque quia."
        },
        {
            id: 3,
            name: "Dudas CSS",
            teacher: "Jose",
            schedule: "17:00",
            description: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Nostrum, enim obcaecati debitis temporibus officia vitae quam laudantium unde quo illum nisi accusantium in, eos praesentium dicta fuga nesciunt itaque quia."
        },
        {
            id: 4,
            name: "Dudas Docker",
            teacher: "Gabriel",
            schedule: "19:00",
            description: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Nostrum, enim obcaecati debitis temporibus officia vitae quam laudantium unde quo illum nisi accusantium in, eos praesentium dicta fuga nesciunt itaque quia."
        }
    ]);

    const filteredMeetings = ref([...meetings.value]);
</script>

<template>
    <main class="flex min-h-screen justify-between">
        <NavBar></NavBar>
        <section class="text-white w-full ml-20 py-10 pr-14 flex flex-col min-h-screen">
            <p class="text-[40px] font-bold shrink-0">Charlas Disponibles</p>
            <SearchBar :meetings="meetings" @update:filtered="filteredMeetings = $event" class="shrink-0" />
            
            <div v-if="filteredMeetings.length > 0" class="grid grid-cols-4 gap-4 mt-10">
                <Meeting
                    v-for="meeting in filteredMeetings"
                    :key="meeting.id"
                    :id="meeting.id"
                    :name="meeting.name"
                    :teacher="meeting.teacher"
                    :schedule="meeting.schedule"
                    :description="meeting.description"
                />
            </div>
        
            <div v-else class="w-fit bg-[#2a4a5a] p-8 mx-auto my-auto rounded-[30px] shadow-xl border border-white/10 text-center">
                <h3 class="text-2xl font-bold text-white mb-2">No se han encontrado reuniones</h3>
            </div>
        </section>
    </main>
</template>
