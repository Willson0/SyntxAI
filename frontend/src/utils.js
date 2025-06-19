export function updateURLWithSubmenu(submenuId, isOpen) {
    var hash = window.location.hash.substring(1);
    var ids = hash ? hash.split(',') : [];

    if (isOpen) {
        if (!ids.includes(submenuId)) {
            ids.push(submenuId);
        }
    } else {
        var index = ids.indexOf(submenuId);
        if (index !== -1) {
            ids.splice(index, 1);
        }
    }

    window.location.hash = ids.join(',');
}

export function initializeSubMenus() {
    var subMenus = document.getElementsByClassName('submenu');
    var idsToShow = window.location.hash.substring(1).split(',');

    for (var i = 0; i < subMenus.length; i++) {
        var id = subMenus[i].id;
        var statItem = document.querySelector('[data-submenu-id="' + id + '"]');
        var icon = statItem ? statItem.querySelector('.stat-text i') : null;

        if (idsToShow.includes(id)) {
            subMenus[i].classList.add('show');
            if (icon) {
                icon.classList.remove('bi-caret-right-fill');
                icon.classList.add('bi-caret-down-fill');
            }
        } else {
            subMenus[i].classList.remove('show');
            if (icon) {
                icon.classList.remove('bi-caret-down-fill');
                icon.classList.add('bi-caret-right-fill');
            }
        }
    }
}

export function toggleSubMenu(submenuId, element) {
    var submenu = document.getElementById(submenuId);
    var icon = element.querySelector('.stat-text i');

    if (submenu.classList.contains('show')) {
        submenu.classList.remove('show');
        icon.classList.remove('bi-caret-down-fill');
        icon.classList.add('bi-caret-right-fill');
        updateURLWithSubmenu(submenuId, false);
    } else {
        submenu.classList.add('show');
        icon.classList.remove('bi-caret-right-fill');
        icon.classList.add('bi-caret-down-fill');
        updateURLWithSubmenu(submenuId, true);
    }
}

export function changePage(timegap) {
    var currentUrl = window.location.href;
    var hash = window.location.hash;
    var baseUrl = 'https://webapp.syntxai.net'; // Убедитесь, что это верный URL

    var newUrl = baseUrl + '?page=account&timegap=' + timegap + hash;
    window.location.href = newUrl;
}

export function changeReferralsPage(timegap) {
    var currentUrl = window.location.href;
    var hash = window.location.hash;
    var baseUrl = 'https://webapp.syntxai.net'; // Убедитесь, что это верный URL

    var newUrl = baseUrl + '?page=partner&section=referrals&timegap=' + timegap + hash;
    window.location.href = newUrl;
}

export function changePricingPage(priceSection) {
    var currentUrl = window.location.href;
    var hash = window.location.hash;
    var baseUrl = 'https://webapp.syntxai.net'; // Убедитесь, что это верный URL

    var newUrl = baseUrl + '?page=subscription&section=pricing&priceSection=' + priceSection;
    window.location.href = newUrl;
}


/*
    export function changeTransactionsPage(priceSection) {
        var currentUrl = window.location.href;
        var hash = window.location.hash;
        var baseUrl = 'https://webapp.syntxai.net'; // Убедитесь, что это верный URL

        var newUrl = baseUrl + '?page=subscription&section=transactions&priceSection=' + priceSection;
        window.location.href = newUrl;
    }
*/

export function changeReferralBalancePage(priceSection) {
    var currentUrl = window.location.href;
    var hash = window.location.hash;
    var baseUrl = 'https://webapp.syntxai.net'; // Убедитесь, что это верный URL

    var newUrl = baseUrl + '?page=partner&section=partnerbalance&priceSection=' + priceSection + '&PHPSESSID=ec48f339284f0424b862e2132a3c98e7';
    window.location.href = newUrl;
}

document.addEventListener("DOMContentLoaded", function() {
    const loadingScreen = document.getElementById('loadingScreen');
    console.log('init ',loadingScreen)
    const links = document.querySelectorAll('a[href]');
    const notification = document.getElementById('copy-notification');
    const subMenus = document.getElementsByClassName('submenu');
    const hashValues = window.location.hash.substring(1).split(',');
    links.forEach(link => {
        link.addEventListener('click', () => {
            loadingScreen.style.display = 'flex';
            loadingScreen.classList.remove('fadeOut');
        });
    });

    window.addEventListener('load', () => {
        console.log('loading screen ', loadingScreen)
        loadingScreen.classList.add('fadeOut');
        loadingScreen.addEventListener('animationend', () => {
            loadingScreen.style.display = 'none';
        });
    });

    document.querySelectorAll('span.promocode').forEach(span => {
        span.style.cursor = "pointer";
        span.addEventListener('click', async function() {
            const promoCode = this.innerText;
            try {
                await navigator.clipboard.writeText(promoCode);
                notification.style.display = "block";
                setTimeout(() => notification.style.display = "none", 3000);
            } catch (error) {
                console.error('Ошибка копирования:', error);
                if (error.name === 'NotAllowedError') {
                    alert('Копирование в буфер обмена запрещено Вашей системой. Пожалуйста, скопируйте код вручную: ' + promoCode);
                } else {
                    alert('Не удалось скопировать. Пожалуйста, скопируйте его вручную: ' + promoCode);
                }
            }
        });
    });

    initializeSubMenus(hashValues, subMenus);
});



/////////////////////////////////////
///// Выбор тарифа для подписки /////
/////////////////////////////////////

export function changeTariff(tariffId) {
    var tariffs = document.getElementsByClassName('tariff-block');
    for (var i = 0; i < tariffs.length; i++) {
        tariffs[i].classList.remove('active');
    }
    document.getElementById(tariffId).classList.add('active');

    // Установите класс "active" на кнопке соответствующего тарифа
    var tariffButtons = document.querySelectorAll('ul.pricingchoose li');
    for (var j = 0; j < tariffButtons.length; j++) {
        tariffButtons[j].classList.remove('active');
    }
    document.querySelector('ul.pricingchoose li[onclick="changeTariff(\'' + tariffId + '\')"]').classList.add('active');

    // Добавьте хэш-фрагмент к адресной строке без скроллинга
    history.pushState(null, null, '#' + tariffId);
}

// Обработка хэш-фрагмента при загрузке страницы
window.addEventListener('load', function () {
    var hash = window.location.hash.substr(1);
    if (hash) {
        // Вызываем функцию changeTariff для активации нужного тарифа
        changeTariff(hash);

        // Возвращаемся к верху страницы с небольшой задержкой
        setTimeout(function() {
            window.scrollTo(0, 0);
        }, 1); // Минимальная задержка для прокрутки обратно
    }
});

// Функция инициализации активного тарифа при загрузке страницы
export function initializeActiveTariff() {
    var hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) {
        changeTariff(hash);
    } else {
        changeTariff('basic'); // Установка 'basic' как стандартного активного тарифа
    }
}

document.addEventListener('DOMContentLoaded', initializeActiveTariff);

/////////////////////////////////////
///////////// ToolTip ///////////////
/////////////////////////////////////

// Функция для переключения видимости тултипа
export function toggleTooltip(element) {
    var tooltip = element.querySelector('.tooltip');
    var isTooltipVisible = tooltip.style.display === "block";

    // Сначала закрываем все тултипы
    document.querySelectorAll('.tooltip').forEach(function(el) {
        el.style.display = "none";
    });

    // Затем открываем нужный тултип, если он был закрыт
    if (!isTooltipVisible) {
        tooltip.style.display = "block";
        setTimeout(() => document.addEventListener('click', documentClickHandler), 0);
    } else {
        document.removeEventListener('click', documentClickHandler);
    }
}

export function documentClickHandler(event) {
    document.querySelectorAll('.tooltip').forEach(function(tooltip) {
        if (!tooltip.contains(event.target) && !tooltip.parentElement.contains(event.target)) {
            tooltip.style.display = "none";
        }
    });

    document.removeEventListener('click', documentClickHandler);
}