<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import PartnerNavigationComponent from "@/components/PartnerNavigationComponent.vue";

export default {
    name: "MyPartnerView",
    components: {PartnerNavigationComponent, NavigationComponent, HeaderComponent},
    data() {
        return {

        };
    },
    mounted() {
    },
    beforeDestroy() {
        document.removeEventListener("click", this.closeMenu);
    },
    methods: {
    }
}
</script>

<template>
    <HeaderComponent />

    <div style="width: 100%; text-align: center; font-weight: 800; font-size: 15px;">
        PARTNER</div>

    <PartnerNavigationComponent />

    <section class="box margin white">
        <div class="time-filters">
            <a href="https://webapp.syntxai.net?page=partner&section=referrals&timegap=new&PHPSESSID=f6e922d0bb8512def10eb1ddcc788cd0" class="active">Полный список</a>
            <a href="https://webapp.syntxai.net?page=partner&section=referrals&timegap=active&PHPSESSID=f6e922d0bb8512def10eb1ddcc788cd0" >Самые активные</a>
        </div>
        <div class="statistics">
            <div class="stat-item">
                <div>
                    <span class="stat-text" style="display: block; padding: 35px; width: 100%; text-align: center;">Рефералы отсутствуют.</span>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="pagination">
            </div>

        </div>
    </section>

    <div class="bottom"></div>
    <NavigationComponent />
</template>

<style scoped>

</style>