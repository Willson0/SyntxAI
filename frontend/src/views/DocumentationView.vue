<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import PartnerNavigationComponent from "@/components/PartnerNavigationComponent.vue";

export default {
    name: "DocumentationView",
    components: {PartnerNavigationComponent, NavigationComponent, HeaderComponent},
    data() {
        return {
            menuClicked: false,
            steps: [100, 280, 500, 750, 1000, 1500, 2000, 2500, 5000],
            prices: {
                "100": { EUR: 3.6, RUB: 360, USD: 3.87, STARS: 230 },
                "280": { EUR: 9.9, RUB: 990, USD: 10.63, STARS: 630 },
                "500": { EUR: 14, RUB: 1400, USD: 15.04, STARS: 895 },
                "750": { EUR: 18.2, RUB: 1820, USD: 19.55, STARS: 1163 },
                "1000": { EUR: 23.5, RUB: 2350, USD: 25.24, STARS: 1500 },
                "1500": { EUR: 35, RUB: 3500, USD: 37.59, STARS: 2236 },
                "2000": { EUR: 46, RUB: 4600, USD: 49.41, STARS: 2940 },
                "2500": { EUR: 54, RUB: 5400, USD: 58, STARS: 3450 },
                "5000": { EUR: 99, RUB: 9900, USD: 106.34, STARS: 6325 },
            },
            selectedStepIndex: 2,
        }
    },
    mounted() {
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeMenu);
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

<!--    <section class="box margin white">-->
<!--        <div class="question">-->
<!--            <i class="bi bi-lightbulb-fill ok"></i> Рекомендуем посмотреть <a target="_blank" href="https://youtu.be/BW9f0nUdpAo">ознакомительное видео, экскурсия по партнерской программе за полторы минуты (КЛИК)</a>.-->
<!--        </div>-->
<!--    </section>-->
<!--    <section class="box margin white">-->
<!--        <div class="question">-->
<!--            <i class="bi bi-lightbulb-fill ok"></i> Рекомендуем посмотреть <a target="_blank" href="https://docs.syntx.ai/%F0%9F%A4%9D+%D0%9F%D0%B0%D1%80%D1%82%D0%BD%D0%B5%D1%80%D1%81%D0%BA%D0%B0%D1%8F+%D0%BF%D1%80%D0%BE%D0%B3%D1%80%D0%B0%D0%BC%D0%BC%D0%B0">полную инструкцию в нашей базе знаний (КЛИК)</a>.-->
<!--        </div>-->
<!--    </section>-->

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Как работет партнерская программа?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Партнер (вы) приглашает пользователей по индивидуальной ссылке (она находится на главной страничке раздела "<a href="?page=partner&PHPSESSID=e1138c8fed5233fe7479ae2434eb3389">партнерство</a>" в нижней части экрана) и получает за это вознаграждение.
            <br /><br />
            1. Пользователь регистрируется по уникальной ссылке партнера. <br />
            2. "Реферал" (тот, кто зарегистрировался по ссылке) покупает подписку. <br />
            3. Партнеру автоматически зачисляется % от покупки.
            <br /><br />
            <b>Важно!</b> Партнерская программа трехуровневая. Это значит, что если реферал, которого Вы пригласили, так же пригласит других пользователей по своей ссылке, зачисление придет и вам, и вашему рефералу, по цепочке.
            <br /><u>Всего таких уровня - 3.</u>
            <br /><br />
            <i>* Партнерские зачисления проводятся только тому, кто пригласил пользователя.</i>
        </div>
    </section>

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Как посчитать % вознаграждений?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i>Возраграждения рассчитываются для каждого уровня по следующим ставкам:

            <br /> <br /><i class="bi bi-1-square-fill"></i> Уровень: 15% от покупки.<br />
            <i class="bi bi-2-square-fill"></i> Уровень: 10% от покупки.<br />
            <i class="bi bi-3-square-fill"></i> Уровень: 5% от покупки.

            <br /><br />

            Представим, что вы пригласили пользователя <b>А</b>, который стал вашим рефералом первого уровня. Пользователь <b>А</b> пригласил пользователя <b>Б</b>, который стал вашим рефералом второго уровня. Пользователь <b>Б</b> пригласил пользователя <b>В</b>, который стал вашим рефералом третьего уровня.
            <br /><br />
            Каждый из приглашенных приобрел тариф 💎 <b>VIP</b> за 50 EUR / 5000 RUB.
            <br /><br />
            <b>Партнеру будет зачислено:</b> <br />
            <i class="bi bi-plus-square-fill"></i> От реферала А: 7.00€ / 700₽ (15%) <br />
            <i class="bi bi-plus-square-fill"></i> От реферала Б: 5.00€ / 500₽ (10%) <br />
            <i class="bi bi-plus-square-fill"></i> От реферала В: 2.50€ / 250₽ (5%) <br />

            <br />
            Итого к зачислению на счет партнера: <b>14.50€</b> или <b>1450₽</b> (30%).
            <br /><br />

            <i>*Не обязательно иметь подписку, чтобы получать вознаграждения.</i>
        </div>
    </section>

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Что такое 🚀 BOOST?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> 🚀 BOOST позвляет зарабатывать больше с каждого привлеченного реферала и работает для партнера с активной 💎 <b>VIP</b> подпиской.
            <br /><br />
            В этом случае, к общему зачислению с любой продажи добавляется еще <b>25%</b> от суммы зачисления.<br /><br />

            <i>*Например, дополнительно к примеру расчета, указанному выше, будет добавлено еще 4.35€ / 435₽.</i>

        </div>
    </section>

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Проводятся ли зачисления за отдельную покупку токенов (не подписка)?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Да. С любой оплаты реферала в рамках чат-бота будет проведено автоматическое зачисление % на счет партнера.
        </div>
    </section>

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Выплата за реферала разовая?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Нет. Выплаты производятся на постоянной основе. Пока пользователь платит - партнер получает вознаграждение. То есть за каждую покупку будет переведена соответствующая комиссия на счет партнера.
        </div>
    </section>

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> В какой валюте я получаю вознаграждения?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Вознаграждения производятся в ⚡токенах в течение 10 минут после оплаты рефералом, автоматически. Токены, это внутренняя валюта проекта SYNTX, которая приравнивается по определенному курсу к евро, доллару или рублю.
            <br /><br />
            <i>* Стоимость 1 токена приблизетельно 0.02€ / 2.00₽.</i>
        </div>
    </section>

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Токены партнера это те же токены, что и токены в аккаунте чат-бота?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Баланс партнера и баланс аккаунта в чат-боте разделен. При этом, эквивалент токенов равноценен.
        </div>
    </section>

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Как и куда вывести токены?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Есть 3 способа реализации токенов:
            <br /><br />
            1. Вывести на личный счет в деньгах (доллары, рубли или евро на банковский счет, карту или криптовалюту).<br />
            2. Перевести на личный счет для использования внутри чат-бота.<br />
            3. Подарить (перевести) другому пользователю.
            <br /><br />
            <i>*Если приобретать токены для работы с чат-ботом за деньги, стоимость одной единицы менее выгодна, нежели использовать токены, полученные в виде вознаграждения за привлечение рефералов.</i>
        </div>
    </section>

    <section class="box margin white">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Как мне вывести средства?
        </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Нажмите на кнопку "Вывести в ₽ € $" на главной страничке раздела "<a href="?page=partner&PHPSESSID=e1138c8fed5233fe7479ae2434eb3389">партнерство</a>":
            <br /><br />
            1. Укажите направление (банковский счет, карта, криптовалюта или свой счет/счет другого пользователя внутри чат-бота).<br />
            2. Если необходимо, выберите валюту (доллары, рубли, евро).<br />
            3. Укажите сумму вывода или количество токенов.<br />
            4. Нажмите кнопку вывода.
            <br /><br />
            <i>*Обработка платежей занимает до 5 рабочих дней (не относится к переводам токенов внутри чат-бота).</i>
        </div>
    </section>

    <div class="bottom"></div>
    <NavigationComponent />

</template>

<style src="../assets/main.css" scoped>

</style>