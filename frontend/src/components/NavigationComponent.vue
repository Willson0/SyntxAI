<script>
import config from "@/config.json"
export default {
    name: "NavigationComponent",
    data () {
        return {
            config: config,
        }
    }
}
</script>

<template>
    <nav class="bottom-nav">
        <ul>
            <li><a href="/" ><i class="bi bi-person-check-fill"></i>Аккаунт</a></li>
            <li><a href="/partner" class="active"><i class="fas fa-handshake"></i>Партнерство</a></li>
<!--            <li><a href="/gifts" ><i class="bi bi-gift-fill"></i>Подарки</a></li>-->
            <li><a :href="config.telegram"><i style="color: #b53636;" class="bi bi-chat-left-heart-fill"></i>Сообщество</a></li>
        </ul>
    </nav>
</template>

<style src="../assets/main.css" scoped>

</style>