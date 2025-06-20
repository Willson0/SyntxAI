<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";

export default {
    name: "faqView",
    components: {NavigationComponent, HeaderComponent}
}
</script>

<template>
    <HeaderComponent />

    <section class="box white margin">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Где я могу посмотреть, как пользоваться всеми функциями чат-бота?    </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> В каждом разделе конкретного инструмента, а так же в главном меню чат-бота есть раздел инструкций, в котором подробно описываются все инструменты искусственного интеллекта.    </div>
    </section>

    <section class="box white margin">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Чат-бот бесплатный?    </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Функции в нашем чат-боте доступны бесплатно для ознакомления. Каждому пользователю начисляется 5 токенов и доступно большинство функций. После того, как токены израсходуются, нужно купить подписку или приобрести токены.    </div>
    </section>

    <section class="box white margin">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Что такое токены ⚡?    </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Токены, это внутренняя валюта, с помощью которой производится расчет за использования нейросетей в чат-боте. Каждая генерация во всех инструментах снимает определенное количество токенов, кроме языковых моделей (GPT и др.). Полный список цен описан в разделе "тарифы". Токенами нельзя пользоваться без подписки, но они никогда не сгорают.    </div>
    </section>

    <section class="box white margin">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Как происходит начисление токенов, если я покупаю подписку?    </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Токены начисляются один раз в момент покупки, на весь период использования.     </div>
    </section>

    <section class="box white margin">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Токены на моем балансе сгорают?    </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Нет. Токены никогда не сгорают и расходуются только когда вы пользуетесь функциями чат-бота. Если Вы приобрели подписку, но не успели потратить все токены до ее окончания - ничего страшного, ими можно будет пользоваться и после покупки новой подписки.    </div>
    </section>

    <section class="box white margin">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Как долго действует подписка?    </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Платную подписку можно приобрести на 1 календарный месяц, 6 месяцев и 12 месяцев. FREE подписка действует без ограничений по времени.    </div>
    </section>

    <section class="box white margin">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Как долго хранятся генерации пользователей на серверах?    </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> Мы стараемся не удалять сгенерированные пользователями работы в нашем сервисе, если пользователь активен. Однако, в некоторых случаях, спустя 60 дней, некоторые результаты работ могут быть удалены. Настоятельно рекомендуем сохранять успешные генерации на свои устройства.    </div>
    </section>

    <section class="box white margin">
        <div class="question">
            <i class="bi bi-question-square-fill red"></i> Автоматическое продление    </div>
        <div class="answer">
            <i class="bi bi-lightbulb-fill ok"></i> В сервисе действует система автоматического списания средств на ежемесячной, ежеквартальной, полугодовой или ежегодной основе. Вы можете отменить подписку в любой момент, выбрав купленный тариф в разделе «подписка» и кликнув на кнопку «отменить подписку».    </div>
    </section>
    <div class="bottom"></div>



    <NavigationComponent />
</template>

<style scoped>

</style>