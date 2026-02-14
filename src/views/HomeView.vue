<script setup>
    import NavBar from '../components/NavBar.vue';
    import SearchBar from '../components/SearchBar.vue';
    import { onMounted } from 'vue';

    import { useTranslations } from '../composables/useTranslations'

    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
    
    onMounted(() => {
        function showCreatedPost(postObjectValues) {

            const postObjectDict = Object.entries(postObjectValues);

            const mainBody = document.getElementById("mainBody");

            const newPostContainer = document.createElement("div");
            const newPostTitle = document.createElement("p");
            const newPostDescription = document.createElement("p");

            newPostContainer.className = "border border-red-500 w-[600px]";
            mainBody.append(newPostContainer);

            newPostTitle.innerHTML = postObjectDict[0][1];
            newPostTitle.className = "text-[30px]";

            newPostDescription.innerHTML = postObjectDict[2][1];
            newPostTitle.className = "text-[25px]";

            newPostContainer.append(newPostTitle);
            newPostContainer.append(newPostDescription);
        }

        const postObject = JSON.parse(sessionStorage.getItem("newPost"));

        if (postObject) {
            showCreatedPost(postObject);
        }

    });

</script>

<template>
    <main class="flex min-h-screen justify-between">
        <NavBar></NavBar>
        <section class="text-white w-[1580px] mr-4">
            <SearchBar></SearchBar>
            <div id="mainBody" class="flex flex-col gap-4 justify-center items-center min-h-[92.9vh]">
                <!-- Post 1 -->
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-question">Pregunta 1</div>
                    </div>
                    <h2 class="post-title">TITULO DEL POST</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">24</span>
                        </div>
                    </div>
                </div>

                <!-- Post 2 -->
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-question">Pregunta 2</div>
                    </div>
                    <h2 class="post-title">TITULO DEL POST</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">15</span>
                        </div>
                    </div>
                </div>

                <!-- Post 3 -->
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-question">Pregunta 3</div>
                    </div>
                    <h2 class="post-title">TITULO DEL POST</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">8</span>
                        </div>
                    </div>
                </div>

                <!-- Post 4 -->
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-question">Pregunta 4</div>
                    </div>
                    <h2 class="post-title">TITULO DEL POST</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                    </div>
                </div>

                <div class="post-card">
                    <div class="post-header">
                        <div class="post-question">Pregunta 4</div>
                    </div>
                    <h2 class="post-title">TITULO DEL POST</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                    </div>
                </div>

                <div class="post-card">
                    <div class="post-header">
                        <div class="post-question">Pregunta 4</div>
                    </div>
                    <h2 class="post-title">TITULO DEL POST</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                    </div>
                </div>

                                <div class="post-card">
                    <div class="post-header">
                        <div class="post-question">Pregunta 4</div>
                    </div>
                    <h2 class="post-title">TITULO DEL POST</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                    </div>
                </div>

                                <div class="post-card">
                    <div class="post-header">
                        <div class="post-question">Pregunta 4</div>
                    </div>
                    <h2 class="post-title">TITULO DEL POST</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
    .post-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        width: 1580px;
        border-radius: 16px;
        padding: 1.75rem;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

</style>