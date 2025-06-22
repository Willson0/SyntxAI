<script>

import NavigationComponent from "@/components/NavigationComponent.vue";
import HeaderComponent from "@/components/HeaderComponent.vue";
import PartnerNavigationComponent from "@/components/PartnerNavigationComponent.vue";
import axios from "axios";
import config from "@/config.json";

export default {
    name: "PartnerView",
    components: {PartnerNavigationComponent, HeaderComponent, NavigationComponent},
    data() {
        return {
            data: null,
            config: config,
        };
    },
    async mounted() {
        await axios.post(config.backend + "partner/my", {
            initData: window.Telegram.WebApp.initData,
        }).then((response) => {
            this.data = response.data;
        }).catch((error) => {

        });

        const notification = document.getElementById('copy-notification');
        document.querySelectorAll('span.promocode').forEach(span => {
            span.style.cursor = "pointer";
            span.addEventListener('click', async function() {
                const promoCode = this.innerText;
                try {
                    await navigator.clipboard.writeText(promoCode);
                    notification.style.display = "block";
                    setTimeout(() => notification.style.display = "none", 3000);
                } catch (error) {
                    console.error('Ошибка копирования:', error);
                    if (error.name === 'NotAllowedError') {
                        alert('Копирование в буфер обмена запрещено Вашей системой. Пожалуйста, скопируйте код вручную: ' + promoCode);
                    } else {
                        alert('Не удалось скопировать. Пожалуйста, скопируйте его вручную: ' + promoCode);
                    }
                }
            });
        });
    },
    methods: {

    },
    computed: {
        telegram_id () {
            return window.Telegram.WebApp.initDataUnsafe.user.id;
        },
        boostSubscriptions () {
            let result = "";
            for (let key in this.data?.boost) {
                result += "<b>" + this.data.boost[key].name + "</b> ";
                if (parseInt(key) !== parseInt(this.data.boost.length - 1)) result += "и ";
            }
            return result;
        },
        isBoosted () {
            for (let sub in this.data?.boost) {
                if (this.data.boost[sub].name === this.user.sub_name) return true;
            }
            return false;
        },
        user() {
            return this.$store.state.user;
        },
    }
}
</script>

<template>
    <HeaderComponent />

    <div style="width: 100%; text-align: center; font-weight: 800; font-size: 15px;">
        PARTNER
    </div>
    <PartnerNavigationComponent />
    <div class="switch-container">
        <span class="switch-label">🚀 BOOST</span>
        <label class="switch">
            <input type="checkbox" :checked="isBoosted" id="toggle" disabled >
            <span class="slider no-allowed-cursor"></span>
        </label>
        <i class="bi bi-info-square-fill inline-info-btn" onclick="toggleTooltip(this)" style="margin-left: 10px;">
            <span class="tooltip">С тарифом <span v-html="boostSubscriptions"></span> включается 🚀 BOOST и к вознаграждениям добавляется <b>еще 25%</b></span>
        </i>
    </div>
    <section class="box margin ">
        <div class="partnerBalance">
            <div class="stat-item">
                <div>
                    <span class="stat-icon"><i class="bi bi-piggy-bank-fill"></i></span>
                    <span class="stat-text">Баланс партнеров</span>
                    <span class="stat-number">{{ data?.sum.toFixed(3) }} ⚡</span>
                </div>
            </div>
            <div class="stat-item">
                <div>
                    <span class="stat-icon"><i class="bi bi-graph-up-arrow"></i></span>
                    <span class="stat-text">Всего продаж</span>
                    <span class="stat-number">{{ data?.count }} 🛒</span>
                </div>
            </div>
            <div class="stat-item">
                <div>
                    <span class="stat-icon"><i class="bi bi-currency-exchange"></i></span>
                    <span class="stat-text">Сумма продаж</span>
                    <span class="stat-number">{{ data?.payments.toFixed(2) }} ₽</span>
                </div>
            </div>
        </div>
        <a href="/partner/withdraw" class="cardButton" style="width: 100%; display: block;">Вывести в <span style="font-size: 13px; font-weight: 600;">₽</span></a>
    </section>

    <section class=" margin">
        <div class="infoBlock" style="margin: 0;">
            <i class="bi bi-info-square-fill"></i> Отправляйте друзьям/коллегам свою уникальную ссылку, чтобы зарабатывать вместе 👇    </div>
        <div class="statistics">
            <div class="stat-item promocodes">
                <div>
                    <span class="stat-icon"><i class="bi bi-link-45deg"></i></span>
                    <span class="stat-text promocodes">
                    <span class="promocode" style="word-wrap: anywhere">{{ config.bot }}?start=aff_{{ telegram_id }}</span>
                    <p class="promocode-about"><i class="bi bi-copy"></i> Нажмите на ссылку, чтобы скопировать</p>
                </span>
                </div>
            </div>
        </div>
    </section>

<!--    <section class="box margin white">-->
<!--        <div class="time-filters">-->
<!--            <a href="javascript:void(0);" onclick="changeReferralBalancePage('refpayments')" class="active">Платежи рефералов</a>-->
<!--            <a href="javascript:void(0);" onclick="changeReferralBalancePage('outpayments')" >Движение токенов</a>-->
<!--        </div>-->
<!--        <div class="statistics">-->
<!--            <div class="content-block">-->
<!--                <div class="stat-item">-->
<!--                    <div>-->
<!--                        <span class="stat-text" style="display: block; padding: 35px; width: 100%; text-align: center;">Транзакции отсутствуют.</span>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </section>-->

    <div id="copy-notification" class="copy-notification" style="display: none;">
        Реферальная ссылка скопирована.</div>
    <div class="bottom"></div>
    <NavigationComponent />
</template>

<style scoped>

</style>