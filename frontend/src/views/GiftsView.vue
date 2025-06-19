<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";

export default {
    name: "GiftsView",
    components: {NavigationComponent, HeaderComponent},
    data() {
        return {
            menuClicked: false,
            steps: [100, 280, 500, 750, 1000, 1500, 2000, 2500, 5000],
            prices: {
                "100": { "EUR": 3.6, "RUB": 360, "USD": 3.87, "STARS": 230 },
                "280": { "EUR": 9.9, "RUB": 990, "USD": 10.63, "STARS": 630 },
                "500": { "EUR": 14, "RUB": 1400, "USD": 15.04, "STARS": 895 },
                "750": { "EUR": 18.2, "RUB": 1820, "USD": 19.55, "STARS": 1163 },
                "1000": { "EUR": 23.5, "RUB": 2350, "USD": 25.24, "STARS": 1500 },
                "1500": { "EUR": 35, "RUB": 3500, "USD": 37.59, "STARS": 2236 },
                "2000": { "EUR": 46, "RUB": 4600, "USD": 49.41, "STARS": 2940 },
                "2500": { "EUR": 54, "RUB": 5400, "USD": 58, "STARS": 3450 },
                "5000": { "EUR": 99, "RUB": 9900, "USD": 106.34, "STARS": 6325 }
            },
        };
    },
    mounted() {
        this.setupMoreMenu();
        this.initializeSubMenus();
        this.initializeActiveTariff();
        this.initPromocodeCopy();
        this.initSlider();
        this.initLoadingScreen();
    },
    methods: {
        setupMoreMenu() {
            document.addEventListener("click", this.closeMenu);
        },
        toggleDropdown(event) {
            const dropdownMenu = document.querySelector('.dropdownMenu');
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            this.menuClicked = true;
            event.stopPropagation();
        },
        closeMenu(event) {
            const dropdownMenu = document.querySelector('.dropdownMenu');
            if (!this.menuClicked && !dropdownMenu.contains(event.target)) {
                dropdownMenu.style.display = 'none';
            }
            this.menuClicked = false;
        },
        updateURLWithSubmenu(submenuId, isOpen) {
            let hash = window.location.hash.substring(1);
            let ids = hash ? hash.split(',') : [];
            if (isOpen) {
                if (!ids.includes(submenuId)) ids.push(submenuId);
            } else {
                ids = ids.filter(id => id !== submenuId);
            }
            window.location.hash = ids.join(',');
        },
        initializeSubMenus() {
            const subMenus = document.getElementsByClassName('submenu');
            const idsToShow = window.location.hash.substring(1).split(',');
            for (let submenu of subMenus) {
                const id = submenu.id;
                const statItem = document.querySelector(`[data-submenu-id="${id}"]`);
                const icon = statItem ? statItem.querySelector('.stat-text i') : null;
                if (idsToShow.includes(id)) {
                    submenu.classList.add('show');
                    icon?.classList.replace('bi-caret-right-fill', 'bi-caret-down-fill');
                } else {
                    submenu.classList.remove('show');
                    icon?.classList.replace('bi-caret-down-fill', 'bi-caret-right-fill');
                }
            }
        },
        toggleSubMenu(submenuId, element) {
            const submenu = document.getElementById(submenuId);
            const icon = element.querySelector('.stat-text i');
            const isShown = submenu.classList.toggle('show');
            icon.classList.toggle('bi-caret-down-fill', isShown);
            icon.classList.toggle('bi-caret-right-fill', !isShown);
            this.updateURLWithSubmenu(submenuId, isShown);
        },
        changePage(timegap) {
            const hash = window.location.hash;
            window.location.href = `https://webapp.syntxai.net?page=account&timegap=${timegap}${hash}`;
        },
        changeReferralsPage(timegap) {
            const hash = window.location.hash;
            window.location.href = `https://webapp.syntxai.net?page=partner&section=referrals&timegap=${timegap}${hash}`;
        },
        changePricingPage(priceSection) {
            window.location.href = `https://webapp.syntxai.net?page=subscription&section=pricing&priceSection=${priceSection}`;
        },
        changeReferralBalancePage(priceSection) {
            window.location.href = `https://webapp.syntxai.net?page=partner&section=partnerbalance&priceSection=${priceSection}&PHPSESSID=e1138c8fed5233fe7479ae2434eb3389`;
        },
        changeTariff(tariffId) {
            const tariffs = document.getElementsByClassName('tariff-block');
            [...tariffs].forEach(el => el.classList.remove('active'));
            document.getElementById(tariffId)?.classList.add('active');

            const tariffButtons = document.querySelectorAll('ul.pricingchoose li');
            tariffButtons.forEach(el => el.classList.remove('active'));
            document.querySelector(`ul.pricingchoose li[onclick="changeTariff('${tariffId}')"]`)?.classList.add('active');

            history.pushState(null, null, '#' + tariffId);
        },
        initializeActiveTariff() {
            const hash = window.location.hash.replace('#', '') || 'basic';
            this.changeTariff(hash);
        },
        initPromocodeCopy() {
            const notification = document.getElementById('copy-notification');
            document.querySelectorAll('span.promocode').forEach(span => {
                span.style.cursor = 'pointer';
                span.addEventListener('click', async () => {
                    const promoCode = span.innerText;
                    try {
                        await navigator.clipboard.writeText(promoCode);
                        notification.style.display = 'block';
                        setTimeout(() => notification.style.display = 'none', 3000);
                    } catch (error) {
                        alert(`Не удалось скопировать. Пожалуйста, скопируйте его вручную: ${promoCode}`);
                    }
                });
            });
        },
        initSlider() {
            const slider = document.getElementById("myRange");
            const outputValue = document.getElementById("value");
            const rubElements = document.querySelectorAll(".priceRUB");
            const eurElements = document.querySelectorAll(".priceEUR");
            const usdElements = document.querySelectorAll(".priceUSD");
            const starsElements = document.querySelectorAll(".priceSTARS");
            const pricePerTokenRub = document.getElementById("pricePerTokenRUB");
            const pricePerTokenEur = document.getElementById("pricePerTokenEUR");
            const pricePerTokenUsd = document.getElementById("pricePerTokenUSD");
            const pricePerTokenStars = document.getElementById("pricePerTokenSTARS");
            const priceUsdDiscount = document.getElementById("priceUSDDiscount");
            const priceUsdDiscountPaykilla = document.getElementById("priceUSDDiscountPaykilla");
            const tokenAmountElement = document.getElementById("tokenAmount");

            slider.min = 0;
            slider.max = this.steps.length - 1;
            const initialIndex = this.steps.indexOf(500);
            slider.value = initialIndex >= 0 ? initialIndex : 0;

            const updateValues = () => {
                const stepIndex = slider.value;
                const stepValue = this.steps[stepIndex];
                const currentPrices = this.prices[stepValue];

                outputValue && (outputValue.textContent = stepValue);
                rubElements.forEach(el => el.textContent = currentPrices.RUB.toFixed(2));
                eurElements.forEach(el => el.textContent = currentPrices.EUR.toFixed(2));
                usdElements.forEach(el => el.textContent = currentPrices.USD.toFixed(2));
                starsElements.forEach(el => el.textContent = currentPrices.STARS.toFixed(2));

                pricePerTokenRub && (pricePerTokenRub.textContent = (currentPrices.RUB / stepValue).toFixed(2));
                pricePerTokenEur && (pricePerTokenEur.textContent = (currentPrices.EUR / stepValue).toFixed(4));
                pricePerTokenUsd && (pricePerTokenUsd.textContent = (currentPrices.USD / stepValue).toFixed(4));
                pricePerTokenStars && (pricePerTokenStars.textContent = (currentPrices.STARS / stepValue).toFixed(4));

                const discounted = currentPrices.USD * 0.95;
                priceUsdDiscount && (priceUsdDiscount.textContent = discounted.toFixed(2));
                priceUsdDiscountPaykilla && (priceUsdDiscountPaykilla.textContent = discounted.toFixed(2));

                tokenAmountElement && (tokenAmountElement.value = stepValue);
            };

            slider.addEventListener('input', updateValues);
            updateValues();
        },
        initLoadingScreen() {
            const loadingScreen = document.getElementById('loadingScreen');
            const links = document.querySelectorAll('a[href]');
            links.forEach(link => link.addEventListener('click', () => {
                loadingScreen.style.display = 'flex';
                loadingScreen.classList.remove('fadeOut');
            }));
            window.addEventListener('load', () => {
                loadingScreen.classList.add('fadeOut');
                loadingScreen.addEventListener('animationend', () => {
                    loadingScreen.style.display = 'none';
                });
            });
        },
        toggleTooltip(element) {
            const tooltip = element.querySelector('.tooltip');
            const isVisible = tooltip.style.display === 'block';
            document.querySelectorAll('.tooltip').forEach(el => el.style.display = 'none');
            if (!isVisible) {
                tooltip.style.display = 'block';
                setTimeout(() => document.addEventListener('click', this.documentClickHandler), 0);
            } else {
                document.removeEventListener('click', this.documentClickHandler);
            }
        },
        documentClickHandler(event) {
            document.querySelectorAll('.tooltip').forEach(tooltip => {
                if (!tooltip.contains(event.target) && !tooltip.parentElement.contains(event.target)) {
                    tooltip.style.display = 'none';
                }
            });
            document.removeEventListener('click', this.documentClickHandler);
        },
        confirmUseGift(type, value) {
            const messages = {
                tokens: `Вы уверены, что хотите зачислить ${value} токенов на свой аккаунт?`,
                subscription: `Вы уверены, что хотите активировать ${value} подписку на своем аккаунте?`,
                promo: `Вы уверены, что хотите активировать промокод на ${value}% на своем аккаунте?`,
                merch: `Вы уверены, что хотите получить ${value} ?`
            };
            return confirm(messages[type] || 'Вы уверены, что хотите использовать этот подарок?');
        }
    }
}
</script>

<template>
    <HeaderComponent />

    <section>
        <div class="giftContainer">
            <div class="infoBlock" style="width: 100%;">Подарков нет <i class="bi bi-emoji-frown-fill"></i><br /><br />Участвуйте в жизни проекта чтобы получать подарки каждый день!<br /><br />Подробности в <a href="https://t.me/syntxaicommunity">сообществе SYNTX</a>.</div>
        </div>
    </section>

    <div class="bottom"></div>

    <div class="bottom-info">
        Время сервера: 15.06.2025 23:32 | ID: 1337592809    </div>
    <NavigationComponent />
</template>

<style scoped>

</style>