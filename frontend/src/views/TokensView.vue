<script>
import NavigationComponent from "@/components/NavigationComponent.vue";
import HeaderComponent from "@/components/HeaderComponent.vue";
import config from "@/config.json";
import axios from "axios";

export default {
    name: "TokensView",
    data () {
        return {
            prices: [],
            tokens: 0,
        }
    },
    components: {HeaderComponent, NavigationComponent},
    async mounted () {
        await axios.get(config.backend + "token").then((response) => {
            this.prices = response.data;
            this.initSlider();
        }).catch((error) => {
        });
    },
    methods: {
        initSlider () {
            const slider = document.getElementById('myRange');

            function updateSlider() {
                const min = slider.min;
                const max = slider.max;
                const value = slider.value;

                const percentage = (value - min) / (max - min) * 100;

                slider.style.background = `linear-gradient(to right, #66EBE3 0%, #66EBE3 ${percentage}%, #D57CFF ${percentage}%, #D57CFF 100%)`;
            }

            slider.addEventListener('input', updateSlider);
            updateSlider(); // Инициализация при загрузке страницы

            var outputValue = document.getElementById("value");

            // Вместо getElementById("priceRUB") берем все элементы с классом priceRUB
            var rubElements = document.querySelectorAll(".priceRUB");
            var pricePerTokenRub = document.getElementById("pricePerTokenRUB");

            var tokenAmountElement = document.getElementById("tokenAmount");

            // Устанавливаем диапазон слайдера
            slider.min = 0;
            slider.max = Object.keys(this.prices).length - 1;

            const prices = this.prices;

            // Пусть по умолчанию выбран шаг со значением 500
            var initialIndex = 0;
            slider.value = initialIndex >= 0 ? initialIndex : 0;

            // Функция обновления значений при движении слайдера
            function updateValues() {
                var stepValue = Object.keys(prices)[slider.value];

                // Показываем текущее количество токенов
                if (outputValue) {
                    outputValue.textContent = stepValue;
                }

                // Получаем цены для текущего шага
                var currentPrice = prices[stepValue];

                // Обновляем элементы с ценой в RUB
                rubElements.forEach(function(el) {
                    el.textContent = currentPrice.toFixed(2);
                });

                // Считаем и выводим стоимость 1 токена
                if (pricePerTokenRub) {
                    pricePerTokenRub.textContent = (currentPrice / stepValue).toFixed(2);
                }

                // Присваиваем текущее значение слайдера в hidden-поле
                if (tokenAmountElement) {
                    tokenAmountElement.value = stepValue;
                }
            }

            // Событие на изменение слайдера
            slider.addEventListener('input', updateValues);

            // Вызываем обновление при загрузке
            updateValues();
        },
        async sendData () {
            await axios.post(config.backend + "token/buy", {
                initData: window.Telegram.WebApp.initData,
                tokens: Object.keys(this.prices)[this.tokens],
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
    }
}
</script>

<template>
    <HeaderComponent />

    <form id="tokenBuyForm" method="POST" action=""><input type="hidden" name="PHPSESSID" value="3e14237f4a00fc45b0ff64354872b4fb" />
        <input type="hidden" name="formId" value="tokenPurchase">
        <section class="margin">
            <div class="tokenbuy-container">
                <h2 style="margin: 0;">Выберите количество: <br /> <span id="value"></span> ⚡</h2>

                <div class="rangeContainer">
                    <input v-model="tokens" type="range" min="100" max="10000" value="100" class="tokenslider" id="myRange">
                </div>
                <input type="hidden" name="tokenAmount" id="tokenAmount" value="100">

                <h4 style="font-weight: 200; margin-top: 0; font-size: 11px;">За 1 токен: <br /> <span id="pricePerTokenRUB"></span> RUB</h4>
            </div>
        </section>

        <section class="margin">
<!--            <div class="infoBlock mt_20 mb_10"><i class="bi bi-info-square-fill"></i> Купить токены можно только с активной подпиской</div>-->

            <a class="cardButton" style="display: block;" @click="$event.preventDefault(); sendData()">Приобрести токены</a>
        </section>
    </form>

    <div class="bottom"></div>
    <NavigationComponent />
</template>

<style src="../assets/main.css" scoped>

</style>