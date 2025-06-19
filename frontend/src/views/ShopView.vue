<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";

export default {
    name: "ShopView",
    components: {NavigationComponent, HeaderComponent},
    data() {
        return {
            currentIndex: 2,
            cards: [],
            isDragging: false,
            startX: 0,
            moveX: 0,
            startIndex: 0,
            startTime: 0,
            isTap: false,
            tapTimeout: null,
            activeCardIndex: 2,
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
            }
        };
    },
    mounted() {
        this.setupStyles();

        // Additional DOM logic from plain JS converted to Vue Options API
        this.addEventListenersForPayment();
        this.setupAnimatedArrow();
        this.setupTelegramDetection();
        this.setupStripeCheckboxValidation();

        this.initializeDropdown();
        this.initializeSubMenus();
        this.initializeActiveTariff();
        this.initTooltip();
        this.initPromoCopy();
        this.setupLoadingScreen();
    },
    methods: {
        initializeDropdown() {
            const moreButton = document.querySelector('.moreMenu');
            const dropdownMenu = document.querySelector('.dropdownMenu');

            moreButton.onclick = (event) => {
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                this.menuClicked = true;
                event.stopPropagation();
            };

            document.addEventListener('click', (event) => {
                if (!this.menuClicked && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.style.display = 'none';
                }
                this.menuClicked = false;
            });
        },

        updateURLWithSubmenu(submenuId, isOpen) {
            const hash = window.location.hash.substring(1);
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

            Array.from(subMenus).forEach(menu => {
                const id = menu.id;
                const statItem = document.querySelector(`[data-submenu-id="${id}"]`);
                const icon = statItem?.querySelector('.stat-text i');

                if (idsToShow.includes(id)) {
                    menu.classList.add('show');
                    icon?.classList.replace('bi-caret-right-fill', 'bi-caret-down-fill');
                } else {
                    menu.classList.remove('show');
                    icon?.classList.replace('bi-caret-down-fill', 'bi-caret-right-fill');
                }
            });
        },

        toggleSubMenu(submenuId, element) {
            const submenu = document.getElementById(submenuId);
            const icon = element.querySelector('.stat-text i');

            const isOpen = submenu.classList.contains('show');
            submenu.classList.toggle('show', !isOpen);
            icon.classList.toggle('bi-caret-right-fill', isOpen);
            icon.classList.toggle('bi-caret-down-fill', !isOpen);
            this.updateURLWithSubmenu(submenuId, !isOpen);
        },

        changePage(timegap) {
            const hash = window.location.hash;
            const url = `https://webapp.syntxai.net?page=account&timegap=${timegap}${hash}`;
            window.location.href = url;
        },

        changeReferralsPage(timegap) {
            const hash = window.location.hash;
            const url = `https://webapp.syntxai.net?page=partner&section=referrals&timegap=${timegap}${hash}`;
            window.location.href = url;
        },

        changePricingPage(priceSection) {
            const url = `https://webapp.syntxai.net?page=subscription&section=pricing&priceSection=${priceSection}`;
            window.location.href = url;
        },

        changeReferralBalancePage(priceSection) {
            const url = `https://webapp.syntxai.net?page=partner&section=partnerbalance&priceSection=${priceSection}&PHPSESSID=ec48f339284f0424b862e2132a3c98e7`;
            window.location.href = url;
        },

        initializeActiveTariff() {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(hash)) {
                this.changeTariff(hash);
            } else {
                this.changeTariff('basic');
            }
        },

        changeTariff(tariffId) {
            document.querySelectorAll('.tariff-block').forEach(el => el.classList.remove('active'));
            document.getElementById(tariffId)?.classList.add('active');

            document.querySelectorAll('ul.pricingchoose li').forEach(li => li.classList.remove('active'));
            const targetLi = document.querySelector(`ul.pricingchoose li[onclick="changeTariff('${tariffId}')"]`);
            targetLi?.classList.add('active');

            history.pushState(null, null, `#${tariffId}`);
        },

        initTooltip() {
            document.querySelectorAll('.tooltip').forEach(el => el.style.display = "none");
        },

        toggleTooltip(element) {
            const tooltip = element.querySelector('.tooltip');
            const isVisible = tooltip.style.display === "block";

            document.querySelectorAll('.tooltip').forEach(el => el.style.display = "none");

            if (!isVisible) {
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
        },

        initPromoCopy() {
            const notification = document.getElementById('copy-notification');

            document.querySelectorAll('span.promocode').forEach(span => {
                span.style.cursor = "pointer";
                span.addEventListener('click', async function () {
                    const promoCode = this.innerText;
                    try {
                        await navigator.clipboard.writeText(promoCode);
                        notification.style.display = "block";
                        setTimeout(() => notification.style.display = "none", 3000);
                    } catch (error) {
                        alert(`Не удалось скопировать. Пожалуйста, скопируйте вручную: ${promoCode}`);
                    }
                });
            });
        },

        setupLoadingScreen() {
            const loadingScreen = document.getElementById('loadingScreen');
            const links = document.querySelectorAll('a[href]');

            if (loadingScreen) {
                links.forEach(link => {
                    link.addEventListener('click', () => {
                        loadingScreen.style.display = 'flex';
                        loadingScreen.classList.remove('fadeOut');
                    });
                });

                window.addEventListener('load', () => {
                    loadingScreen.classList.add('fadeOut');
                    loadingScreen.addEventListener('animationend', () => {
                        loadingScreen.style.display = 'none';
                    });
                });
            }
        },
        setupStyles() {
            const style = document.createElement("style");
            style.textContent = `
        @keyframes priceZoom {
          0%, 100% { transform: scale(1); }
          50% { transform: scale(1.1); }
        }
        .price-zooming {
          animation: priceZoom 0.3s ease-in-out;
        }
        .cardsSlider {
          user-select: none;
          -webkit-user-select: none;
          -moz-user-select: none;
          -ms-user-select: none;
        }
      `;
            document.head.appendChild(style);
        },

        addEventListenersForPayment() {
            const updatePage = () => {
                const paymentMethod = document.getElementById('payment_method')?.value;
                const subscriptionLength = document.getElementById('subscription_length')?.value;
                const currentUrl = new URL(window.location.href);

                if (paymentMethod) {
                    currentUrl.searchParams.set('payment_method', paymentMethod);
                }
                if (subscriptionLength) {
                    currentUrl.searchParams.set('subscription_length', subscriptionLength);
                }

                window.location.href = currentUrl.toString();
            };

            const paymentMethodSelect = document.getElementById('payment_method');
            const subscriptionLengthSelect = document.getElementById('subscription_length');

            paymentMethodSelect?.addEventListener('change', updatePage);
            subscriptionLengthSelect?.addEventListener('change', updatePage);
        },
        setupAnimatedArrow() {
            const input = document.getElementById('regular_field');
            const arrow = document.getElementById('animated-arrow');
            let timeout;
            let animationInterval;

            const startAnimation = () => {
                arrow.style.display = 'block';
                animateArrow();
                animationInterval = setInterval(animateArrow, 1500);
            };

            const animateArrow = () => {
                arrow.classList.remove('animate');
                arrow.offsetWidth;
                arrow.classList.add('animate');
            };

            const stopAnimation = () => {
                clearInterval(animationInterval);
                arrow.style.display = 'none';
                arrow.classList.remove('animate');
            };

            input?.addEventListener('input', function () {
                clearTimeout(timeout);
                stopAnimation();
                if (this.value.length > 0) {
                    timeout = setTimeout(startAnimation, 1000);
                }
            });
        },
        setupTelegramDetection() {
            const problemBtn = document.getElementById('problem');
            problemBtn?.addEventListener('click', function (e) {
                e.preventDefault();
                const url = 'https://syntx.ai/login';
                const ua = navigator.userAgent || '';

                if (/Telegram/i.test(ua)) {
                    if (/Android/i.test(ua)) {
                        const hostAndPath = url.replace(/^https?:\/\//, '');
                        const link = `intent://${hostAndPath}#Intent;scheme=https;package=com.android.chrome;end`;
                        window.open(link, '_blank');
                        return;
                    }

                    alert(
                        'Looks like you’re in Telegram’s in-app browser.\n' +
                        'Please tap the ⋮ menu (top-right) and choose “Open in Browser” to continue.'
                    );
                    return;
                }

                window.open(url, '_blank', 'noopener,noreferrer');
            });
        },
        setupStripeCheckboxValidation() {
            const payBtn = document.getElementById('pay');
            const checkbox = document.getElementById('agreeStripe');

            if (payBtn && checkbox) {
                payBtn.addEventListener('click', function (e) {
                    if (!checkbox.checked) {
                        e.preventDefault();
                        checkbox.setCustomValidity("Please agree to continue");
                        checkbox.reportValidity();
                        return false;
                    } else {
                        checkbox.setCustomValidity("");
                    }
                });

                checkbox.addEventListener('change', function () {
                    if (checkbox.checked) {
                        checkbox.setCustomValidity("");
                        checkbox.style.outline = "";
                    }
                });
            }
        }
    },
}
</script>

<template>
    <HeaderComponent />
    <section class="margin white relative">
        <a class="close-button" style="position: absolute; top: 15px; right: 15px;"
           href="https://webapp.syntxai.net?page=subscription&changingPlan=pro&PHPSESSID=ec48f339284f0424b862e2132a3c98e7"><i
            class="bi bi-x-lg"></i></a>
        <h2 class="mb_10">Тариф        <span>PRO</span></h2>
        <div class="hint">* Вы можете подарить подписку</div>
        <form method="POST" action=""><input type="hidden" name="PHPSESSID" value="ec48f339284f0424b862e2132a3c98e7" />
            <input type="hidden" name="formId" value="userToGift">
            <div class="form-group">
                <div class="label-and-select" style="position: relative;">
                    <input type="text" name="userToGiftCheck" style="margin: 0; width: 70%;"
                           placeholder="Телеграм @ ник друга" id="regular_field"/>
                    <button type="submit" class="btnSeperate" style="margin: 0 0 0 10px;">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    <div id="animated-arrow" class="arrow-animation" style="display: none;">
                        <i class="bi bi-chevron-double-down"></i>
                    </div>
                </div>
            </div>
        </form>

        <div class="select-wrapper mt_5" style="width: 100%;">
            <div class="form-group">
                <div class="label-and-select">
                    <label for="payment_method">Метод оплаты</label>
                    <p>⭐️ Telegram
                        stars</p>
                    <input id="payment_method" type="hidden" name="payment_method" value="overpay"/>
                </div>
            </div>
        </div>

        <div class="select-wrapper mt_10" style="width: 100%;">
            <div class="form-group">
                <div class="label-and-select">
                    <label for="subscription_length">Срок подписки</label>
                    <select id="subscription_length" name="subscription_length" class="select-input">
                        <option value="1" selected>1 месяц</option>
                        <option value="3" >3 месяца                        -5%
                        </option>
                        <option value="6" >6 месяцев                        -10%
                        </option>
                        <option value="12" >12 месяцев                        -15%
                        </option>
                    </select>
                    <i class="bi bi-chevron-double-down"></i>
                </div>
            </div>
        </div>


        <div class="defRow mt_10">
            <div class="leftCell">Получатель:</div>
            <div class="rightCell">
                Никита Логвинов        </div>
        </div>
        <div class="defRow">
            <div class="leftCell">Токены:</div>
            <div class="rightCell">680<i class="bi bi-lightning-charge-fill"></i></div>
        </div>
        <div class="defRow">
            <div class="leftCell">Итоговая скидка:</div>
            <div class="rightCell">0%</div>
        </div>
        <div class="defRow">
            <div class="leftCell">Итоговая цена:</div>
            <div class="rightCell">
                <div class="fullPrice">1,455.00 ⭐️</div>
            </div>
        </div>


        <!-- TG Stars -->
        <div class="payment-info button" data-duration="1" data-method="tgstars">
            <form method="post" action=""><input type="hidden" name="PHPSESSID" value="ec48f339284f0424b862e2132a3c98e7" />
                <input type="hidden" name="action" value="invoice_tgstars">
                <button style="padding: 14px;" class="cardButton mt_10">СЧЕТ(⭐️)
                </button>
            </form>
        </div>
        <div style="display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 10px;
        ">
            <a style="padding: 10px 0; background-color: #0b0b0e; color: white; font-weight: lighter; text-decoration: none;" id="problem" class="cardButton" target="_blank" href="#">Проблемы с оплатой?</a>
        </div>
    </section>

    <div class="bottom"></div>


    <div class="bottom-info">
        Время сервера: 18.06.2025 21:59 | ID: 1337592809    </div>
    <NavigationComponent />
</template>

<style scoped>

</style>