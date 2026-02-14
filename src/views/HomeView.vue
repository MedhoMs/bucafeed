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
        <section class="text-white w-[1500px] mr-14 mb-4">
            <SearchBar></SearchBar>
            <div id="mainBody" class="flex flex-col gap-4 justify-center items-center min-h-[92.9vh]">
                <!-- Post 1 -->
                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 1</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">24</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <!-- Post 2 -->
                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 2</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">15</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <!-- Post 3 -->
                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 3</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">8</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <!-- Post 4 -->
                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 4</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 4</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 4</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 4</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 4</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="post-card">
                    <h2 class="post-title">TITULO DEL POST 4</h2>
                    <div class="post-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">42</span>
                        </div>
                        <hr></hr>
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
        width: 1500px;
        border-radius: 20px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .post-card:hover {
        cursor: pointer;
        background: linear-gradient(135deg, rgba(54, 54, 54, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
    }

    .post-title {
        font-size: 20px;
        font-weight: bold;
    }

    .responses-badge {
        display: flex;
        justify-content: flex-end;
    }
</style>