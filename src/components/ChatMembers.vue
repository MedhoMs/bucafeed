<script setup>
    import { useTranslations } from "../composables/useTranslations";
    import { useRoute } from 'vue-router';
    import { ref, computed, onMounted, watch } from 'vue';
    import { user as authUser } from '../stores/auth';
    import { useApi } from "../composables/useApi";
    import UserAvatar from './common/UserAvatar.vue';
    import defaultLogo from '../assets/logo/logoTelamon.png';

    const { get } = useApi();

    const { t } = useTranslations();
    const route = useRoute();
    const students = ref([]);

    const props = defineProps({
        meeting: {
            type: Object,
            default: null
        }
    });

    const meetingId = computed(() => route.params.id);
    const meetingTeacherParam = computed(() => route.params.teacher);
    const groupName = computed(() => route.params.group);

    const isMenuOpen = ref(false)
    const isDesktop = ref(window.innerWidth >= 1024)

    const fetchStudents = async () => {
        const centerId = props.meeting?.educational_center_id;
        const centerName = props.meeting?.educational_center?.name || groupName.value;
        const groupId = props.meeting?.group_id;
        
        console.log('Fetching students for center/group:', { centerId, centerName, groupId });
        
        if (centerId || centerName || groupId) {
            try {
                let url = `users/by-center?`;
                if (groupId) {
                    url += `group_id=${groupId}`;
                } else if (centerId) {
                    url += `center_id=${centerId}`;
                } else {
                    url += `center_name=${encodeURIComponent(centerName)}`;
                }

                const response = await get(url);
                console.log('Students received:', response);
                students.value = response || [];
            } catch (error) {
                console.error('Error fetching students:', error);
            }
        }
    }

    onMounted(async () => {
        const handleResize = () => {
            isDesktop.value = window.innerWidth >= 1024
            if (isDesktop.value) isMenuOpen.value = false
        }
        window.addEventListener('resize', handleResize)

        await fetchStudents();
    });

    // Re-fetch si cambia el meeting (pasado por prop) o el nombre del grupo
    watch(() => props.meeting, fetchStudents);
    watch(groupName, fetchStudents);

    function toggleMenu() {
        isMenuOpen.value = !isMenuOpen.value
    }

    function closeMenu() {
        isMenuOpen.value = false
    }
</script>


<template>
    <svg v-show="!isMenuOpen" @click="toggleMenu()" class="lg:hidden absolute top-5 right-6 z-50 text-white cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>

    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="isMenuOpen" @click="closeMenu()" class="lg:hidden fixed inset-0 z-40 bg-black/60"></div>
    </Transition>

    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-300 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full">
        <aside v-if="isMenuOpen || true" v-show="isMenuOpen || isDesktop" 
               class="fixed lg:static top-0 right-0 flex flex-col lg:flex-row w-75 lg:w-1/5 h-screen z-50 bg-[linear-gradient(180deg,#1f5252_0%,#0f2828_100%)] lg:bg-none lg:bg-transparent border-l border-white/10 lg:border-none shadow-[-5px_0px_20px_rgba(0,0,0,0.6)] lg:shadow-none shrink-0 transition-all duration-300">
            
            <div class="lg:hidden flex items-center p-5 border-b border-white/10 shrink-0">
                <p class="text-xl font-bold text-white">Miembros</p>
                <svg @click="closeMenu()" class="ml-auto text-white/70 hover:text-white cursor-pointer transition-colors" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M18 6l-12 12" /><path d="M6 6l12 12" />
                </svg>
            </div>

            <div class="hidden lg:flex self-center h-[90%] ml-4 bg-white w-0.5 shrink-0"></div>

            <div class="flex flex-col lg:w-full h-full items-center text-white p-5 overflow-hidden">

                <div class="mr-auto w-full shrink-0">
                    <p class="text-lg lg:text-2xl font-bold self-start mb-2">Profesor</p>
                    <div class="flex items-center w-full rounded-3xl p-3 mb-5 hover:bg-[#2a4a5a] hover:cursor-pointer transition-colors">
                        <UserAvatar :user="props.meeting?.teacher" size="w-10 h-10" class="border-2 border-white shadow-xs mr-2 shrink-0" />
                        <p class="text-base lg:text-xl truncate">
                            {{ props.meeting?.teacher ? `${props.meeting.teacher.name} ${props.meeting.teacher.last_name || ''}` : (meetingId ? meetingTeacherParam : 'Profesor') }}
                        </p>
                    </div>
                </div>

                <div class="flex h-0.5 w-full bg-white shrink-0"></div>

                <p class="text-lg lg:text-2xl font-bold self-start mt-5 mb-2 shrink-0">Alumnos</p>

                <div class="flex flex-col mr-auto w-full flex-1 overflow-y-auto pb-4 pr-2 custom-scrollbar">
                    <!-- Estudiante Logueado (Yo) -->
                    <div v-if="authUser && (['student', 'alumno', 'estudiante'].includes(authUser.role?.toLowerCase()))" class="flex items-center w-full rounded-3xl p-3 mb-5 shrink-0 hover:bg-[#2a4a5a] cursor-pointer transition-colors">
                        <UserAvatar :user="authUser" size="w-10 h-10" class="border-2 border-white shadow-xs mr-2 shrink-0" />
                        <p class="text-base lg:text-xl truncate">{{ authUser.name }} (Tú)</p>
                    </div>

                    <!-- Otros Estudiantes -->
                    <div v-for="student in students.filter(s => s.id !== authUser?.id)" :key="student.id"
                        class="flex items-center w-full rounded-3xl p-3 mb-5 shrink-0 hover:bg-[#2a4a5a] cursor-pointer transition-colors">
                        <UserAvatar :user="student" size="w-10 h-10" class="border-2 border-white shadow-xs mr-2 shrink-0" />
                        <p class="text-base lg:text-xl truncate">{{ student.name }} {{ student.last_name }}</p>
                    </div>

                    <p v-if="students.length === 0 && (!authUser || authUser.role !== 'Student')" class="text-sm text-white/40 italic">No hay otros alumnos</p>
                </div>

            </div>
        </aside>
    </Transition>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    display: block;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
