<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import PartnerNavigationComponent from "@/components/PartnerNavigationComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";

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
    script.async = true;
    script.onload = () => {
        this.initYooKassaWidget();
    };
    document.head.appendChild(script);
  },
    methods: {
        initYooKassaWidget() {
            const payoutData = new window.PayoutsData({
                type: "payout",
                success_callback: () => {
                    alert ("great!");
                },
                error_callback: (error) => {
                    alert ("error: " + error);
                },
                account_id: 508885
            });
            payoutData.render("YookassaChecker");
        },
        success () {
            alert ("Great!");
        }
    }
};
</script>

<template>
  <HeaderComponent />

  <div
    style="width: 100%; text-align: center; font-weight: 800; font-size: 15px"
  >
    WITHDRAW
  </div>
    <div id="YookassaChecker"></div>
  <PartnerNavigationComponent />
  <NavigationComponent />
</template>

<style src="../assets/main.css" scoped></style>