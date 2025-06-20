<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";

export default {
    name: "GiftsView",
    components: {NavigationComponent, HeaderComponent},
    data() {
        return {
        };
    },
    mounted() {
    },
    methods: {
    }
}
</script>

<template>
    <HeaderComponent />

    <section>
        <div class="giftContainer">
            <div class="infoBlock" style="width: 100%;">Подарков нет <i class="bi bi-emoji-frown-fill"></i><br /><br />Участвуйте в жизни проекта чтобы получать подарки каждый день!</div>
        </div>
    </section>

    <div class="bottom"></div>
    <NavigationComponent />
</template>

<style scoped>

</style>