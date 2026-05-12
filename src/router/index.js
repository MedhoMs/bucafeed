import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/home/HomeView.vue";
import LoginView from "../views/LoginView.vue";
import GetPasswordView from "../views/GetPasswordView.vue";
import ProfileView from "../views/ProfileView.vue";
import RegisterView from "../views/RegisterView.vue";
import QuestionView from "../views/home/QuestionView.vue";
import ExploreView from "../views/home/ExploreView.vue";
import NotificationView from "../views/home/NotificationView.vue";
import EventView from "../views/home/EventView.vue";
import MeetingView from "../views/home/MeetingView.vue";
import MeetingChatView from "../views/home/MeetingChatView.vue";
import LaravelTestView from "../views/LaravelTestView.vue";
import VideoCallView from "../views/home/VideoCallView.vue";
import CenterManagementView from "../views/home/CenterManagementView.vue";
import ForumView from "../views/home/ForumView.vue";
import SettingsView from "../views/SettingsView.vue";
import EventDetailsView from "../views/home/EventDetailsView.vue";
import PrivateChatView from "../views/home/PrivateChatView.vue";
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
            path: '/meeting',
            name: 'meeting',
            component: MeetingView
        },
        {
            path: '/meetingchat/:id/:name?/:teacher?/:group?',
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

    next();
});

export default router
