import { createRouter, createWebHistory } from "vue-router";
const HomeView = () => import("../views/home/HomeView.vue");
const LoginView = () => import("../views/LoginView.vue");
const GetPasswordView = () => import("../views/GetPasswordView.vue");
const ProfileView = () => import("../views/ProfileView.vue");
const RegisterView = () => import("../views/RegisterView.vue");
const QuestionView = () => import("../views/home/QuestionView.vue");
const ExploreView = () => import("../views/home/ExploreView.vue");
const NotificationView = () => import("../views/home/NotificationView.vue");
const EventView = () => import("../views/home/EventView.vue");
const MeetingView = () => import("../views/home/MeetingView.vue");
const MeetingChatView = () => import("../views/home/MeetingChatView.vue");
const LaravelTestView = () => import("../views/LaravelTestView.vue");
const VideoCallView = () => import("../views/home/VideoCallView.vue");
const CenterManagementView = () => import("../views/home/CenterManagementView.vue");
const ForumView = () => import("../views/home/ForumView.vue");
const SettingsView = () => import("../views/SettingsView.vue");
const EventDetailsView = () => import("../views/home/EventDetailsView.vue");
const PublicationView = () => import("../views/home/PublicationView.vue");
const PublicationDetailsView = () => import("../views/home/PublicationDetailsView.vue");
const PrivateChatView = () => import("../views/home/PrivateChatView.vue");
const ErrorPageView = () => import("../views/ErrorPageView.vue");
import { user } from "@/stores/auth";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'register',
            component: RegisterView
        },
        {
            path: '/video-call',
            name: 'video-call',
            component: VideoCallView
        },
        {
            path: '/video-call/:roomId',
            name: 'video-call-room',
            component: VideoCallView
        },
        {
            path: '/home',
            name: 'home',
            component: HomeView
        },
        {
            path: '/login',
            name: 'login',
            component: LoginView
        },
        {
            path: '/recover-password',
            name: 'recover-password',
            component: GetPasswordView
        },
        {
            path: '/profile/:id?',
            name: 'profile',
            component: ProfileView
        },
        {
            path: '/question/:id?',
            name: 'question',
            component: QuestionView
        },
        {
            path: '/explore',
            name: 'explore',
            component: ExploreView
        },
        {
            path: '/notification',
            name: 'notification',
            component: NotificationView
        },
        {
            path: '/event',
            name: 'event',
            component: EventView
        },
        {
            path: '/event-details/:id?',
            name: 'event-details',
            component: EventDetailsView
        },
        {
            path: '/publicaciones',
            name: 'publications',
            component: PublicationView
        },
        {
            path: '/publicacion-detalles/:id?',
            name: 'publication-details',
            component: PublicationDetailsView
        },
        {
            path: '/meeting',
            name: 'meeting',
            component: MeetingView
        },
        {
            path: '/meetingchat/:id/:name?/:teacher?/:group?/:groupId?',
            name: 'meetingchat',
            component: MeetingChatView
        },
        {
            path: '/private-chat',
            name: 'private-chat',
            component: PrivateChatView
        },
        {
            path: '/laravel',
            name: 'laravel',
            component: LaravelTestView
        },
        {
            path: '/mi-centro',
            name: 'center-management',
            component: CenterManagementView
        },
        {
            path: '/foro',
            name: 'foro',
            component: ForumView
        },
        {
            path: '/settings',
            name: 'settings',
            component: SettingsView
        },
        {
            path: '/:pathMatch(.*)*',
            name: 'ErrorPageView',
            component: ErrorPageView
        }
    ]
})

// Navigation Guard
router.beforeEach((to, from, next) => {
    const publicPages = ['/login', '/', '/recover-password'];
    const authRequired = !publicPages.includes(to.path);
    const loggedIn = user.value;

    if (authRequired && !loggedIn) {
        return next('/');
    }

    if (loggedIn && publicPages.includes(to.path)) {
        return next('/home');
    }

    // Block unverified students and teachers from restricted routes
    const isStudentOrTeacher = ['Student', 'Teacher'].includes(loggedIn?.role);
    const isUnverified = isStudentOrTeacher && loggedIn?.is_verified === false;
    const restrictedForUnverified = ['/foro', '/event', '/meeting', '/private-chat', '/meetingchat'];

    if (isUnverified && restrictedForUnverified.some(r => to.path.startsWith(r))) {
        // Allow navigation but the view itself will show the banner (don't hard-redirect)
        // Just proceed — the views handle it with UnverifiedBanner
    }

    // Block admin from entering meeting chats
    const isAdmin = loggedIn?.role?.toLowerCase() === 'admin' || loggedIn?.role?.toLowerCase() === 'administrador';
    if (isAdmin && to.path.startsWith('/meetingchat')) {
        return next('/meeting');
    }

    // Block EU (External Users) from restricted routes
    if (loggedIn?.role === 'EU') {
        const isLegalTutor = loggedIn?.is_legal_tutor === true;
        
        // EU never has access to forum or meetings
        const noAccessRoutesForEU = ['/foro', '/question', '/meeting', '/meetingchat'];
        if (noAccessRoutesForEU.some(r => to.path.startsWith(r))) {
            return next('/home');
        }
        
        // EU has access to private-chat ONLY if they are a legal tutor
        if (to.path.startsWith('/private-chat') && !isLegalTutor) {
            return next('/home');
        }
    }

    next();
});

export default router
