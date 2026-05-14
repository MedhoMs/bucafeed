<script setup>
    import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
    import { useRoute } from 'vue-router';
    import NavBar from '@/components/NavBar/NavBar.vue';
    import { useTranslations } from '@/composables/useTranslations';
    import UserAvatar from '@/components/common/UserAvatar.vue';
    import { useApi } from '@/composables/useApi';
    import { user as authUser } from '@/stores/auth';
    import { useSocket } from '@/composables/useSocket';
    import TextChatBar from '@/components/TextChatBar.vue';

    const { t } = useTranslations();
    const { get, post } = useApi();
    const { connect: connectSocket, joinRoom, leaveRoom, emit: emitSocket, on: onSocket, disconnect: disconnectSocket } = useSocket();
    const route = useRoute();
    
    const contacts = ref([]);
    const loadingContacts = ref(false);
    const messages = ref([]);
    const selectedContactId = ref(null);
    const currentChat = ref(null);
    const mobileShowChat = ref(false);
    const loadingMessages = ref(false);
    const chatContainer = ref(null);
    const currentRoomId = ref(null);

    const selectedContact = computed(() => {
        if (!selectedContactId.value) return null;
        return contacts.value.find(c => Number(c.id) === Number(selectedContactId.value));
    });

    function scrollToBottom() {
        nextTick(() => {
            if (chatContainer.value) {
                chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
            }
        });
    }

    const fetchContacts = async () => {
        if (!authUser.value?.educational_center_id) return;
        loadingContacts.value = true;
        try {
            const role = authUser.value.role?.toLowerCase();
            let endpoint = 'my-center/students';
            
            if (role === 'student' || role === 'alumno') {
                endpoint = 'my-center/teachers';
            } else if (role === 'admin' || role === 'administrador') {
                endpoint = 'my-center/admins';
            } else if (role === 'teacher' || role === 'profesor' || role === 'ei') {
                endpoint = 'my-center/students';
            }
                
            const data = await get(endpoint);
            contacts.value = Array.isArray(data) ? data : [];
        } catch (error) {
            
        } finally {
            loadingContacts.value = false;
        }
    };

    function goBack() {
        mobileShowChat.value = false;
    }

    const navBarRef = ref(null);

    function openNavMenu() {
        if (navBarRef.value) {
            navBarRef.value.activeMenu();
        }
    }

    const selectContact = async (id) => {
        selectedContactId.value = id;
        messages.value = [];
        loadingMessages.value = true;
        mobileShowChat.value = true;
        currentChat.value = null;

        if (currentRoomId.value) {
            leaveRoom(currentRoomId.value);
            currentRoomId.value = null;
        }
        
        try {
            const chatData = await post('chats/find-or-create', { user_id: id });
            if (chatData && chatData.id) {
                currentChat.value = chatData;
                
                const history = await get(`chats/${chatData.id}/messages`);
                messages.value = Array.isArray(history) ? history : [];
                scrollToBottom();

                const newRoom = `chat-${chatData.id}`;
                currentRoomId.value = newRoom;
                const userData = authUser.value ? { 
                    userId: authUser.value.id, 
                    username: authUser.value.name, 
                    role: authUser.value.role 
                } : null;
                joinRoom(newRoom, userData);
            }
        } catch (error) {

        } finally {
            loadingMessages.value = false;
        }
    };

    async function handleSendMessage(msgObj) {
        if (!currentChat.value?.id || !authUser.value) return;
        
        try {
            let content = msgObj.content;
            let fileName = msgObj.fileName || null;

            if (msgObj.type === 'image' || msgObj.type === 'pdf') {
                const response = await fetch(msgObj.content);
                const blob = await response.blob();
                const formData = new FormData();
                formData.append('file', blob, fileName || 'file');
                const uploadResult = await post('upload', formData);
                content = uploadResult.url;
                fileName = uploadResult.filename || fileName;
            }

            const savedMsg = await post(`chats/${currentChat.value.id}/messages`, {
                type: msgObj.type,
                content: content,
                file_name: fileName
            });
            
            if (savedMsg && savedMsg.id) {
                messages.value.push(savedMsg);
                scrollToBottom();
                emitSocket('chat:message', currentRoomId.value, savedMsg);
            }
        } catch (error) {

        }
    }

    onMounted(async () => {
        await fetchContacts();

        const userId = route.query.user;
        if (userId && contacts.value.length > 0) {
            const contact = contacts.value.find(c => Number(c.id) === Number(userId));
            if (contact) {
                await selectContact(contact.id);
            }
        }

        connectSocket();

        onSocket('chat:message', (data) => {
            if (!data) return;
            if (Number(data.sender) !== Number(authUser.value?.id) && !messages.value.find(m => m.id === data.id)) {
                messages.value.push(data);
                scrollToBottom();
            }
        });
    });

    onUnmounted(() => {
        disconnectSocket();
    });
</script>



<template>
    <div class="h-screen overflow-hidden">
        <NavBar ref="navBarRef" hide-hamburger></NavBar>
        
        <main class="lg:pl-75 flex w-full h-full overflow-hidden">     
            
            <div class="flex-1 flex-col overflow-hidden relative" :class="mobileShowChat ? 'flex' : 'hidden lg:flex'">
                <template v-if="selectedContact">
                    <div class="px-6 py-4 border-b border-white/5 flex justify-between items-center gap-3 bg-white/5 backdrop-blur-md z-20 shrink-0">
                        <button @click="openNavMenu" class="lg:hidden text-white/70 hover:text-white transition-colors cursor-pointer shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg>
                        </button>
                        <div class="flex items-center gap-4">
                            <UserAvatar :user="selectedContact" size="w-10 h-10" />
                            <div>
                                <h3 class="text-lg font-bold text-white leading-none mb-1">{{ selectedContact.name }} {{ selectedContact.last_name }}</h3>
                            </div>
                        </div>
                        <button @click="goBack" class="lg:hidden text-white/70 hover:text-white transition-colors cursor-pointer shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" height="32px" width="32px" viewBox="0 -960 960 960" fill="#e3e3e3"><path d="M680-240v-80h200v80H680Zm-80-200v-80h280v80H600Zm-80-200v-80h360v80H520ZM235-515q-35-35-35-85t35-85q35-35 85-35t85 35q35 35 35 85t-35 85q-35 35-85 35t-85-35ZM80-240v-76q0-21 10-40t28-30q45-27 95.5-40.5T320-440q56 0 106.5 13.5T522-386q18 11 28 30t10 40v76H80Zm160-110q-39 10-74 30h308q-35-20-74-30t-80-10q-41 0-80 10Zm108.5-221.5Q360-583 360-600t-11.5-28.5Q337-640 320-640t-28.5 11.5Q280-617 280-600t11.5 28.5Q303-560 320-560t28.5-11.5ZM320-600Zm0 280Z"/></svg>
                        </button>
                    </div>
                    <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 flex flex-col space-y-4 custom-scrollbar">
                        <div v-if="loadingMessages" class="flex-1 flex items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-normal"></div>
                        </div>
                        
                        <template v-else>
                            <div 
                                v-for="(msg, index) in messages" 
                                :key="msg.id || index"
                                :class="[Number(msg.sender) === Number(authUser?.id) ? 'self-end items-end' : 'self-start items-start', 'flex flex-col max-w-3/4 min-w-0']"
                            >
                                <p class="text-[10px] text-white opacity-40 mb-1 px-1" :class="Number(msg.sender) === Number(authUser?.id) ? 'text-right' : 'text-left'">
                                    {{ msg.user_name || (Number(msg.sender) === Number(authUser?.id) ? 'Yo' : selectedContact.name) }}
                                </p>
                                
                                <div :class="[Number(msg.sender) === Number(authUser?.id) ? 'bg-secondary-normal rounded-tr-none' : 'bg-forum-card rounded-tl-none', 'rounded-2xl p-3 text-white shadow-sm w-fit']">
                                    <p v-if="msg.type === 'text'" class="break-words whitespace-pre-wrap text-sm leading-relaxed">{{ msg.content }}</p>
                                    
                                    <img v-else-if="msg.type === 'image'" :src="msg.content" class="w-full max-w-80 max-h-64 object-contain rounded-lg cursor-pointer bg-black/20" @click="window.open(msg.content, '_blank')" />
                                    
                                    <a v-else-if="msg.type === 'pdf'" :href="msg.content" target="_blank" class="flex items-center gap-3 bg-black/20 p-3 rounded-xl no-underline text-white border border-white/5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--error-normal)" stroke-width="2"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>
                                        <span class="text-xs truncate max-w-[150px]">{{ msg.file_name || 'PDF' }}</span>
                                    </a>
                                </div>
                            </div>
                            
                            <div v-if="messages.length === 0" class="text-center text-white opacity-20 py-12 flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mb-2 opacity-30"><path d="M8 9h8"/><path d="M8 13h6"/><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12"/></svg>
                                <span class="text-sm italic">No hay mensajes aún.</span>
                            </div>
                        </template>
                    </div>

                    <div class="px-6 pb-4">
                        <TextChatBar @sendMessage="handleSendMessage" />
                    </div>
                </template>

                <div v-else class="flex-1 flex flex-col items-center justify-center p-12 text-center text-white opacity-10">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round" class="mb-6 opacity-20"><path d="M8 9h8"/><path d="M8 13h6"/><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12"/></svg>
                    <h2 class="text-2xl font-black uppercase tracking-tighter mb-2">{{ t.privateChat.title }}</h2>
                    <p class="text-xs font-bold uppercase tracking-widest max-w-xs leading-loose">
                        {{ t.privateChat.selectToStart }}
                    </p>
                </div>
            </div>

            <div class="w-full lg:w-90 border-r border-white/5 flex flex-col bg-accent-normal z-10 shrink-0" :class="{ 'hidden lg:flex': mobileShowChat }">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <button @click="openNavMenu" class="lg:hidden text-white/70 hover:text-white transition-colors cursor-pointer shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg>
                        </button>
                        <h2 class="text-2xl font-bold text-white">Chat Privado</h2>
                    </div>
                    <p class="text-xs text-white opacity-50 uppercase font-bold tracking-widest">
                        {{ 
                            (authUser?.role?.toLowerCase() === 'student' || authUser?.role?.toLowerCase() === 'alumno') ? 'Profesores' : 
                            ((authUser?.role?.toLowerCase() === 'admin' || authUser?.role?.toLowerCase() === 'administrador') ? 'Administradores' : 'Estudiantes')
                        }}
                    </p>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <div v-if="loadingContacts" class="px-6 space-y-4">
                        <div v-for="i in 3" :key="i" class="w-full h-16 skeleton"></div>
                    </div>

                    <div 
                        v-else
                        v-for="contact in contacts" 
                        :key="contact.id"
                        @click="selectContact(contact.id)"
                        class="px-6 py-4 flex items-center gap-3 cursor-pointer transition-colors border-b border-white/5"
                        :class="[Number(selectedContactId) === Number(contact.id) ? 'bg-white/10' : 'hover:bg-white/5']"
                    >
                        <UserAvatar :user="contact" size="w-10 h-10" />
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-white truncate">{{ contact.name }} {{ contact.last_name }}</h3>
                            <p class="text-[10px] text-white opacity-40 uppercase">{{ contact.role_name || 'Contacto' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.1); }

.break-words {
    overflow-wrap: break-word;
    word-break: break-word;
}
</style>