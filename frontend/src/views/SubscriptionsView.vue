<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import config from "@/config.json";
import axios from "axios";

export default {
  name: "SubscriptionsView",
  components: {NavigationComponent, HeaderComponent },
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
            subscriptions: [],
        };
    },
    async mounted() {
        await axios.get(config.backend + "subscription").then((response) => {
            this.subscriptions = response.data;
        })

        this.setupStyles();
        this.cards = document.querySelectorAll(".pricing-card");
        this.setupCardsSlider();
        this.setupTooltip();

        document.addEventListener("click", this.closeMenu);
        this.initializeSlider();
        this.initializePromo();
        this.setupTokenSwitcher();

        window.onclick = function(event) {
            const modal = document.getElementById('planModal');
            if (event.target === modal) {
                this.closeModal();
            }
        }
    },
    methods: {
        toggleMenu(event) {
            const menu = document.querySelector(".dropdownMenu");
            menu.style.display = menu.style.display === "block" ? "none" : "block";
            this.menuClicked = true;
            event.stopPropagation();
        },
        closeMenu(event) {
            const menu = document.querySelector(".dropdownMenu");
            if (!this.menuClicked && !menu.contains(event.target)) {
                menu.style.display = "none";
            }
            this.menuClicked = false;
        },
        initializeSlider() {
            const slider = document.querySelector("#myRange");
            if (!slider) return;

            slider.min = 0;
            slider.max = this.steps.length - 1;
            const index = this.steps.indexOf(500);
            slider.value = index >= 0 ? index : 0;

            slider.addEventListener("input", this.updateValues);
            this.updateValues();
        },
        updateValues() {
            const slider = document.querySelector("#myRange");
            const index = +slider.value;
            const stepValue = this.steps[index];
            const currentPrices = this.prices[stepValue];

            document.querySelector("#value").textContent = stepValue;

            ["RUB", "EUR", "USD", "STARS"].forEach((curr) => {
                const elements = document.querySelectorAll(`.price${curr}`);
                elements.forEach((el) => (el.textContent = currentPrices[curr].toFixed(2)));
            });

            const perToken = {
                RUB: "pricePerTokenRUB",
                EUR: "pricePerTokenEUR",
                USD: "pricePerTokenUSD",
                STARS: "pricePerTokenSTARS"
            };

            Object.entries(perToken).forEach(([curr, id]) => {
                const el = document.querySelector(`#${id}`);
                if (el) {
                    const fixed = curr === "RUB" ? 2 : 4;
                    el.textContent = (currentPrices[curr] / stepValue).toFixed(fixed);
                }
            });

            const discountedUSD = (currentPrices.USD * 0.95).toFixed(2);
            const discountEls = [
                "#priceUSDDiscount",
                "#priceUSDDiscountPaykilla"
            ];

            discountEls.forEach((id) => {
                const el = document.querySelector(id);
                if (el) el.textContent = discountedUSD;
            });

            const tokenAmount = document.querySelector("#tokenAmount");
            if (tokenAmount) tokenAmount.value = stepValue;
        },
        initializePromo() {
            const notification = document.querySelector("#copy-notification");
            const promoCodes = document.querySelectorAll("span.promocode");

            promoCodes.forEach((el) => {
                el.style.cursor = "pointer";
                el.addEventListener("click", async () => {
                    const code = el.innerText;
                    try {
                        await navigator.clipboard.writeText(code);
                        notification.style.display = "block";
                        setTimeout(() => (notification.style.display = "none"), 3000);
                    } catch (error) {
                        alert(
                            error.name === "NotAllowedError"
                                ? `Копирование запрещено системой. Скопируйте вручную: ${code}`
                                : `Ошибка копирования: ${code}`
                        );
                    }
                });
            });
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
        setupCardsSlider() {
            const cardsSlider = document.querySelector(".cardsSlider");
            const updateCardsSlider = (animate = true) => {
                const offset = -this.currentIndex * 80 + 10;
                cardsSlider.style.transition = animate ? "transform 0.3s ease" : "none";
                cardsSlider.style.transform = `translateX(${offset}%)`;

                this.cards.forEach((card, index) => {
                    card.classList.toggle("active", index === this.currentIndex);
                });
            };

            const setSlide = (index, animate = true) => {
                this.currentIndex = Math.max(0, Math.min(this.cards.length - 1, index));
                updateCardsSlider(animate);
            };

            const startDrag = (e) => {
                if (
                    e.target.closest(".token-switcher") ||
                    ["a", "button"].includes(e.target.tagName.toLowerCase())
                ) return;
                this.startX = e.type === "mousedown" ? e.clientX : e.touches[0].clientX;
                this.isDragging = true;
                this.startIndex = this.currentIndex;
                this.startTime = Date.now();
                this.isTap = true;
                cardsSlider.style.transition = "none";
                if (e.type === "mousedown") e.preventDefault();

                clearTimeout(this.tapTimeout);
                this.tapTimeout = setTimeout(() => (this.isTap = false), 200);
            };

            const drag = (e) => {
                if (!this.isDragging) return;
                this.moveX = e.type === "mousemove" ? e.clientX : e.touches[0].clientX;
                const diff = this.moveX - this.startX;
                if (Math.abs(diff) > 10) {
                    this.isTap = false;
                    clearTimeout(this.tapTimeout);
                }
                if (!this.isTap) {
                    e.preventDefault();
                    const diffRatio = diff / (cardsSlider.offsetWidth * 0.4);
                    setSlide(this.startIndex - diffRatio, false);
                }
            };

            const endDrag = (e) => {
                if (!this.isDragging) return;
                this.isDragging = false;
                clearTimeout(this.tapTimeout);
                const timeDiff = Date.now() - this.startTime;

                if (this.isTap && timeDiff < 300) {
                    const clickedCard = e.target.closest(".pricing-card");
                    if (clickedCard) {
                        const clickedIndex = Array.from(this.cards).indexOf(clickedCard);
                        if (clickedIndex !== this.currentIndex) setSlide(clickedIndex);
                    }
                } else {
                    setSlide(Math.round(this.currentIndex));
                }
            };

            cardsSlider.addEventListener("mousedown", startDrag);
            document.addEventListener("mousemove", drag);
            document.addEventListener("mouseup", endDrag);
            cardsSlider.addEventListener("touchstart", startDrag, { passive: true });
            cardsSlider.addEventListener("touchmove", drag, { passive: false });
            document.addEventListener("touchend", endDrag);

            cardsSlider.addEventListener("click", (e) => {
                if (!this.isTap) return;
                const clickedCard = e.target.closest(".pricing-card");
                if (
                    clickedCard &&
                    !e.target.closest(".token-switcher") &&
                    !["a", "button"].includes(e.target.tagName.toLowerCase())
                ) {
                    const clickedIndex = Array.from(this.cards).indexOf(clickedCard);
                    if (clickedIndex !== this.currentIndex) setSlide(clickedIndex);
                }
            });

            updateCardsSlider();
        },
        setupTokenSwitcher () {
            const cards = document.querySelectorAll('.pricing-card');
            cards.forEach((card) => {
                const tokenSwitcher = card.querySelector('.token-switcher');
                const tokenSlider = card.querySelector('.token-slider');
                const cardPrice = card.querySelector('.cardPrice');
                const cardButton = card.querySelector('.cardButton');
                let isTokenRight = true; // Начальное положение - справа (tokens2)

                function toggleTokenSlider() {
                    isTokenRight = !isTokenRight;
                    tokenSlider.style.transition = 'transform 0.3s ease';
                    tokenSlider.style.transform = isTokenRight ? 'translateX(0)' : 'translateX(-100%)';
                    updatePrice();
                    updateButtonUrl();
                    animatePrice();
                }

                function updatePrice() {
                    if (cardPrice) {
                        const price1 = cardPrice.querySelector('.price1');
                        const price2 = cardPrice.querySelector('.price2');
                        if (price1 && price2) {
                            price1.style.display = isTokenRight ? 'none' : 'inline';
                            price2.style.display = isTokenRight ? 'inline' : 'none';
                        }
                    }
                }

                function updateButtonUrl() {
                    if (cardButton) {
                        const currentUrl = new URL(cardButton.href);
                        const tokens1 = cardButton.getAttribute('data-tokens1');
                        const tokens2 = cardButton.getAttribute('data-tokens2');
                        currentUrl.searchParams.set('tokens', isTokenRight ? tokens2 : tokens1);
                        cardButton.href = currentUrl.toString();
                    }
                }

                function animatePrice() {
                    if (cardPrice) {
                        cardPrice.classList.remove('price-zooming');
                        // Принудительный reflow
                        void cardPrice.offsetWidth;
                        cardPrice.classList.add('price-zooming');
                    }
                }

                let startX, moveX;
                let isDragging = false;
                let clickStartTime;
                let startPosition;

                function handleTokenStart(e) {
                    if (!tokenSwitcher) return;
                    e.preventDefault();
                    startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
                    isDragging = true;
                    clickStartTime = new Date().getTime();
                    tokenSlider.style.transition = 'none';
                    startPosition = isTokenRight ? 0 : -100;
                }

                function handleTokenMove(e) {
                    if (!isDragging || !tokenSwitcher) return;
                    e.preventDefault();
                    moveX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
                    const diff = moveX - startX;
                    const percentage = Math.max(-100, Math.min(0, startPosition + (diff / tokenSwitcher.offsetWidth * 100)));
                    tokenSlider.style.transform = `translateX(${percentage}%)`;
                }

                function handleTokenEnd(e) {
                    if (!isDragging || !tokenSwitcher) return;
                    isDragging = false;
                    const endX = e.type === 'mouseup' ? e.clientX : e.changedTouches[0].clientX;
                    const diff = endX - startX;
                    const clickEndTime = new Date().getTime();
                    const clickDuration = clickEndTime - clickStartTime;

                    if (Math.abs(diff) < 5 && clickDuration < 200) {
                        // Это был клик, просто переключаем состояние
                        toggleTokenSlider();
                    } else {
                        // Это был свайп, определяем направление
                        const threshold = tokenSwitcher.offsetWidth * 0.1; // 10% порог для свайпа
                        if (Math.abs(diff) > threshold) {
                            isTokenRight = diff > 0;
                        }
                        tokenSlider.style.transition = 'transform 0.3s ease';
                        tokenSlider.style.transform = isTokenRight ? 'translateX(0)' : 'translateX(-100%)';
                        updatePrice();
                        updateButtonUrl();
                        animatePrice();
                    }
                }

                if (tokenSwitcher) {
                    tokenSwitcher.addEventListener('mousedown', handleTokenStart);
                    tokenSwitcher.addEventListener('touchstart', handleTokenStart);
                    document.addEventListener('mousemove', handleTokenMove);
                    document.addEventListener('touchmove', handleTokenMove);
                    document.addEventListener('mouseup', handleTokenEnd);
                    document.addEventListener('touchend', handleTokenEnd);

                    // Устанавливаем начальное положение слайдера
                    tokenSlider.style.transform = 'translateX(0)';

                    function hintAnimation() {
                        tokenSlider.style.transition = 'transform 0.15s ease-in-out';
                        tokenSlider.style.transform = 'translateX(-20%)';

                        setTimeout(() => {
                            tokenSlider.style.transform = 'translateX(0)';
                            tokenSlider.style.transition = 'transform 0.3s ease-out';
                        }, 150);
                    }

                    setTimeout(() => {
                        hintAnimation();
                        setTimeout(hintAnimation, 300);
                    }, 2000);
                }

                // Устанавливаем начальное отображение цены и URL кнопки
                updatePrice();
                updateButtonUrl();
            });
        },
        setupTooltip() {
            const tooltipContainer = document.getElementById("tooltip-container");
            const cardInfoIcons = document.querySelectorAll(".cardInfoIcon");

            cardInfoIcons.forEach((cardInfoIcon, index) => {
                const tt = cardInfoIcon.querySelector(".tt");
                let isAutoShowComplete = false;
                let originalParent = tt.parentNode;
                let isTouchDevice = false;

                const positionTooltip = () => {
                    const iconRect = cardInfoIcon.getBoundingClientRect();
                    const padding = 10;
                    const maxWidth = Math.min(300, window.innerWidth * 0.3);
                    tt.style.maxWidth = `${maxWidth}px`;
                    const left = Math.max(
                        padding,
                        Math.min(
                            iconRect.left + (iconRect.width - tt.offsetWidth) / 2,
                            window.innerWidth - tt.offsetWidth - padding
                        )
                    );
                    tt.style.left = `${left}px`;
                    const top = iconRect.top - tt.offsetHeight - padding;
                    if (top < padding) {
                        tt.style.top = `${iconRect.bottom + padding}px`;
                    } else {
                        tt.style.top = `${top}px`;
                    }
                };

                const showTooltip = () => {
                    clearTimeout(tt.hideTimer);
                    tooltipContainer.appendChild(tt);
                    tt.classList.add("visible");
                    positionTooltip();
                };

                const hideTooltip = () => {
                    clearTimeout(tt.hideTimer);
                    tt.classList.remove("visible");
                    setTimeout(() => {
                        if (!tt.classList.contains("visible")) originalParent.appendChild(tt);
                    }, 300);
                };

                setTimeout(() => {
                    if (index === this.activeCardIndex) {
                        showTooltip();
                        tt.hideTimer = setTimeout(() => {
                            hideTooltip();
                            isAutoShowComplete = true;
                        }, 4000);
                    } else {
                        isAutoShowComplete = true;
                    }
                }, 1000);

                cardInfoIcon.addEventListener(
                    "touchstart",
                    (e) => {
                        isTouchDevice = true;
                        e.preventDefault();
                        if (isAutoShowComplete) {
                            tt.classList.contains("visible") ? hideTooltip() : showTooltip();
                        }
                    },
                    { passive: false }
                );

                cardInfoIcon.addEventListener("click", (e) => {
                    if (isTouchDevice) return;
                    e.stopPropagation();
                    if (isAutoShowComplete) {
                        tt.classList.contains("visible") ? hideTooltip() : showTooltip();
                    }
                });

                cardInfoIcon.addEventListener("mouseenter", () => {
                    if (!isTouchDevice) showTooltip();
                });

                cardInfoIcon.addEventListener("mouseleave", () => {
                    if (!isTouchDevice) hideTooltip();
                });

                document.addEventListener("click", (e) => {
                    if (!cardInfoIcon.contains(e.target) && !isTouchDevice) hideTooltip();
                });

                document.addEventListener(
                    "touchstart",
                    (e) => {
                        if (!cardInfoIcon.contains(e.target) && isTouchDevice) hideTooltip();
                    },
                    { passive: true }
                );

                window.addEventListener("resize", positionTooltip);
            });
        },
        openModal(planTitle) {
            const modal = document.getElementById('planModal');
            const modalContent = document.getElementById('modalContent');
            const planContent = document.getElementById(`modal-content-${planTitle}`);

            modalContent.innerHTML = planContent ? planContent.innerHTML : 'Информация о тарифе отсутствует.';

            modal.style.display = 'block';
        },
        closeModal() {
            const modal = document.getElementById('planModal');
            modal.style.display = 'none';
        }
    },
};
</script>

<template>
  <HeaderComponent />

  <div
    id="tooltip-container"
    style="
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      pointer-events: none;
    "
  ></div>

  <div class="cardsSlider-container">
    <div class="cardsSlider" id="cardsSlider">
      <div class="pricing-card" data-plan-id="1">
        <div class="pricingCard">
          <div class="cardInfoIcon">
            <div class="sIcon info"></div>
            <div class="tt">Токен - внутренняя валюта чат-бота</div>
          </div>
          <div class="cardTitle">FREE</div>
          <div class="token-noSwitcher">5 токенов</div>
          <ul class="cardInfoTable">
            <li>Пробные запросы почти во всех инструментах</li>
            <li>+ 5 демо-запросов в языковые модели</li>
            <li>+ 3 запроса/день в Dall-e 3</li>
            <li>+ 3 запроса/день в Stable Diffusion</li>
            <li>+ 5 запросов/день во FLUX.1</li>
            <li>+ <b>Бесплатно</b> Kling Try-On</li>
          </ul>
          <div class="cardPrice" id="cardPrice1"></div>
        </div>
      </div>
      <div class="pricing-card" data-plan-id="2">
        <div class="pricingCard">
          <div class="cardInfoIcon">
            <div class="sIcon info"></div>
            <div class="tt">Токен - внутренняя валюта чат-бота</div>
          </div>
          <div class="cardTitle">BASIC</div>
          <div class="token-switcher" id="tokenSwitcher2">
            <div class="token-slider" id="tokenSlider2"></div>
            <div class="token-text">
              <span>0 токенов</span>
              <span>260 токенов</span>
            </div>
          </div>
          <ul class="cardInfoTable">
            <li>Частичный функционал чат-бота</li>
            <li>+ 10 запросов/час в языковые модели</li>
            <li>+ 60 запросов/24ч. в языковые модели</li>
            <li>+ <b>Безлимит</b> GPT 4o-mini, Claude Haiku</li>
            <li>+ <b>Бесплатно</b> Stable Diffusion</li>
            <li>+ <b>Бесплатно</b> Kling Try-On</li>
            <li>+ <b>Бесплатно</b> Google AI Editor</li>
            <li>+ <b>Бесплатно</b> Google Imagen 4</li>
          </ul>
          <a
            class="cardMore"
            @click="openModal('basic')"
            style="text-decoration: underline"
            >Что входит в тариф?</a
          >
          <div class="cardPrice" id="cardPrice2">
            <span class="price1" style="display: none">
              790.00₽<span>/</span>7.90€<span>/</span>
              <span style="color: #dcd16c">8.01$</span>
            </span>
            <span class="price2">
              890.00₽<span>/</span>8.90€<span>/</span>
              <span style="color: #d9ff9b">8.91$</span>
            </span>
            <div class="discounts">* Скидки при покупке > 1 мес.</div>
          </div>
          <a
            class="cardButton mt_20"
            href="/shop?plan=free"
            data-tokens1="0"
            data-tokens2="260"
            >КУПИТЬ</a
          >
        </div>
      </div>
      <div class="pricing-card" data-plan-id="3">
        <div class="pricingCard">
          <div class="cardInfoIcon">
            <div class="sIcon info"></div>
            <div class="tt">Токен - внутренняя валюта чат-бота</div>
          </div>
          <div class="cardTitle">PRO</div>
          <div class="token-switcher" id="tokenSwitcher3">
            <div class="token-slider" id="tokenSlider3"></div>
            <div class="token-text">
              <span>0 токенов</span>
              <span>680 токенов</span>
            </div>
          </div>

          <ul class="cardInfoTable">
            <li><b>Полный</b> функционал чат-бота</li>
            <li>+ 25 запросов/час в языковые модели</li>
            <li>+ 150 запросов/24ч. в языковые модели</li>
            <li>+ <b>Безлимит</b> GPT 4o-mini, Claude Haiku</li>
            <li>+ <b>Бесплатно</b> Stable Diffusion</li>
            <li>+ <b>Бесплатно</b> Kling Try-On</li>
            <li>+ <b>Бесплатно</b> Google AI Editor</li>
            <li>+ <b>Бесплатно</b> Google Imagen 4</li>
          </ul>

          <a
            class="cardMore"
            @click="openModal('pro')"
            style="text-decoration: underline"
            >Что входит в тариф?</a
          >

          <div class="cardPrice" id="cardPrice3">
            <span class="price1" style="display: none">
              1490.00₽<span>/</span> 14.90€ <span>/</span>
              <span style="color: #dcd16c">15.21$</span>
            </span>
            <span class="price2">
              1690.00₽<span>/</span> 16.90€ <span>/</span>
              <span style="color: #d9ff9b">17.01$</span>
            </span>
            <div class="discounts">* Скидки при покупке > 1 мес.</div>
          </div>
          <a
            class="cardButton mt_20"
            href="/shop?plan=pro"
            data-tokens1="0"
            data-tokens2="680"
            >КУПИТЬ</a
          >
        </div>
      </div>
      <div class="pricing-card" data-plan-id="5">
        <div class="pricingCard">
          <div class="cardInfoIcon">
            <div class="sIcon info"></div>
            <div class="tt">Токен - внутренняя валюта чат-бота</div>
          </div>
          <div class="cardTitle">VIP</div>
          <div class="token-noSwitcher">1700 токенов</div>

          <ul class="cardInfoTable">
            <li><b>Полный</b> функционал чат-бота</li>
            <li>+ <b>Безлимит</b> в языковых моделях</li>
            <li>+ <b>Бесплатно</b> Dall-e 3 через GPT</li>
            <li>+ <b>Бесплатно</b> Stable Diffusion</li>
            <li>+ <b>Бесплатно</b> Kling Try-On</li>
            <li>+ <b>Бесплатно</b> Google AI Editor</li>
            <li>+ <b>Бесплатно</b> Google Imagen 4</li>
          </ul>

          <a
            class="cardMore"
            @click="openModal('vip')"
            style="text-decoration: underline"
            >Что входит в тариф?</a
          >

          <div class="cardPrice" id="cardPrice5">
            3990.00₽<span>/</span> 39.90€ <span>/</span>
            <span style="color: #d9ff9b">41.31$</span>
            <div class="discounts">* Скидки при покупке > 1 мес.</div>
          </div>
          <a
            class="cardButton mt_20"
            href="https://webapp.syntxai.net?page=subscription&section=buysubscription&plan=vip&tokens=1700&PHPSESSID=e1138c8fed5233fe7479ae2434eb3389"
            data-tokens1="0"
            data-tokens2="1700"
            >КУПИТЬ</a
          >
        </div>
      </div>
      <div class="pricing-card" data-plan-id="6">
        <div class="pricingCard">
          <div class="cardInfoIcon">
            <div class="sIcon info"></div>
            <div class="tt">Токен - внутренняя валюта чат-бота</div>
          </div>
          <div class="cardTitle">ELITE</div>
          <div class="token-switcher" id="tokenSwitcher6">
            <div class="token-slider" id="tokenSlider6"></div>
            <div class="token-text">
              <span>0 токенов</span>
              <span>2600 токенов</span>
            </div>
          </div>

          <ul class="cardInfoTable">
            <li><b>Полный</b> функционал чат-бота</li>
            <li>+ <b>Безлимит</b> в языковых моделях</li>
            <li>+ <b>Бесплатно</b> RunWay</li>
            <li>+ <b>Бесплатно</b> Sora Images (GPT Images)</li>
            <li>+ <b>Бесплатно</b> Topaz AI</li>
            <li>+ <b>Бесплатно</b> Stable Diffusion</li>
            <li>+ <b>Бесплатно</b> Kling Try-On</li>
            <li>+ <b>Бесплатно</b> Google AI Editor</li>
            <li>+ <b>Бесплатно</b> Google Imagen 4</li>
          </ul>

          <a
            class="cardMore"
            @click="openModal('elite')"
            style="text-decoration: underline"
            >Что входит в тариф?</a
          >

          <div class="cardPrice" id="cardPrice6">
            <span class="price1" style="display: none">
              4990.00₽<span>/</span> 49.90€ <span>/</span>
              <span style="color: #dcd16c">49.41$</span>
            </span>
            <span class="price2">
              5990.00₽<span>/</span> 59.90€ <span>/</span>
              <span style="color: #d9ff9b">62.01$</span>
            </span>
            <div class="discounts">* Скидки при покупке > 1 мес.</div>
          </div>
          <a
            class="cardButton mt_20"
            href="https://webapp.syntxai.net?page=subscription&section=buysubscription&plan=elite&tokens=2600&PHPSESSID=e1138c8fed5233fe7479ae2434eb3389"
            data-tokens1="0"
            data-tokens2="2600"
            >КУПИТЬ</a
          >
        </div>
      </div>
      <div class="pricing-card" data-plan-id="7">
        <div class="pricingCard">
          <div class="cardInfoIcon">
            <div class="sIcon info"></div>
            <div class="tt">Токен - внутренняя валюта чат-бота</div>
          </div>
          <div class="cardTitle">ULTRA ELITE</div>
          <div class="token-switcher" id="tokenSwitcher7">
            <div class="token-slider" id="tokenSlider7"></div>
            <div class="token-text">
              <span>0 токенов</span>
              <span>2600 токенов</span>
            </div>
          </div>

          <ul class="cardInfoTable">
            <li><b>Все что в Elite, а так же:</b></li>
            <li>+ <b>Бесплатно</b> Sora Video</li>
            <li>+ <b>Бесплатно</b> MiniMax</li>
            <li>+ <b>Бесплатно</b> Luma</li>
            <li>+ <b>Бесплатно</b> HeyGen</li>
          </ul>

          <a
            class="cardMore"
            @click="openModal('ultraelite')"
            style="text-decoration: underline"
            >Что входит в тариф?</a
          >

          <div class="cardPrice" id="cardPrice7">
            <span class="price1" style="display: none">
              9990.00₽<span>/</span> 99.00€ <span>/</span>
              <span style="color: #dcd16c">99$</span>
            </span>
            <span class="price2">
              11990.00₽<span>/</span> 119.00€ <span>/</span>
              <span style="color: #d9ff9b">118.8$</span>
            </span>
            <div class="discounts">* Скидки при покупке > 1 мес.</div>
          </div>
          <a
            class="cardButton mt_20"
            href="https://webapp.syntxai.net?page=subscription&section=buysubscription&plan=ultraelite&tokens=2600&PHPSESSID=e1138c8fed5233fe7479ae2434eb3389"
            data-tokens1="0"
            data-tokens2="2600"
            >КУПИТЬ</a
          >
        </div>
      </div>
      <div class="pricing-card" data-plan-id="8">
        <div class="pricingCard">
          <div class="cardInfoIcon">
            <div class="sIcon info"></div>
            <div class="tt">Токен - внутренняя валюта чат-бота</div>
          </div>
          <div class="cardTitle">GPT</div>
          <div class="token-noSwitcher">0 токенов</div>

          <ul class="cardInfoTable">
            <li><b>Полный</b> функционал чат-бота</li>
            <li>+ 60 запросов/час в языковые модели</li>
            <li>+ 600 запросов/24ч. в языковые модели</li>
            <li>+ <b>Безлимит</b> GPT 4o-mini, Claude Haiku</li>
            <li>+ <b>Бесплатно</b> Stable Diffusion</li>
            <li>+ <b>Бесплатно</b> Kling Try-On</li>
            <li>+ <b>Бесплатно</b> Google AI Editor</li>
            <li>+ <b>Бесплатно</b> Google Imagen 4</li>
          </ul>

          <a
            class="cardMore"
            @click="openModal('gpt')"
            style="text-decoration: underline"
            >Что входит в тариф?</a
          >

          <div class="cardPrice" id="cardPrice8">
            1990.00₽<span>/</span> 19.90€ <span>/</span>
            <span style="color: #d9ff9b">20.61$</span>
            <div class="discounts">* Скидки при покупке > 1 мес.</div>
          </div>
          <a
            class="cardButton mt_20"
            href="https://webapp.syntxai.net?page=subscription&section=buysubscription&plan=gpt&tokens=0&PHPSESSID=e1138c8fed5233fe7479ae2434eb3389"
            data-tokens1="0"
            data-tokens2="0"
            >КУПИТЬ</a
          >
        </div>
      </div>
    </div>
  </div>

  <!--
    <div class="compareall">
        <a href="https://webapp.syntxai.net?page=subscription&section=subnew_more">Сравнить все тарифы</a>
    </div>
    -->

  <!-- Модальное окно -->
  <div id="planModal" class="modal">
    <div class="modal-content">
      <div class="close-button" @click="closeModal()">
        <i class="bi bi-x-lg"></i>
      </div>
      <div id="modalContent"></div>
    </div>
  </div>

  <!-- Блоки с содержимым для каждого тарифа -->
  <div id="modalContentBlocks" style="display: none">
    <div class="modal-plan-content" id="modal-content-basic">
      <div class="title">Тариф <span>basic</span></div>
      <div class="plan-features">
        <div class="feature">
          <span> Очередь исполнения </span>
          <span>Отдельная</span>
        </div>
        <div class="feature">
          <span> Одновременных заданий </span>
          <span>2</span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Higgsfield
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> HeyGen Avatar 4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hedra </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA Images
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA
          </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Runway: Gen-4
          </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> RunWay: Gen-3 </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> RunWay: Upscale x4 </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> RunWay: Act-One (Аватары 2.0) </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> RW: Стилизатор видео </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> VEO 2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >59.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3 Fast
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >19.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >119.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Luma: Dream Machine
          </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Kling Ai
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >6.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Pika полный функционал </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hailuo MiniMax </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >8.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Topaz AI
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync RunWay) </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync Kling) </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> Google AI редактор (Gemini Flash 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney весь функционал
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney редактор
          </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> Kling Kolors </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Try-On (примерка) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Runway Frames </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 вектор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >4.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Flux обучение от Syntx
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux обучение LoRa </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.18/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0-0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 Редактор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO ULTRA </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Syntx Enhancer (Апскейлер) х2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Stable Diffusion </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Генератор звуков </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.50/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v3.5 (создание музыки) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v4.0 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >3.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синтез речи </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.0125/symb.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Аудио транскрибатор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30/min.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Создание аватаров </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >13.00/15 sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            GPT-4o Images Generation
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> GPTs Store </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT 4.5
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT o3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1 PRO
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >5.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4-o (omni) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Grok 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            DeepSeek R1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Qwen 2.5 Max </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Gemini Pro &amp; Flash </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Claude 3.7 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            GPT o3 Mini, 4.1 Mini/Nano, 4o Mini &amp; Gemini 2.5 Flash &amp;
            Claude 3.5 Haiku &amp; Perplexity Sonar
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Хранитель изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Смешивание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Описывание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Ideogram </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.90+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 Turbo </span>
          <span><i class="bi bi-x-lg" style="font-size: 15px"></i></span>
        </div>
        <div class="feature">
          <span> Дорисовка (inPaint&amp;OutPaint) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Фото Мастер </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Замена лиц на фото </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.15 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Редактор изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
      </div>
    </div>
    <div class="modal-plan-content" id="modal-content-pro">
      <div class="title">Тариф <span>pro</span></div>
      <div class="plan-features">
        <div class="feature">
          <span> Очередь исполнения </span>
          <span>Отдельная+</span>
        </div>
        <div class="feature">
          <span> Одновременных заданий </span>
          <span>3</span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Higgsfield
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> HeyGen Avatar 4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hedra </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA Images
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Runway: Gen-4
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Gen-3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Upscale x4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >5.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Act-One (Аватары 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RW: Стилизатор видео </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> VEO 2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >59.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3 Fast
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >19.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >119.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Luma: Dream Machine
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Kling Ai
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >6.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Pika полный функционал </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hailuo MiniMax </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >8.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Topaz AI
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync RunWay) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync Kling) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Google AI редактор (Gemini Flash 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney весь функционал
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney редактор
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Kolors </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Try-On (примерка) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Runway Frames </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 вектор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >4.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Flux обучение от Syntx
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux обучение LoRa </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.18/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0-0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 Редактор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO ULTRA </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Syntx Enhancer (Апскейлер) х2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Stable Diffusion </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Генератор звуков </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.50/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v3.5 (создание музыки) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v4.0 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >3.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синтез речи </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.0125/symb.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Аудио транскрибатор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30/min.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Создание аватаров </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >13.00/15 sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            GPT-4o Images Generation
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> GPTs Store </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT 4.5
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT o3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1 PRO
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >5.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4-o (omni) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Grok 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            DeepSeek R1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Qwen 2.5 Max </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Gemini Pro &amp; Flash </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Claude 3.7 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            GPT o3 Mini, 4.1 Mini/Nano, 4o Mini &amp; Gemini 2.5 Flash &amp;
            Claude 3.5 Haiku &amp; Perplexity Sonar
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Хранитель изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Смешивание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Описывание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Ideogram </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.90+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Дорисовка (inPaint&amp;OutPaint) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Фото Мастер </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Замена лиц на фото </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.15 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Редактор изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
      </div>
    </div>
    <div class="modal-plan-content" id="modal-content-vip">
      <div class="title">Тариф <span>vip</span></div>
      <div class="plan-features">
        <div class="feature">
          <span> Очередь исполнения </span>
          <span>Без очереди</span>
        </div>
        <div class="feature">
          <span> Одновременных заданий </span>
          <span>10</span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Higgsfield
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> HeyGen Avatar 4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hedra </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA Images
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Runway: Gen-4
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Gen-3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Upscale x4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >5.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Act-One (Аватары 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RW: Стилизатор видео </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> VEO 2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >59.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3 Fast
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >19.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >119.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Luma: Dream Machine
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Kling Ai
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >6.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Pika полный функционал </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hailuo MiniMax </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >8.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Topaz AI
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync RunWay) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync Kling) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Google AI редактор (Gemini Flash 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney весь функционал
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney редактор
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Kolors </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Try-On (примерка) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Runway Frames </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 вектор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >4.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Flux обучение от Syntx
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux обучение LoRa </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.18/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0-0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 Редактор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO ULTRA </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Syntx Enhancer (Апскейлер) х2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Stable Diffusion </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Генератор звуков </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.50/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v3.5 (создание музыки) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v4.0 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >3.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синтез речи </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.0125/symb.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Аудио транскрибатор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30/min.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Создание аватаров </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >13.00/15 sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            GPT-4o Images Generation
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> GPTs Store </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT 4.5
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT o3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1 PRO
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >5.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4-o (omni) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Grok 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            DeepSeek R1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Qwen 2.5 Max </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Gemini Pro &amp; Flash </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Claude 3.7 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            GPT o3 Mini, 4.1 Mini/Nano, 4o Mini &amp; Gemini 2.5 Flash &amp;
            Claude 3.5 Haiku &amp; Perplexity Sonar
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Хранитель изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Смешивание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Описывание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Ideogram </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.90+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Дорисовка (inPaint&amp;OutPaint) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Фото Мастер </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Замена лиц на фото </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.15 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Редактор изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
      </div>
    </div>
    <div class="modal-plan-content" id="modal-content-elite">
      <div class="title">Тариф <span>elite</span></div>
      <div class="plan-features">
        <div class="feature">
          <span> Очередь исполнения </span>
          <span>Без очереди</span>
        </div>
        <div class="feature">
          <span> Одновременных заданий </span>
          <span>10</span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Higgsfield
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> HeyGen Avatar 4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hedra </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA Images
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Runway: Gen-4
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Gen-3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Upscale x4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Act-One (Аватары 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RW: Стилизатор видео </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> VEO 2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >59.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3 Fast
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >19.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >119.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Luma: Dream Machine
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Kling Ai
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >6.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Pika полный функционал </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hailuo MiniMax </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >8.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Topaz AI
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync RunWay) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync Kling) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Google AI редактор (Gemini Flash 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney весь функционал
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney редактор
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Kolors </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Try-On (примерка) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Runway Frames </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 вектор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >4.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Flux обучение от Syntx
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux обучение LoRa </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.18/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0-0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 Редактор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO ULTRA </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Syntx Enhancer (Апскейлер) х2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Stable Diffusion </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Генератор звуков </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.50/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v3.5 (создание музыки) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v4.0 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >3.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синтез речи </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.0125/symb.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Аудио транскрибатор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30/min.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Создание аватаров </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >13.00/15 sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            GPT-4o Images Generation
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> GPTs Store </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT 4.5
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT o3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1 PRO
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >5.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4-o (omni) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Grok 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            DeepSeek R1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Qwen 2.5 Max </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Gemini Pro &amp; Flash </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Claude 3.7 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            GPT o3 Mini, 4.1 Mini/Nano, 4o Mini &amp; Gemini 2.5 Flash &amp;
            Claude 3.5 Haiku &amp; Perplexity Sonar
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Хранитель изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Смешивание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Описывание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Ideogram </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.90+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Дорисовка (inPaint&amp;OutPaint) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Фото Мастер </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Замена лиц на фото </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.15 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Редактор изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
      </div>
    </div>
    <div class="modal-plan-content" id="modal-content-ultraelite">
      <div class="title">Тариф <span>ultraelite</span></div>
      <div class="plan-features">
        <div class="feature">
          <span> Очередь исполнения </span>
          <span>Без очереди</span>
        </div>
        <div class="feature">
          <span> Одновременных заданий </span>
          <span>10</span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Higgsfield
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> HeyGen Avatar 4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hedra </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA Images
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Runway: Gen-4
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Gen-3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Upscale x4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Act-One (Аватары 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RW: Стилизатор видео </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> VEO 2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >59.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3 Fast
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >19.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >119.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Luma: Dream Machine
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Kling Ai
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >6.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Pika полный функционал </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hailuo MiniMax </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Topaz AI
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync RunWay) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync Kling) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Google AI редактор (Gemini Flash 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney весь функционал
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney редактор
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Kolors </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Try-On (примерка) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Runway Frames </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 вектор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >4.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Flux обучение от Syntx
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux обучение LoRa </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.18/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0-0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 Редактор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO ULTRA </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Syntx Enhancer (Апскейлер) х2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Stable Diffusion </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Генератор звуков </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.50/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v3.5 (создание музыки) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v4.0 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >3.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синтез речи </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.0125/symb.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Аудио транскрибатор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30/min.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Создание аватаров </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >13.00/15 sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            GPT-4o Images Generation
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> GPTs Store </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT 4.5
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT o3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1 PRO
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >5.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4-o (omni) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Grok 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            DeepSeek R1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Qwen 2.5 Max </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Gemini Pro &amp; Flash </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Claude 3.7 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            GPT o3 Mini, 4.1 Mini/Nano, 4o Mini &amp; Gemini 2.5 Flash &amp;
            Claude 3.5 Haiku &amp; Perplexity Sonar
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Хранитель изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Смешивание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Описывание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Ideogram </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.90+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Дорисовка (inPaint&amp;OutPaint) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Фото Мастер </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Замена лиц на фото </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.15 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Редактор изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
      </div>
    </div>
    <div class="modal-plan-content" id="modal-content-gpt">
      <div class="title">Тариф <span>gpt</span></div>
      <div class="plan-features">
        <div class="feature">
          <span> Очередь исполнения </span>
          <span>Отдельная+</span>
        </div>
        <div class="feature">
          <span> Одновременных заданий </span>
          <span>5</span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Higgsfield
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> HeyGen Avatar 4 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hedra </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA Images
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            SORA
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Runway: Gen-4
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Gen-3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Upscale x4 </span>
          <span>
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RunWay: Act-One (Аватары 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> RW: Стилизатор видео </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> VEO 2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >59.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3 Fast
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >19.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            VEO 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >119.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Luma: Dream Machine
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >14.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Kling Ai
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >6.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Pika полный функционал </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >12.00+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Hailuo MiniMax </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >8.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Topaz AI
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync RunWay) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.70/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синхронизатор губ (Lipsync Kling) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.20/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Google AI редактор (Gemini Flash 2.0) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney весь функционал
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            MidJourney редактор
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Kolors </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Kling Try-On (примерка) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Runway Frames </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Recraft v3 вектор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >4.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Flux обучение от Syntx
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux обучение LoRa </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.18/step
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0-0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 Редактор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.80 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Flux.1.1 PRO ULTRA </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Syntx Enhancer (Апскейлер) х2 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Stable Diffusion </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Генератор звуков </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.50/sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v3.5 (создание музыки) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >2.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> SUNO v4.0 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >3.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Синтез речи </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.0125/symb.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Аудио транскрибатор </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30/min.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Создание аватаров </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >13.00/15 sec.
            <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            GPT-4o Images Generation
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> GPTs Store </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT 4.5
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT o3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1 PRO
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >5.50 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            ChatGPT o1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4-o (omni) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> ChatGPT 4 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            Grok 3
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            <i class="bi bi-star-fill"></i>
            DeepSeek R1
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Qwen 2.5 Max </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Gemini Pro &amp; Flash </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Claude 3.7 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span>
            GPT o3 Mini, 4.1 Mini/Nano, 4o Mini &amp; Gemini 2.5 Flash &amp;
            Claude 3.5 Haiku &amp; Perplexity Sonar
          </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-infinity purple" style="font-size: 20px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Хранитель изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Смешивание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Описывание картинок </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.00 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Ideogram </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.90+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.30 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Dall-e 3 Turbo </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >1.50+ <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Дорисовка (inPaint&amp;OutPaint) </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Фото Мастер </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.40 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Замена лиц на фото </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.15 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
        <div class="feature">
          <span> Редактор изображений </span>
          <span
            ><i class="bi bi-lightning-charge-fill" style="font-size: 10px"></i
            >0.10 <i class="bi bi-check2 purple" style="font-size: 23px"></i
          ></span>
        </div>
      </div>
    </div>
  </div>

  <div class="bottom"></div>
  <NavigationComponent />
</template>

<style scoped></style>
