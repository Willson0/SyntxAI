<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import PartnerNavigationComponent from "@/components/PartnerNavigationComponent.vue";
import config from "@/config.json";
import axios from "axios";

export default {
    name: "MyPartnerView",
    components: {PartnerNavigationComponent, NavigationComponent, HeaderComponent},
    data() {
        return {
            partners: [],
        };
    },
    async mounted() {
        await axios.post(config.backend + "partner/my", {
            initData: window.Telegram.WebApp.initData,
        }).then((response) => {
            this.partners = response.data.partners;
        }).catch((error) => {

        });
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
            <a href="" @click="$event.preventDefault()" class="active">Полный список</a>
<!--            <a href="https://webapp.syntxai.net?page=partner&section=referrals&timegap=active&PHPSESSID=f6e922d0bb8512def10eb1ddcc788cd0" >Самые активные</a>-->
        </div>
        <div class="statistics">
            <div class="stat-item" v-if="partners.length === 0">
                <div>
                    <span class="stat-text" style="display: block; padding: 35px; width: 100%; text-align: center;">Рефералы отсутствуют.</span>
                </div>
            </div>
            <div class="stat-item" v-for="(partner, key) in partners" v-else>
                <div>
                    <span class="stat-icon"><i class="bi bi-person-plus-fill"></i></span>
                    <span class="stat-text payments_details"><span>#{{ key+1 }} {{partner.username}} </span></span>
                    <span class="stat-number payments_amounts">{{ new Date(partner.created_at).toLocaleString() }}</span>
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