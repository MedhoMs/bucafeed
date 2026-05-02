<script setup>
    import { useTranslations } from "../composables/useTranslations";
    import { useRoute } from 'vue-router';
    import { ref, computed, onMounted } from 'vue';
    import { user as authUser } from '../stores/auth';
    import UserAvatar from './common/UserAvatar.vue';
    import defaultLogo from '../assets/logo/logoTelamon.png';

    const { t } = useTranslations();
    const route = useRoute();
    const students = ref([]);

    const meetingId = computed(() => route.params.id);
    const meetingTeacher = computed(() => route.params.teacher);
    const groupName = computed(() => route.params.group);

    onMounted(async () => {
        if (groupName.value) {
            try {
                const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
                const response = await fetch(`${apiBase}/users/by-center?center_name=${encodeURIComponent(groupName.value)}`);
                if (response.ok) {
                    students.value = await response.json();
                }
            } catch (error) {
                console.error('Error fetching students:', error);
            }
        }
    });
</script>


<template>
    <aside class="flex justify-end w-40 lg:w-1/5 h-screen shrink-0">
        <div class="flex self-center h-[90%] ml-4 bg-white w-0.5 shrink-0"></div>

        <div class="flex flex-col lg:w-full h-full items-center text-white p-5 overflow-hidden">

            <div class="mr-auto w-full shrink-0">
                <p class="text-lg lg:text-2xl font-bold self-start mb-2">Profesor</p>
                <div class="flex items-center w-full rounded-3xl p-3 mb-5 hover:bg-[#2a4a5a] hover:cursor-pointer">
                    <UserAvatar size="w-10 h-10" class="hidden lg:block border-2 border-white shadow-xs mr-2" />
                    <p class="text-base lg:text-xl">{{ meetingId ? meetingTeacher : 'Profesor' }}</p>
                </div>
            </div>

            <div class="flex h-0.5 w-full bg-white shrink-0"></div>

            <p class="text-lg lg:text-2xl font-bold self-start mt-5 mb-2 shrink-0">Alumnos</p>

            <div class="flex flex-col mr-auto w-full flex-1 overflow-y-auto pb-4 pr-2">
                <!-- Estudiante Logueado (Yo) -->
                <div v-if="authUser && authUser.role === 'Student'" class="flex items-center w-full rounded-3xl p-3 mb-5 shrink-0 hover:bg-[#2a4a5a] cursor-pointer">
                    <UserAvatar :user="authUser" size="w-10 h-10" class="hidden lg:block border-2 border-white shadow-xs mr-2" />
                    <p class="text-base lg:text-xl">{{ authUser.name }} (Tú)</p>
                </div>

                <!-- Otros Estudiantes del Centro -->
                <div v-for="student in students.filter(s => s.id !== authUser?.id)" :key="student.id"
                    class="flex items-center w-full rounded-3xl p-3 mb-5 shrink-0 hover:bg-[#2a4a5a] cursor-pointer">
                    <UserAvatar :user="student" size="w-10 h-10" class="hidden lg:block border-2 border-white shadow-xs mr-2" />
                    <p class="text-base lg:text-xl">{{ student.name }} {{ student.last_name }}</p>
                </div>

                <p v-if="students.length === 0 && !authUser" class="text-sm text-white/40 italic">No hay otros alumnos</p>
            </div>

        </div>
    </aside>
</template>

<style scoped>

</style>
