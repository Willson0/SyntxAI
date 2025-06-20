<script>
import NavigationComponent from "@/components/NavigationComponent.vue";
import HeaderComponent from "@/components/HeaderComponent.vue";
import PartnerNavigationComponent from "@/components/PartnerNavigationComponent.vue";

export default {
    name: "PartnerView",
    components: {PartnerNavigationComponent, HeaderComponent, NavigationComponent},
    data() {
        return {
            menuClicked: false,
            sliderValue: 0,
            steps: [100, 280, 500, 750, 1000, 1500, 2000, 2500, 5000],
            prices: {
                100: { EUR: 3.6, RUB: 360, USD: 3.87, STARS: 230 },
                280: { EUR: 9.9, RUB: 990, USD: 10.63, STARS: 630 },
                500: { EUR: 14, RUB: 1400, USD: 15.04, STARS: 895 },
                750: { EUR: 18.2, RUB: 1820, USD: 19.55, STARS: 1163 },
                1000: { EUR: 23.5, RUB: 2350, USD: 25.24, STARS: 1500 },
                1500: { EUR: 35, RUB: 3500, USD: 37.59, STARS: 2236 },
                2000: { EUR: 46, RUB: 4600, USD: 49.41, STARS: 2940 },
                2500: { EUR: 54, RUB: 5400, USD: 58, STARS: 3450 },
                5000: { EUR: 99, RUB: 9900, USD: 106.34, STARS: 6325 }
            },
            isTouchDevice: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
            emojiTooltip: null,
            hideTooltipHandler: null
        };
    },
    mounted() {
        this.initEventListeners();
        this.initializeSubMenus();
        // this.initializeActiveTariff();
        this.updateValues();

        this.emojiTooltip = document.getElementById('emojiTooltip');

        if (this.emojiTooltip) {
            const handler = this.toggleEmojiTooltip;
            const hideHandler = this.hideEmojiTooltip;

            this.emojiTooltip.addEventListener('click', handler);
            document.addEventListener('click', hideHandler);

            this.hideTooltipHandler = hideHandler; // сохранить для removeEventListener при destroy
        }
    },
    methods: {
        toggleEmojiTooltip(event) {
            console.log('clicked');
            const tooltip = document.getElementById('emojiTooltip');
            if (tooltip) {
                tooltip.classList.toggle('show-tooltip');
                event.stopPropagation();
            }
        },
        hideEmojiTooltip(event) {
            const tooltip = document.getElementById('emojiTooltip');
            if (tooltip && !tooltip.contains(event.target)) {
                tooltip.classList.remove('show-tooltip');
            }
        },
        initEventListeners() {
            document.addEventListener('click', this.closeMenu);

            const loadingScreen = document.getElementById('loadingScreen');
            const links = document.querySelectorAll('a[href]');
            const notification = document.getElementById('copy-notification');

            links.forEach(link => {
                link.addEventListener('click', () => {
                    loadingScreen.style.display = 'flex';
                    loadingScreen.classList.remove('fadeOut');
                });
            });

            // window.addEventListener('load', () => {
            //     loadingScreen.classList.add('fadeOut');
            //     loadingScreen.addEventListener('animationend', () => {
            //         loadingScreen.style.display = 'none';
            //     });
            // });

            document.querySelectorAll('span.promocode').forEach(span => {
                span.style.cursor = "pointer";
                span.addEventListener('click', async () => {
                    const promoCode = span.innerText;
                    try {
                        await navigator.clipboard.writeText(promoCode);
                        notification.style.display = "block";
                        setTimeout(() => notification.style.display = "none", 3000);
                    } catch (error) {
                        alert('Не удалось скопировать. Скопируйте вручную: ' + promoCode);
                    }
                });
            });
        },
        toggleMenu(event) {
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

            if (isOpen && !ids.includes(submenuId)) {
                ids.push(submenuId);
            } else if (!isOpen) {
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
                const icon = statItem?.querySelector('.stat-text i');

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

            if (submenu.classList.contains('show')) {
                submenu.classList.remove('show');
                icon.classList.replace('bi-caret-down-fill', 'bi-caret-right-fill');
                this.updateURLWithSubmenu(submenuId, false);
            } else {
                submenu.classList.add('show');
                icon.classList.replace('bi-caret-right-fill', 'bi-caret-down-fill');
                this.updateURLWithSubmenu(submenuId, true);
            }
        },
        changePage(timegap) {
            window.location.href = `https://webapp.syntxai.net?page=account&timegap=${timegap}${window.location.hash}`;
        },
        changeReferralsPage(timegap) {
            window.location.href = `https://webapp.syntxai.net?page=partner&section=referrals&timegap=${timegap}${window.location.hash}`;
        },
        changePricingPage(priceSection) {
            window.location.href = `https://webapp.syntxai.net?page=subscription&section=pricing&priceSection=${priceSection}`;
        },
        changeReferralBalancePage(priceSection) {
            window.location.href = `https://webapp.syntxai.net?page=partner&section=partnerbalance&priceSection=${priceSection}&PHPSESSID=f6e922d0bb8512def10eb1ddcc788cd0`;
        },
        // changeTariff(tariffId) {
        //     const tariffs = document.getElementsByClassName('tariff-block');
        //     [...tariffs].forEach(t => t.classList.remove('active'));
        //     document.getElementById(tariffId).classList.add('active');
        //
        //     document.querySelectorAll('ul.pricingchoose li').forEach(li => li.classList.remove('active'));
        //     const target = document.querySelector(`ul.pricingchoose li[onclick="changeTariff('${tariffId}')"]`);
        //     if (target) target.classList.add('active');
        //
        //     history.pushState(null, null, `#${tariffId}`);
        // },
        // initializeActiveTariff() {
        //     const hash = window.location.hash.replace('#', '');
        //     this.changeTariff(hash || 'basic');
        // },
        updateValues() {
            // const slider = document.getElementById("myRange");
            const outputValue = document.getElementById("value");
            const rubElements = document.querySelectorAll(".priceRUB");
            const eurElements = document.querySelectorAll(".priceEUR");
            const usdElements = document.querySelectorAll(".priceUSD");
            const starsElements = document.querySelectorAll(".priceSTARS");

            const pricePerTokenEur = document.getElementById("pricePerTokenEUR");
            const pricePerTokenRub = document.getElementById("pricePerTokenRUB");
            const pricePerTokenUsd = document.getElementById("pricePerTokenUSD");
            const pricePerTokenStars = document.getElementById("pricePerTokenSTARS");
            const priceUsdDiscount = document.getElementById("priceUSDDiscount");
            const priceUsdDiscountPaykilla = document.getElementById("priceUSDDiscountPaykilla");
            const tokenAmountElement = document.getElementById("tokenAmount");

            // slider.addEventListener('input', () => {
            //     const stepValue = this.steps[slider.value];
            //     const currentPrices = this.prices[stepValue];
            //     outputValue.textContent = stepValue;
            //
            //     rubElements.forEach(el => el.textContent = currentPrices.RUB.toFixed(2));
            //     eurElements.forEach(el => el.textContent = currentPrices.EUR.toFixed(2));
            //     usdElements.forEach(el => el.textContent = currentPrices.USD.toFixed(2));
            //     starsElements.forEach(el => el.textContent = currentPrices.STARS.toFixed(2));
            //
            //     pricePerTokenRub.textContent = (currentPrices.RUB / stepValue).toFixed(2);
            //     pricePerTokenEur.textContent = (currentPrices.EUR / stepValue).toFixed(4);
            //     pricePerTokenUsd.textContent = (currentPrices.USD / stepValue).toFixed(4);
            //     pricePerTokenStars.textContent = (currentPrices.STARS / stepValue).toFixed(4);
            //
            //     const discounted = currentPrices.USD * 0.95;
            //     priceUsdDiscount.textContent = discounted.toFixed(2);
            //     priceUsdDiscountPaykilla.textContent = discounted.toFixed(2);
            //
            //     tokenAmountElement.value = stepValue;
            // });
            //
            // slider.value = this.steps.indexOf(500);
            // slider.dispatchEvent(new Event('input'));
        },
        toggleTooltip(element) {
            const tooltip = element.querySelector('.tooltip');
            const visible = tooltip.style.display === "block";
            document.querySelectorAll('.tooltip').forEach(t => t.style.display = "none");
            if (!visible) {
                tooltip.style.display = "block";
                setTimeout(() => document.addEventListener('click', this.documentClickHandler), 0);
            } else {
                document.removeEventListener('click', this.documentClickHandler);
            }
        },
        documentClickHandler(event) {
            document.querySelectorAll('.tooltip').forEach(tooltip => {
                if (!tooltip.contains(event.target) && !tooltip.parentElement.contains(event.target)) {
                    tooltip.style.display = "none";
                }
            });
            document.removeEventListener('click', this.documentClickHandler);
        }
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
            <input type="checkbox" id="toggle" disabled >
            <span class="slider no-allowed-cursor"></span>
        </label>
        <i class="bi bi-info-square-fill inline-info-btn" onclick="toggleTooltip(this)" style="margin-left: 10px;">
            <span class="tooltip">С тарифом <b>VIP</b> 💎 и <b>ELITE</b> 💣 включается 🚀 BOOST и к вознаграждениям добавляется <b>еще 25%</b></span>
        </i>
    </div>
    <section class="box margin ">
        <div class="partnerBalance">
            <div class="stat-item">
                <div>
                    <span class="stat-icon"><i class="bi bi-piggy-bank-fill"></i></span>
                    <span class="stat-text">Баланс партнера</span>
                    <span class="stat-number">0.000 ⚡</span>
                </div>
            </div>
            <div class="stat-item">
                <div>
                    <span class="stat-icon"><i class="bi bi-graph-up-arrow"></i></span>
                    <span class="stat-text">Всего продаж</span>
                    <span class="stat-number">0 🛒</span>
                </div>
            </div>
            <div class="stat-item">
                <div>
                    <span class="stat-icon"><i class="bi bi-currency-exchange"></i></span>
                    <span class="stat-text">Сумма продаж</span>
                    <span class="stat-number">0.00 ₽ / 0.00 €</span>
                </div>
            </div>
        </div>
        <a href="?page=partner&section=transferPartnerTokens&PHPSESSID=f6e922d0bb8512def10eb1ddcc788cd0" class="cardButton" style="width: 100%; display: block;">Вывести в <span style="font-size: 13px; font-weight: 600;">₽ € $</span></a>
    </section>

    <section class=" margin">
        <div class="infoBlock" style="margin: 0;">
            <i class="bi bi-info-square-fill"></i> Отправляйте друзьям/коллегам свою уникальную ссылку, чтобы зарабатывать вместе 👇    </div>
        <div class="statistics">
            <div class="stat-item promocodes">
                <div>
                    <span class="stat-icon"><i class="bi bi-link-45deg"></i></span>
                    <span class="stat-text promocodes">
                    <span class="promocode">https://t.me/syntxaibot?start=aff_1337592809</span>
                    <p class="promocode-about"><i class="bi bi-copy"></i> Нажмите на ссылку, чтобы скопировать</p>
                </span>
                </div>
            </div>
        </div>
    </section>

    <section class="box margin white">
        <div class="time-filters">
            <a href="javascript:void(0);" onclick="changeReferralBalancePage('refpayments')" class="active">Платежи рефералов</a>
            <a href="javascript:void(0);" onclick="changeReferralBalancePage('outpayments')" >Движение токенов</a>
        </div>
        <div class="statistics">
            <div class="content-block">
                <div class="stat-item">
                    <div>
                        <span class="stat-text" style="display: block; padding: 35px; width: 100%; text-align: center;">Транзакции отсутствуют.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="copy-notification" class="copy-notification" style="display: none;">
        Реферальная ссылка скопирована.</div>
    <div class="bottom"></div>
    <NavigationComponent />
</template>

<style scoped>

</style>