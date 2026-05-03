import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/home/HomeView.vue";
import LoginView from "../views/LoginView.vue";
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
        }
    ]
})

// Navigation Guard
router.beforeEach((to, from, next) => {
    const publicPages = ['/login', '/'];
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
