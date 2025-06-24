<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import config from "@/config.json";
import axios from "axios";

export default {
    name: "ShopView",
    components: {NavigationComponent, HeaderComponent},
    data() {
        return {
            tokens: 0,
            period: 1,
            costPerToken: 2,
            sub: {},
        };
    },
    async mounted() {
        this.tokens = this.$route.query.tokens;

        await axios.get (config.backend + "subscription/" + this.$route.query.plan).then((response) => {
            this.sub = response.data;
        }).catch((error) => {
            alert("Ошибка:\n" + error.response.data.message);
        });
    },
    methods: {
        async sendData () {
            await axios.post(config.backend + "subscription/subscribe", {
                initData: window.Telegram.WebApp.initData,
                tokens: this.tokens,
                subscription: this.sub.id,
                period: this.period,
            }).then((response) => {
                let a = document.createElement('a');
                a.href = response.data.url;
                a.target = '_self';
                a.style.display = 'none';

                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }).catch((error) => {
                alert("Ошибка:\n" + error.response.data.message);
            });
        }
    },
    computed: {
        username () {
            return (window.Telegram.WebApp.initDataUnsafe.user.first_name + " " + window.Telegram.WebApp.initDataUnsafe.user.last_name);
        },
        price () {
            let priceInt = this.sub.price * this.period -
                (this.sub.price * this.period * Math.min(Math.floor(this.period / 3)*0.05, 0.3))
                + this.tokens * this.costPerToken;

            return priceInt.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        sale () {
            return Math.min(Math.floor(this.period / 3)*0.05, 0.3)*100;
        },
        user() {
            return this.$store.state.user;
        },
    }
}
</script>

<template>
    <HeaderComponent />
    <section class="margin white relative">
        <a class="close-button" style="position: absolute; top: 15px; right: 15px;"
           href="/subscriptions"><i
            class="bi bi-x-lg"></i></a>
        <h2 class="mb_10">Тариф        <span>{{ sub.name }}</span></h2>
<!--        <div class="hint">* Вы можете подарить подписку</div>-->
<!--        <form method="POST" action=""><input type="hidden" name="PHPSESSID" value="ec48f339284f0424b862e2132a3c98e7" />-->
<!--            <input type="hidden" name="formId" value="userToGift">-->
<!--            <div class="form-group">-->
<!--                <div class="label-and-select" style="position: relative;">-->
<!--                    <input type="text" name="userToGiftCheck" style="margin: 0; width: 70%;"-->
<!--                           placeholder="Телеграм @ ник друга" id="regular_field"/>-->
<!--                    <button type="submit" class="btnSeperate" style="margin: 0 0 0 10px;">-->
<!--                        <i class="bi bi-arrow-right"></i>-->
<!--                    </button>-->
<!--                    <div id="animated-arrow" class="arrow-animation" style="display: none;">-->
<!--                        <i class="bi bi-chevron-double-down"></i>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </form>-->

        <div class="select-wrapper mt_5" style="width: 100%;">
            <div class="form-group">
                <div class="label-and-select">
                    <label for="payment_method">Метод оплаты</label>
                    <p>🛒 YooKassa</p>
                    <input id="payment_method" type="hidden" name="payment_method" value="overpay"/>
                </div>
            </div>
        </div>

        <div class="select-wrapper mt_10" style="width: 100%;">
            <div class="form-group">
                <div class="label-and-select">
                    <label for="subscription_length">Срок подписки</label>
                    <select v-model="period" id="subscription_length" name="subscription_length" class="select-input">
                        <option value="1" selected>
                            1 месяц
                        </option>
                        <option value="3">
                            3 месяца -5%
                        </option>
                        <option value="6">
                            6 месяцев -10%
                        </option>
                        <option value="12">
                            12 месяцев -20%
                        </option>
                    </select>
                    <i class="bi bi-chevron-double-down"></i>
                </div>
            </div>
        </div>


        <div class="defRow mt_10">
            <div class="leftCell">Получатель:</div>
            <div class="rightCell">
                {{ username }}
            </div>
        </div>
        <div class="defRow">
            <div class="leftCell">Токены:</div>
            <div class="rightCell">{{ tokens }}<i class="bi bi-lightning-charge-fill"></i></div>
        </div>
        <div class="defRow">
            <div class="leftCell">Итоговая скидка:</div>
            <div class="rightCell">{{ sale }}%</div>
        </div>
        <div class="defRow">
            <div class="leftCell">Итоговая цена:</div>
            <div class="rightCell">
                <div class="fullPrice">{{ price }} ₽</div>
            </div>
        </div>


        <!-- TG Stars -->
        <div class="payment-info button" data-duration="1" data-method="tgstars">
            <form method="post" action="">
                <input type="hidden" name="PHPSESSID" value="ec48f339284f0424b862e2132a3c98e7" />
                <input type="hidden" name="action" value="invoice_tgstars">
                <button type="button" style="padding: 14px;" class="cardButton mt_10" @click="sendData">СЧЕТ</button>
            </form>
        </div>
<!--        <div style="display: flex;-->
<!--            justify-content: center;-->
<!--            align-items: center;-->
<!--            padding-top: 10px;-->
<!--        ">-->
<!--            <a style="padding: 10px 0; background-color: #0b0b0e; color: white; font-weight: lighter; text-decoration: none;" id="problem" class="cardButton" target="_blank" href="#">Проблемы с оплатой?</a>-->
<!--        </div>-->
    </section>

    <div class="bottom"></div>
    <NavigationComponent />
</template>

<style src="../assets/main.css" scoped>

</style>