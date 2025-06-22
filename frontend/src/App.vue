<script>
import config from "@/config.json"
import axios from "axios";
    export default {
        async mounted () {
            await axios.post(config.backend + "auth/profile", {
                "initData": window.Telegram.WebApp.initData,
            }).then((response) => {
                this.$store.dispatch("updateUser", response.data);

                let loading = document.querySelector(".loading");
                loading.style.opacity = 0;

                setTimeout (() => {
                    loading.style.display = "none";
                }, 400);
            }).catch((error) => {
                alert("Ошибка:\n" + error.response.data.message);
                window.Telegram.WebApp.close();
            });
        },
    }
</script>

<template>
    <div class="loading"></div>
    <router-view />
</template>

<style>
    .loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: var(--tg-theme-bg-color);
        z-index: 999999999;
        transition: 0.4s;
    }
</style>
