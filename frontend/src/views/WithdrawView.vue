<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import PartnerNavigationComponent from "@/components/PartnerNavigationComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import config from "@/config.json";
import axios from "axios";

export default {
  name: "WithdrawView",
  components: {
    NavigationComponent,
    PartnerNavigationComponent,
    HeaderComponent,
  },
  async mounted() {
    const script = document.createElement("script");
    script.src = "https://yookassa.ru/payouts-data/3.1.0/widget.js";
    script.onload = () => {
        this.initYooKassaWidget();
    };
    document.head.appendChild(script);
  },
    methods: {
        initYooKassaWidget() {
            const payoutData = new window.PayoutsData({
                type: "payout",
                account_id: '508885',
                success_callback: (info) => {
                    axios.post(config.backend + "partner/payout", {
                        initData: window.Telegram.WebApp.initData,
                        data: info,
                    }).then((response) => {
                        alert ("Успешно. Ожидайте выплаты.");
                        window.location.href = "/";
                    }).catch((error) => {
                        alert ("Ошибка: " + error);
                    })
                },
                error_callback: (error) => {
                    alert ("error: " + error);
                },
            });
            payoutData.render("yookassa").then(() => {
            });
        },
    }
};
</script>

<template>
  <HeaderComponent />

  <div style="width: 100%; text-align: center; font-weight: 800; font-size: 15px">
    WITHDRAW
  </div>
  <PartnerNavigationComponent />
    <div id="yookassa"></div>
  <NavigationComponent />
</template>

<style scoped>
</style>