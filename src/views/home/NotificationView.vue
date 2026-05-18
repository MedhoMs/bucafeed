<script setup>
    import { ref, onMounted } from 'vue';
    import { useRouter } from 'vue-router';
    import NavBar from '../../components/NavBar/NavBar.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import Pagination from '../../components/common/Pagination.vue';

    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations()
    const router = useRouter()

    import {
        notifications,
        unreadCount,
        loading,
        lastPage,
        currentPage,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        deleteNotification,
    } from '@/stores/notifications'

    const activeFilter = ref(null)
    const activeFilterType = ref(null)

    const filterTabs = [
        { label: 'Todo', type: null, icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>' },
        { label: 'Charlas', type: 'meeting,meeting_message', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 9h8"/><path d="M8 13h6"/><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/></svg>' },
        { label: 'Mensajes Privados', type: 'private_message', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-8a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v8z"/><path d="M3 7l9 6l9 -6"/></svg>' },
        { label: 'Respuestas', type: 'answer,answer_useful', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 9h8"/><path d="M8 13h6"/><path d="M15 18h-2l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v5.5"/><path d="M19 16v3"/><path d="M19 22v.01"/></svg>' },
    ]

    const setFilter = (tab) => {
        activeFilter.value = tab.label
        activeFilterType.value = tab.type
        fetchNotifications(1, tab.type)
    }

    const handlePageChange = (page) => {
        fetchNotifications(page, activeFilterType.value)
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }

    const handleClick = async (notification) => {
        if (!notification.read) {
            await markAsRead(notification)
        }
        if (notification.type === 'private_message' && notification.data?.sender_id) {
            router.push(`/private-chat?user=${notification.data.sender_id}`)
        } else if (notification.type === 'meeting_message' && notification.data?.meeting_id) {
            router.push(`/meetingchat/${notification.data.meeting_id}`)
        } else if (notification.type === 'group_message' && notification.data?.group_id) {
            router.push(`/group-chat/${notification.data.group_id}`)
        }
    }

    const handleDelete = async (e, notification) => {
        e.stopPropagation()
        await deleteNotification(notification)
    }

    const getNotificationIcon = (type) => {
        if (type === 'answer') {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M15 18h-2l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v5.5" /><path d="M19 16v3" /><path d="M19 22v.01" /></svg>'
        }
        if (type === 'answer_useful') {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1.002l3.086 -6.253l3.086 6.253l6.9 1.002l-5 4.867l1.179 6.873z" /></svg>'
        }
        if (type === 'meeting' || type === 'meeting_message') {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" /><path d="M18 14v4h4" /><path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M15 3v4" /><path d="M7 3v4" /><path d="M3 11h16" /></svg>'
        }
        if (type === 'private_message') {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" /></svg>'
        }
        if (type === 'group_message') {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>'
        }
        return ''
    }

    const formatDate = (dateStr) => {
        const d = new Date(dateStr)
        const now = new Date()
        const diff = now - d
        const mins = Math.floor(diff / 60000)
        if (mins < 1) return 'Ahora'
        if (mins < 60) return `Hace ${mins} min`
        const hours = Math.floor(mins / 60)
        if (hours < 24) return `Hace ${hours}h`
        return d.toLocaleDateString()
    }

    onMounted(() => {
        fetchNotifications()
    })
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen w-full">
            <PageHeader :title="t.notifications.title" :subtitle="t.notifications.subtitle">
                <template #search>
                    <!-- Mobile: scroll horizontal sin scrollbar visible -->
                    <!-- Desktop: centrado con wrap -->
                    <div
                        id="notificationFilter"
                        class="text-white flex items-center gap-3 overflow-x-auto lg:overflow-x-visible flex-nowrap lg:flex-wrap lg:justify-center scroll-smooth px-4 lg:px-0 pb-1 lg:pb-0 no-scrollbar"
                    >
                        <span
                            v-for="tab in filterTabs"
                            :key="tab.label"
                            :class="[
                                'inline-flex items-center gap-2 lg:gap-3 py-3 lg:py-4 px-5 lg:px-8 rounded-full cursor-pointer duration-500 font-black text-sm lg:text-base uppercase tracking-wider shrink-0',
                                activeFilter === tab.label || (!activeFilter && tab.label === 'Todo')
                                    ? 'bg-secondary-normal text-white shadow-2xl scale-105'
                                    : 'hover:bg-white/5 text-white/60 hover:text-white'
                            ]"
                            @click="setFilter(tab)"
                        >
                            <span v-html="tab.icon" class="[&>svg]:w-5 [&>svg]:h-5 lg:[&>svg]:w-6 lg:[&>svg]:h-6 shrink-0 opacity-80"></span>
                            {{
                                tab.label === 'Todo' ? t.notifications.filterAll :
                                tab.label === 'Charlas' ? t.notifications.filterMeetings :
                                tab.label === 'Mensajes Privados' ? t.notifications.filterPrivate :
                                tab.label === 'Respuestas' ? t.notifications.filterPending :
                                tab.label
                            }}
                        </span>
                    </div>
                </template>
            </PageHeader>

            <section class="text-white w-full px-6 lg:px-14 mb-20 flex-1 flex flex-col">
                <div id="mainBody" class="flex flex-col gap-4 w-full flex-1">
                    <div v-if="loading" class="text-white/40 italic py-20">
                        Cargando...
                    </div>

                    <div v-else-if="notifications.length === 0" class="text-center py-20">
                        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white/20"><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                        </div>
                        <p class="text-white/40 italic text-lg">{{ t.notifications.noNotifications }}</p>
                        <p class="text-white/20 text-sm mt-2">{{ t.notifications.emptyMessage }}</p>
                    </div>

                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="notification-card w-full"
                        :class="{ 'border-accent-normal/30 ring-1 ring-accent-normal/10': !notification.read }"
                        @click="handleClick(notification)"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center shrink-0"
                                    :class="{ 'bg-accent-normal/10 border-accent-normal/20': !notification.read }"
                                >
                                    <div v-html="getNotificationIcon(notification.type)" class="[&>svg]:w-5 [&>svg]:h-5"></div>
                                </div>
                                <div>
                                    <span class="text-sm font-bold">
                                        {{
                                            notification.type === 'answer' ? t.notifications.answered :
                                            notification.type === 'answer_useful' ? t.notifications.answerUseful :
                                            notification.type === 'meeting' ? t.notifications.meetingAlert :
                                            notification.type === 'private_message' ? t.notifications.privateMessage :
                                            notification.type === 'meeting_message' ? t.notifications.meetingMessage :
                                            notification.type === 'group_message' ? t.notifications.groupMessage :
                                            ''
                                        }}
                                    </span>
                                    <span v-if="!notification.read" class="w-2 h-2 bg-accent-normal rounded-full inline-block ml-2"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs text-white/30 font-medium">{{ formatDate(notification.created_at) }}</span>
                                <button
                                    @click="handleDelete($event, notification)"
                                    class="p-1.5 rounded-lg hover:bg-white/10 text-white/30 hover:text-red-400 transition-colors"
                                    title="Eliminar"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <div v-if="notification.type === 'answer' || notification.type === 'answer_useful'" class="mt-3 ml-14">
                            <h2 class="text-xl lg:text-2xl font-black uppercase">{{ notification.data?.question_title || '' }}</h2>
                            <p v-if="notification.data?.answer_snippet && notification.type === 'answer'" class="text-white/50 text-sm mt-1 truncate">
                                {{ notification.data.answer_snippet }}
                            </p>
                            <p v-if="notification.data?.user_name" class="text-white/30 text-xs mt-1">
                                {{ t.notifications.from }} <span class="text-white/50 font-medium">{{ notification.data.user_name }}</span>
                            </p>
                        </div>

                        <div v-if="notification.type === 'meeting'" class="mt-3 ml-14">
                            <h2 class="text-xl lg:text-2xl font-black uppercase">{{ notification.data?.meeting_name || '' }}</h2>
                            <p v-if="notification.data?.teacher_name" class="text-white/50 text-sm mt-1">
                                {{ t.notifications.from }} <span class="text-white/50 font-medium">{{ notification.data.teacher_name }}</span>
                            </p>
                            <div class="flex justify-end mt-2">
                                <span v-if="notification.data?.schedule" class="inline-flex items-center gap-1.5 text-xs font-bold bg-white/5 px-3 py-1 rounded-lg border border-white/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ t.notifications.schedule }}: {{ notification.data.schedule }}
                                </span>
                            </div>
                        </div>

                        <div v-if="notification.type === 'private_message' || notification.type === 'meeting_message' || notification.type === 'group_message'" class="mt-3 ml-14">
                            <h2 v-if="notification.type === 'private_message'" class="text-base font-black">
                                {{ notification.data?.sender_name || '' }}
                            </h2>
                            <h2 v-if="notification.type === 'meeting_message'" class="text-base font-black uppercase">
                                {{ notification.data?.meeting_name || '' }}
                            </h2>
                            <h2 v-if="notification.type === 'group_message'" class="text-base font-black uppercase">
                                {{ notification.data?.group_name || '' }}
                            </h2>
                            <p v-if="notification.data?.sender_name" class="text-white/30 text-xs mt-1">
                                {{ t.notifications.from }} <span class="text-white/50 font-medium">{{ notification.data.sender_name }}</span>
                            </p>
                            <p v-if="notification.data?.snippet" class="text-white/50 text-sm mt-1 truncate">
                                {{ notification.data.snippet }}
                            </p>
                        </div>
                    </div>

                    <div v-if="lastPage > 1" class="mt-12 mb-10 flex justify-center w-full">
                        <Pagination
                            :current-page="currentPage"
                            :last-page="lastPage"
                            @change="handlePageChange"
                        />
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
    /* Ocultar scrollbar manteniendo funcionalidad de scroll */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    .notification-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .notification-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: background 0.3s ease;
    }

    .notification-card:has(.border-accent-normal\/30)::before {
        background: linear-gradient(180deg, rgba(54, 54, 54, 0.6), rgba(54, 54, 54, 0.2));
    }

    .notification-card:hover {
        cursor: pointer;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.06) 100%);
        border-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }
</style>