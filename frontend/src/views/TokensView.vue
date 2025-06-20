<script>
import NavigationComponent from "@/components/NavigationComponent.vue";
import HeaderComponent from "@/components/HeaderComponent.vue";

export default {
    name: "TokensView",
    components: {HeaderComponent, NavigationComponent},
    mounted () {
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
        var eurElements = document.querySelectorAll(".priceEUR");
        var usdElements = document.querySelectorAll(".priceUSD");
        var starsElements = document.querySelectorAll(".priceSTARS");

        var pricePerTokenEur = document.getElementById("pricePerTokenEUR");
        var pricePerTokenRub = document.getElementById("pricePerTokenRUB");
        var pricePerTokenUsd = document.getElementById("pricePerTokenUSD");
        var pricePerTokenStars = document.getElementById("pricePerTokenSTARS");
        var priceUsdDiscount = document.getElementById("priceUSDDiscount");
        var priceUsdDiscountPaykilla = document.getElementById("priceUSDDiscountPaykilla");

        var tokenAmountElement = document.getElementById("tokenAmount");

        // Данные, которые приходят из PHP
        var steps = [100,280,500,750,1000,1500,2000,2500,5000];
        var prices = {"100":{"EUR":3.6,"RUB":360,"USD":3.87,"STARS":230},"280":{"EUR":9.9,"RUB":990,"USD":10.63,"STARS":630},"500":{"EUR":14,"RUB":1400,"USD":15.04,"STARS":895},"750":{"EUR":18.2,"RUB":1820,"USD":19.55,"STARS":1163},"1000":{"EUR":23.5,"RUB":2350,"USD":25.24,"STARS":1500},"1500":{"EUR":35,"RUB":3500,"USD":37.59,"STARS":2236},"2000":{"EUR":46,"RUB":4600,"USD":49.41,"STARS":2940},"2500":{"EUR":54,"RUB":5400,"USD":58,"STARS":3450},"5000":{"EUR":99,"RUB":9900,"USD":106.34,"STARS":6325}};

        // Устанавливаем диапазон слайдера
        slider.min = 0;
        slider.max = steps.length - 1;

        // Пусть по умолчанию выбран шаг со значением 500
        var initialIndex = steps.indexOf(500);
        slider.value = initialIndex >= 0 ? initialIndex : 0;

        // Функция обновления значений при движении слайдера
        function updateValues() {
            var stepIndex = slider.value;
            var stepValue = steps[stepIndex];

            // Показываем текущее количество токенов
            if (outputValue) {
                outputValue.textContent = stepValue;
            }

            // Получаем цены для текущего шага
            var currentPrices = prices[stepValue];
            var priceEUR = currentPrices.EUR;
            var priceRUB = currentPrices.RUB;
            var priceUSD = currentPrices.USD;
            var priceSTARS = currentPrices.STARS;

            // Обновляем элементы с ценой в RUB
            rubElements.forEach(function(el) {
                el.textContent = priceRUB.toFixed(2);
            });

            // Обновляем элементы с ценой в EUR
            eurElements.forEach(function(el) {
                el.textContent = priceEUR.toFixed(2);
            });

            // Обновляем элементы с ценой в USD
            usdElements.forEach(function(el) {
                el.textContent = priceUSD.toFixed(2);
            });

            // Обновляем элементы с ценой в STARS
            starsElements.forEach(function(el) {
                el.textContent = priceSTARS.toFixed(2);
            });

            // Считаем и выводим стоимость 1 токена
            if (pricePerTokenRub) {
                pricePerTokenRub.textContent = (priceRUB / stepValue).toFixed(2);
            }
            if (pricePerTokenEur) {
                pricePerTokenEur.textContent = (priceEUR / stepValue).toFixed(4);
            }
            if (pricePerTokenUsd) {
                pricePerTokenUsd.textContent = (priceUSD / stepValue).toFixed(4);
            }
            if (pricePerTokenStars) {
                pricePerTokenStars.textContent = (priceSTARS / stepValue).toFixed(4);
            }

            // Обновляем цену со скидкой для крипты
            var discountedPriceUSD = priceUSD * 0.95;
            if (priceUsdDiscount) {
                priceUsdDiscount.textContent = discountedPriceUSD.toFixed(2);
            }
            // Обновляем цену со скидкой для крипты paykilla
            if (priceUsdDiscountPaykilla) {
                priceUsdDiscountPaykilla.textContent = discountedPriceUSD.toFixed(2);
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
                    <input type="range" min="100" max="10000" value="100" class="tokenslider" id="myRange">
                </div>
                <input type="hidden" name="tokenAmount" id="tokenAmount" value="100">

                <h4 style="font-weight: 200; margin-top: 0; font-size: 11px;">За 1 токен: <br /> <span id="pricePerTokenEUR"></span> EUR / <span id="pricePerTokenRUB"></span> RUB / <span id="pricePerTokenUSD"></span> USD / <span id="pricePerTokenSTARS"></span> ⭐️</h4>
            </div>
        </section>

        <section class="margin">
            <div class="infoBlock mt_20 mb_10"><i class="bi bi-info-square-fill"></i> Купить токены можно только с активной подпиской</div>

            <a class="cardButton" style="display: block;" href="/subscriptions">Приобрести подписку</a>
        </section>
    </form>

    <div class="bottom"></div>
    <NavigationComponent />
</template>

<style scoped>

</style>