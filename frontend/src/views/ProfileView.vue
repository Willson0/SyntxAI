<script>
import CarouselComponent from "@/components/CarouselComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import HeaderComponent from "@/components/HeaderComponent.vue";
import config from "@/config.json";

export default {
    name: "ProfileView",
    components: {HeaderComponent, NavigationComponent, CarouselComponent},
    data() {
        return {
            menuClicked: false,
            config: config,
            steps: [100,280,500,750,1000,1500,2000,2500,5000],
            prices: {
                "100": {"EUR":3.6,"RUB":360,"USD":3.87,"STARS":230},
                "280": {"EUR":9.9,"RUB":990,"USD":10.63,"STARS":630},
                "500": {"EUR":14,"RUB":1400,"USD":15.04,"STARS":895},
                "750": {"EUR":18.2,"RUB":1820,"USD":19.55,"STARS":1163},
                "1000": {"EUR":23.5,"RUB":2350,"USD":25.24,"STARS":1500},
                "1500": {"EUR":35,"RUB":3500,"USD":37.59,"STARS":2236},
                "2000": {"EUR":46,"RUB":4600,"USD":49.41,"STARS":2940},
                "2500": {"EUR":54,"RUB":5400,"USD":58,"STARS":3450},
                "5000": {"EUR":99,"RUB":9900,"USD":106.34,"STARS":6325}
            }
        };
    },
    methods: {
        toggleDropdownMenu(event) {
            this.menuClicked = true;
            const dropdownMenu = document.querySelector('.dropdownMenu');
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
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
            const hash = window.location.hash.substring(1);
            const ids = hash ? hash.split(',') : [];
            if (isOpen && !ids.includes(submenuId)) {
                ids.push(submenuId);
            } else if (!isOpen && ids.includes(submenuId)) {
                ids.splice(ids.indexOf(submenuId), 1);
            }
            window.location.hash = ids.join(',');
        },
        initializeSubMenus() {
            const subMenus = document.getElementsByClassName('submenu');
            const idsToShow = window.location.hash.substring(1).split(',');
            Array.from(subMenus).forEach(subMenu => {
                const id = subMenu.id;
                const statItem = document.querySelector(`[data-submenu-id="${id}"]`);
                const icon = statItem ? statItem.querySelector('.stat-text i') : null;
                if (idsToShow.includes(id)) {
                    subMenu.classList.add('show');
                    icon?.classList.replace('bi-caret-right-fill', 'bi-caret-down-fill');
                } else {
                    subMenu.classList.remove('show');
                    icon?.classList.replace('bi-caret-down-fill', 'bi-caret-right-fill');
                }
            });
        },
        toggleSubMenu(submenuId, element) {
            const submenu = document.getElementById(submenuId);
            const icon = element.querySelector('.stat-text i');
            const isOpen = submenu.classList.contains('show');
            submenu.classList.toggle('show');
            icon?.classList.toggle('bi-caret-down-fill', !isOpen);
            icon?.classList.toggle('bi-caret-right-fill', isOpen);
            this.updateURLWithSubmenu(submenuId, !isOpen);
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
            window.location.href = `https://webapp.syntxai.net?page=partner&section=partnerbalance&priceSection=${priceSection}&PHPSESSID=744bb3911cb62050014053af234cf545`;
        },
        changeTariff(tariffId) {
            document.querySelectorAll('.tariff-block').forEach(el => el.classList.remove('active'));
            // document.getElementById(tariffId).classList.add('active');
            document.querySelectorAll('ul.pricingchoose li').forEach(el => el.classList.remove('active'));
            document.querySelector(`ul.pricingchoose li[onclick="changeTariff('${tariffId}')"]`)?.classList.add('active');
            history.pushState(null, null, '#' + tariffId);
        },
        initializeActiveTariff() {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(hash)) {
                this.changeTariff(hash);
            } else {
                this.changeTariff('basic');
            }
        },
        updateSliderValues(slider, steps, prices) {
            const outputValue = document.getElementById("value");
            const rubElements = document.querySelectorAll(".priceRUB");
            const eurElements = document.querySelectorAll(".priceEUR");
            const usdElements = document.querySelectorAll(".priceUSD");
            const starsElements = document.querySelectorAll(".priceSTARS");
            const tokenAmountElement = document.getElementById("tokenAmount");
            const stepValue = steps[slider.value];
            const price = prices[stepValue];

            outputValue.textContent = stepValue;
            rubElements.forEach(el => el.textContent = price.RUB.toFixed(2));
            eurElements.forEach(el => el.textContent = price.EUR.toFixed(2));
            usdElements.forEach(el => el.textContent = price.USD.toFixed(2));
            starsElements.forEach(el => el.textContent = price.STARS.toFixed(2));

            document.getElementById("pricePerTokenRUB").textContent = (price.RUB / stepValue).toFixed(2);
            document.getElementById("pricePerTokenEUR").textContent = (price.EUR / stepValue).toFixed(4);
            document.getElementById("pricePerTokenUSD").textContent = (price.USD / stepValue).toFixed(4);
            document.getElementById("pricePerTokenSTARS").textContent = (price.STARS / stepValue).toFixed(4);
            const discounted = price.USD * 0.95;
            document.getElementById("priceUSDDiscount").textContent = discounted.toFixed(2);
            document.getElementById("priceUSDDiscountPaykilla").textContent = discounted.toFixed(2);
            tokenAmountElement.value = stepValue;
        },
        toggleTooltip(el) {
            const tooltip = el.querySelector('.tooltip');
            const isVisible = tooltip.style.display === 'block';
            document.querySelectorAll('.tooltip').forEach(t => t.style.display = 'none');
            if (!isVisible) {
                tooltip.style.display = 'block';
                setTimeout(() => document.addEventListener('click', this.documentClickHandler), 0);
            } else {
                document.removeEventListener('click', this.documentClickHandler);
            }
        },
        documentClickHandler(e) {
            document.querySelectorAll('.tooltip').forEach(tooltip => {
                if (!tooltip.contains(e.target) && !tooltip.parentElement.contains(e.target)) {
                    tooltip.style.display = 'none';
                }
            });
            document.removeEventListener('click', this.documentClickHandler);
        }
    },
    mounted() {
        window.Telegram.WebApp.ready();
        window.Telegram.WebApp.expand();

        document.addEventListener('click', this.closeMenu);
        this.initializeSubMenus();
        this.initializeActiveTariff();

        const loadingScreen = document.getElementById('loadingScreen');
        const notification = document.getElementById('copy-notification');
        document.querySelectorAll('a[href]').forEach(link => {
            link.addEventListener('click', () => {
                loadingScreen.style.display = 'flex';
                loadingScreen.classList.remove('fadeOut');
            });
        });
        // window.addEventListener('load', () => {
        //     // loadingScreen.classList.add('fadeOut');
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
                } catch (err) {
                    console.error('Ошибка копирования:', err);
                    alert(`Не удалось скопировать. Пожалуйста, скопируйте вручную: ${promoCode}`);
                }
            });
        });

        // const slider = document.getElementById("myRange");
        // slider.min = 0;
        // slider.max = this.steps.length - 1;
        // const initialIndex = this.steps.indexOf(500);
        // slider.value = initialIndex >= 0 ? initialIndex : 0;
        // this.updateSliderValues(slider, this.steps, this.prices);
        // slider.addEventListener('input', () => this.updateSliderValues(slider, this.steps, this.prices));
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeMenu);
    },
    computed: {
        user() {
            return this.$store.state.user;
        },
    }
}
</script>

<template>
    <HeaderComponent />

    <div class="mainBlock">
        <CarouselComponent />
        <div class="rightSide">
            <div class="titleButton">
                Присоединяйтесь	        <button class="arrowButton"></button>
            </div>
            <div class="buttonSection">
                <div class="button">
                    <a target="_blank" :href="config.telegram">
                        помощь<br />поддержка<br />общение		            <div class="button-icon">
                        <span class="telegram-icon"><i class="bi bi-telegram"></i></span>
                    </div>
                    </a>
                </div>
                <div class="button">
                    <a target="_blank" :href="config.telegram_group">
                        гайды<br />инсайды<br />новости		            <div class="button-icon">
                        <i class="bi bi-substack"></i>
                    </div>
                    </a>
                </div>
                <div class="button">
                    <a target="_blank" :href="config.instagram">
                        тренды<br />лайфхаки<br />новинки		            <div class="button-icon">
                        <i class="bi bi-instagram"></i>
                    </div>
                    </a>
                </div>
            </div>
            <div class="bottomButton">
                <div class="videoBtnText" id="youtubeBtn">
                    <a target="_blank" :href="config.youtube">
                        МЫ В YOUTUBE		            <div class="button-icon">
                        <i class="bi bi-youtube"></i>
                    </div>
                    </a>
                </div>
                <div class="videoBtnText hidden" id="vkBtn">
                    <a target="_blank" :href="config.vk">
                        МЫ ВКОНТАКТЕ		            <div class="button-icon">
                        <i class="fab fa-vk"></i>
                    </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <a href="/text/models">to text</a><br></br>
    <a href="/image/settings">to image</a>
    <div class="bottom"></div>
    <NavigationComponent />

</template>

<style scoped>
    .arrow-container {
        display: none !important;
    }
</style>