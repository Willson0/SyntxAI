<script>
export default {
    name: 'PricingCardComponent',
    props: {
        planId: Number,
        title: String,
        tokenSwitcher: Boolean,
        tokenRange: Array, // [min, max]
        features: Array,
        modalId: String,
        priceHtml: String,
        buyLink: String,
        dataTokens1: Number,
        dataTokens2: Number,
    }
}
</script>

<template>
    <div class="pricing-card" :data-plan-id="planId">
        <div class="pricingCard">
            <div class="cardInfoIcon">
                <div class="sIcon info"></div>
                <div class="tt">Токен - внутренняя валюта чат-бота</div>
            </div>
            <div class="cardTitle">{{ title }}</div>

            <div v-if="tokenSwitcher" class="token-switcher" :id="'tokenSwitcher' + planId">
                <div class="token-slider" :id="'tokenSlider' + planId"></div>
                <div class="token-text">
                    <span>{{ tokenRange[0] }} токенов</span>
                    <span>{{ tokenRange[1] }} токенов</span>
                </div>
            </div>
            <div v-else class="token-noSwitcher">
                {{ tokenRange[1] }} токенов
            </div>

            <ul class="cardInfoTable">
                <li v-for="(item, index) in features" :key="index" v-html="item"></li>
            </ul>

            <a v-if="modalId" class="cardMore" @click="$emit('open-modal', modalId)" style="text-decoration: underline;">Что входит в тариф?</a>

            <div class="cardPrice" :id="'cardPrice' + planId" v-html="priceHtml"></div>

            <a class="cardButton mt_20" :href="buyLink" :data-tokens1="dataTokens1" :data-tokens2="dataTokens2">
                КУПИТЬ
            </a>
        </div>
    </div>
</template>
