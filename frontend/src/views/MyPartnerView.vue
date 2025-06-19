<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import PartnerNavigationComponent from "@/components/PartnerNavigationComponent.vue";

export default {
    name: "MyPartnerView",
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
                "5000": { EUR: 99, RUB: 9900, USD: 106.34, STARS: 6325 }
            }
        };
    },
    mounted() {
        document.addEventListener("click", this.closeMenu);
        this.initializeSubMenus();
        this.initializeActiveTariff();
        this.setupSlider();
        this.setupCopyPromocode();
        this.setupLoadingScreen();
    },
    beforeDestroy() {
        document.removeEventListener("click", this.closeMenu);
    },
    methods: {
        toggleMenu(event) {
            const dropdownMenu = document.querySelector(".dropdownMenu");
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
            this.menuClicked = true;
            event.stopPropagation();
        },
        closeMenu(event) {
            const dropdownMenu = document.querySelector(".dropdownMenu");
            if (!this.menuClicked && !dropdownMenu.contains(event.target)) {
                dropdownMenu.style.display = "none";
            }
            this.menuClicked = false;
        },
        updateURLWithSubmenu(submenuId, isOpen) {
            let hash = window.location.hash.substring(1);
            let ids = hash ? hash.split(",") : [];
            if (isOpen) {
                if (!ids.includes(submenuId)) ids.push(submenuId);
            } else {
                ids = ids.filter(id => id !== submenuId);
            }
            window.location.hash = ids.join(",");
        },
        initializeSubMenus() {
            const subMenus = document.getElementsByClassName("submenu");
            const idsToShow = window.location.hash.substring(1).split(",");
            Array.from(subMenus).forEach(subMenu => {
                const id = subMenu.id;
                const statItem = document.querySelector(`[data-submenu-id="${id}"]`);
                const icon = statItem?.querySelector(".stat-text i");
                if (idsToShow.includes(id)) {
                    subMenu.classList.add("show");
                    icon?.classList.replace("bi-caret-right-fill", "bi-caret-down-fill");
                } else {
                    subMenu.classList.remove("show");
                    icon?.classList.replace("bi-caret-down-fill", "bi-caret-right-fill");
                }
            });
        },
        toggleSubMenu(submenuId, element) {
            const submenu = document.getElementById(submenuId);
            const icon = element.querySelector(".stat-text i");
            const isOpen = submenu.classList.contains("show");
            submenu.classList.toggle("show");
            icon.classList.toggle("bi-caret-down-fill", !isOpen);
            icon.classList.toggle("bi-caret-right-fill", isOpen);
            this.updateURLWithSubmenu(submenuId, !isOpen);
        },
        changePage(timegap) {
            const baseUrl = "https://webapp.syntxai.net";
            window.location.href = `${baseUrl}?page=account&timegap=${timegap}${window.location.hash}`;
        },
        changeReferralsPage(timegap) {
            const baseUrl = "https://webapp.syntxai.net";
            window.location.href = `${baseUrl}?page=partner&section=referrals&timegap=${timegap}${window.location.hash}`;
        },
        changePricingPage(section) {
            const baseUrl = "https://webapp.syntxai.net";
            window.location.href = `${baseUrl}?page=subscription&section=pricing&priceSection=${section}`;
        },
        changeReferralBalancePage(section) {
            const baseUrl = "https://webapp.syntxai.net";
            window.location.href = `${baseUrl}?page=partner&section=partnerbalance&priceSection=${section}&PHPSESSID=f6e922d0bb8512def10eb1ddcc788cd0`;
        },
        changeTariff(tariffId) {
            const tariffs = document.getElementsByClassName("tariff-block");
            Array.from(tariffs).forEach(t => t.classList.remove("active"));
            document.getElementById(tariffId).classList.add("active");
            const tariffButtons = document.querySelectorAll("ul.pricingchoose li");
            tariffButtons.forEach(btn => btn.classList.remove("active"));
            document.querySelector(`ul.pricingchoose li[onclick="changeTariff('${tariffId}')"]`)?.classList.add("active");
            history.pushState(null, null, `#${tariffId}`);
        },
        initializeActiveTariff() {
            const hash = window.location.hash.replace("#", "") || "basic";
            if (document.getElementById(hash)) this.changeTariff(hash);
            else this.changeTariff("basic");
        },
        setupSlider() {
            const slider = document.getElementById("myRange");
            const outputValue = document.getElementById("value");
            const rub = document.querySelectorAll(".priceRUB");
            const eur = document.querySelectorAll(".priceEUR");
            const usd = document.querySelectorAll(".priceUSD");
            const stars = document.querySelectorAll(".priceSTARS");
            const tokenAmount = document.getElementById("tokenAmount");
            const priceUsdDiscount = document.getElementById("priceUSDDiscount");
            const priceUsdDiscountPaykilla = document.getElementById("priceUSDDiscountPaykilla");
            slider.min = 0;
            slider.max = this.steps.length - 1;
            slider.value = this.steps.indexOf(500);

            const updateValues = () => {
                const stepValue = this.steps[slider.value];
                const prices = this.prices[stepValue];
                if (outputValue) outputValue.textContent = stepValue;
                rub.forEach(el => el.textContent = prices.RUB.toFixed(2));
                eur.forEach(el => el.textContent = prices.EUR.toFixed(2));
                usd.forEach(el => el.textContent = prices.USD.toFixed(2));
                stars.forEach(el => el.textContent = prices.STARS.toFixed(2));

                const elRUB = document.getElementById("pricePerTokenRUB");
                if (elRUB) elRUB.textContent = (prices.RUB / stepValue).toFixed(2);

                const elEUR = document.getElementById("pricePerTokenEUR");
                if (elEUR) elEUR.textContent = (prices.EUR / stepValue).toFixed(4);

                const elUSD = document.getElementById("pricePerTokenUSD");
                if (elUSD) elUSD.textContent = (prices.USD / stepValue).toFixed(4);

                const elSTARS = document.getElementById("pricePerTokenSTARS");
                if (elSTARS) elSTARS.textContent = (prices.STARS / stepValue).toFixed(4);

                const discount = prices.USD * 0.95;
                if (priceUsdDiscount) priceUsdDiscount.textContent = discount.toFixed(2);
                if (priceUsdDiscountPaykilla) priceUsdDiscountPaykilla.textContent = discount.toFixed(2);
                if (tokenAmount) tokenAmount.value = stepValue;
            };

            slider.addEventListener("input", updateValues);
            updateValues();
        },
        setupCopyPromocode() {
            const notification = document.getElementById("copy-notification");
            document.querySelectorAll("span.promocode").forEach(span => {
                span.style.cursor = "pointer";
                span.addEventListener("click", async () => {
                    const code = span.innerText;
                    try {
                        await navigator.clipboard.writeText(code);
                        notification.style.display = "block";
                        setTimeout(() => notification.style.display = "none", 3000);
                    } catch (err) {
                        alert(`Не удалось скопировать. Пожалуйста, вручную: ${code}`);
                    }
                });
            });
        },
        setupLoadingScreen() {
            const loadingScreen = document.getElementById("loadingScreen");
            const links = document.querySelectorAll("a[href]");
            links.forEach(link => {
                link.addEventListener("click", () => {
                    loadingScreen.style.display = "flex";
                    loadingScreen.classList.remove("fadeOut");
                });
            });
            window.addEventListener("load", () => {
                loadingScreen.classList.add("fadeOut");
                loadingScreen.addEventListener("animationend", () => {
                    loadingScreen.style.display = "none";
                });
            });
        },
        toggleTooltip(el) {
            const tooltip = el.querySelector(".tooltip");
            const isVisible = tooltip.style.display === "block";
            document.querySelectorAll(".tooltip").forEach(t => t.style.display = "none");
            if (!isVisible) {
                tooltip.style.display = "block";
                setTimeout(() => document.addEventListener("click", this.documentClickHandler), 0);
            } else {
                document.removeEventListener("click", this.documentClickHandler);
            }
        },
        documentClickHandler(event) {
            document.querySelectorAll(".tooltip").forEach(tooltip => {
                if (!tooltip.contains(event.target) && !tooltip.parentElement.contains(event.target)) {
                    tooltip.style.display = "none";
                }
            });
            document.removeEventListener("click", this.documentClickHandler);
        }
    }
}
</script>

<template>
    <HeaderComponent />

    <div style="width: 100%; text-align: center; font-weight: 800; font-size: 15px;">
        PARTNER</div>

    <PartnerNavigationComponent />

    <section class="box margin">
        <div class="statistics">
        </div>
    </section>

    <section class="box margin white">
        <div class="time-filters">
            <a href="https://webapp.syntxai.net?page=partner&section=referrals&timegap=new&PHPSESSID=f6e922d0bb8512def10eb1ddcc788cd0" class="active">Полный список</a>
            <a href="https://webapp.syntxai.net?page=partner&section=referrals&timegap=active&PHPSESSID=f6e922d0bb8512def10eb1ddcc788cd0" >Самые активные</a>
        </div>
        <div class="statistics">
            <div class="stat-item">
                <div>
                    <span class="stat-text" style="display: block; padding: 35px; width: 100%; text-align: center;">Рефералы отсутствуют.</span>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="pagination">
            </div>

        </div>
    </section>

    <div class="bottom"></div>

    <div class="bottom-info">
        Время сервера: 15.06.2025 22:55 | ID: 1337592809    </div>
    <NavigationComponent />
</template>

<style scoped>

</style>