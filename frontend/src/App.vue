<script>
import config from "@/config.json"
import axios from "axios";
    export default {
        async mounted () {
            await axios.post(config.backend + "auth/profile", {
                "initData": window.Telegram.WebApp.initData,
            }).then((response) => {
                this.$store.dispatch("updateUser", response.data);
            }).catch((error) => {
                alert("Ошибка:\n" + error.response.data.message);
                window.Telegram.WebApp.close();
            });
        },
    }
</script>

<template>
    <router-view />
</template>

<style>
</style>
