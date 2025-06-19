<script>
export default {
    name: "TextChatView",
    created () {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/src/assets/TextModels.css';
        link.id = 'component-styles'; // Чтобы потом удалить
        document.head.appendChild(link);
    },
    beforeUnmount() {
        // Удаляем стили при уничтожении компонента
        const link = document.getElementById('component-styles');
        if (link) link.remove();
    },
    mounted () {
        document.body.style.fontFamily = "ui-sans-serif, -apple-system, system-ui, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, Helvetica, Apple Color Emoji, Arial, Segoe UI Emoji, Segoe UI Symbol"
        document.body.style.minHeight = "unset";

        // Обработчик для кнопки "Скопировать всё"
        document.getElementById('copyAllButton').addEventListener('click', () => {
            const messages = document.querySelectorAll('.message');
            let fullDialog = '';

            messages.forEach((message) => {
                const content = message.querySelector('.content');
                const isUserMessage = message.classList.contains('userquestion');
                let messageContent = this.cleanContent(content, isUserMessage);

                if (isUserMessage) {
                    fullDialog += 'User: ' + messageContent + '\n\n';
                } else {
                    fullDialog += 'Assistant: ' + messageContent + '\n\n';
                }
            });

            // Копируем текст
            if (this.copyTextToClipboard(fullDialog.trim())) {
                const button = document.getElementById('copyAllButton');
                button.innerHTML = '<i class="bi bi-check2"></i> Скопировано!';
                setTimeout(() => {
                    button.innerHTML = '<i class="bi bi-clipboard"></i> ';
                }, 2000);
            }
        });


        document.querySelectorAll('.copy-button').forEach((button) => {
            button.addEventListener('click', () => {
                const language = button.getAttribute('data-language');
                const codeBlock = button.closest('.code-header').nextElementSibling.querySelector('code.language-' + language);
                const code = codeBlock.innerText;
                navigator.clipboard.writeText(code).then(() => {
                    button.innerHTML = '<i class="bi bi-check2"></i> Скопировано!';
                    setTimeout(() => {
                        button.innerHTML = '<i class="bi bi-copy"></i> Скопировать';
                    }, 2000);
                });
            });
        });

        document.querySelectorAll('.toggle-button').forEach((button) => {
            button.addEventListener('click', () => {
                const codeBlock = button.closest('.code-header').nextElementSibling;
                if (codeBlock.style.display === 'none' || codeBlock.style.display === '') {
                    codeBlock.style.display = 'block';
                    button.innerHTML = '<i class="bi bi-arrows-angle-contract"></i> Закрыть блок';
                } else {
                    codeBlock.style.display = 'none';
                    button.innerHTML = '<i class="bi bi-arrows-angle-expand"></i> Открыть блок';
                }
            });
        });
    },
    methods: {
        copyText(event) {
            event.preventDefault(); // Предотвращает переход по ссылке
            const textToCopy = "https://LLMchat.syntxai.net/d06d0694-4823-c493-c2c83e98";
            navigator.clipboard.writeText(textToCopy).then(() => {
                const messageBox = document.getElementById('error_message');
                messageBox.style.display = 'block';
                setTimeout(() => {
                    messageBox.style.display = 'none';
                }, 3000);
            }).catch(err => {
                console.error("Ошибка копирования текста: ", err);
            });
        },
        copyMessage (button, type) {
            const contentElement = button.closest('.content');
            let messageContent = this.cleanContent(contentElement, type === 'user');

            if (this.copyTextToClipboard(messageContent)) {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check2"></i> Скопировано!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
            }
        },
        async copyTextToClipboard(text) {
            let successful = false;

            // Проверяем поддержку современного API буфера обмена
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    await navigator.clipboard.writeText(text);
                    successful = true;
                } catch (err) {
                    console.error('Failed to copy text: ', err);
                }
            } else {
                // Fallback для устаревших браузеров
                const tempTextArea = document.createElement('textarea');
                tempTextArea.value = text;

                // Делаем элемент невидимым
                tempTextArea.style.position = 'absolute';
                tempTextArea.style.left = '-9999px';
                tempTextArea.style.top = '0';
                tempTextArea.style.opacity = '0';

                document.body.appendChild(tempTextArea);
                tempTextArea.focus();
                tempTextArea.select();

                try {
                    successful = document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                }

                document.body.removeChild(tempTextArea);
            }

            if (successful) {
                const messageBox = document.getElementById('error_message');
                messageBox.style.display = 'block';
                setTimeout(() => {
                    messageBox.style.display = 'none';
                }, 3000);
            }

            return successful;
        },
        cleanContent(element, isUserMessage = false) {
            const clone = element.cloneNode(true);
            clone.querySelectorAll('.code-header, .copy-button, .toggle-button, .copy-message-button').forEach(el => el.remove());
            clone.querySelectorAll('div[style*="opacity: 0.7; font-size: 11px;"]').forEach(el => el.remove());

            let content = clone.innerText || clone.textContent;
            content = content.replace(/\n{3,}/g, '\n\n');
            content = content.split('\n').map(line => line.trim()).join('\n');
            content = content.trim();
            if (isUserMessage) {
                content = content.replace(/\s+/g, ' ').trim();
            }

            return content;
        }
    }
}
</script>

<template>

    <div class="header">
        <a href="/text/dialogs"><i class="bi bi-arrow-return-left"></i> Вернуться</a>
    </div>

    <div id="error_message"><i class="bi bi-clipboard2-check-fill"></i> Ссылка скопирована.</div>

    <section class="container mt-3">
        <div class="markdown-content">
            <div class="message userquestion">
                <div class="content">
                    <p>Привет!</p>                    <button class="copy-message-button" @click="copyMessage($event.target, 'user')">
                    <i class="bi bi-clipboard"></i> Скопировать                    </button>
                </div>
            </div>
            <div class="message answer">
                <div class="logo"><img src="/syntxwhite.png" alt="Logo"></div>
                <div class="content">
                    <p>Привет! Как я могу помочь тебе сегодня?</p>                    <button class="copy-message-button" @click="copyMessage($event.target, 'assistant')">
                    <i class="bi bi-clipboard"></i> Скопировать                    </button>
                    <div style="opacity: 0.7; font-size: 11px;"><i class="bi bi-robot" style="margin-right: 4px;"></i>
                        GPT-o1 Mini                </div>
                    <div style="opacity: 0.7; font-size: 11px;"><i class="bi bi-clock-history" style="margin-right: 4px;"></i>18.06.2025 22:56:58 GMT+3</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Добавьте кнопку "Скопировать весь диалог" -->
    <button id="copyAllButton"><i class="bi bi-clipboard"></i> </button>


    <section class="container footer">
        <svg style="margin-top: -2px;"
             width="15px"
             height="15px"
             viewBox="60 105 75 70"
             version="1.1"
             id="svg1"
             xml:space="preserve"
             xmlns="http://www.w3.org/2000/svg"
             xmlns:svg="http://www.w3.org/2000/svg"><defs
         id="defs1" /><g
            id="layer1"><path
           class="loading-logo"
           d="m 94.326665,180.21418 c -0.119916,-0.19403 -0.04991,-0.35278 0.155569,-0.35278 0.205478,0 0.373597,0.15875 0.373597,0.35278 0,0.19403 -0.07001,0.35278 -0.155569,0.35278 -0.08556,0 -0.253681,-0.15875 -0.373597,-0.35278 z m -15.434028,-4.8537 c -4.819567,-0.0834 -5.909027,-0.19455 -5.909027,-0.60288 0,-0.27535 0.352823,-0.74776 0.784051,-1.0498 1.219939,-0.85448 0.104717,-0.68617 -3.451902,0.52096 -1.992242,0.67618 -3.687547,1.03333 -4.605049,0.97014 l -1.452067,-0.1 3.547188,-3.0868 3.547188,-3.08681 9.722934,-0.0847 c 12.092895,-0.10536 15.177229,0.006 16.072934,0.58195 0.411038,0.26416 2.399034,0.53767 4.762503,0.65524 4.25394,0.21161 5.28433,0.42343 5.30288,1.09014 0.006,0.22177 0.23328,0.69631 0.50468,1.05453 0.46157,0.6092 0.53956,0.56095 1.20664,-0.74665 0.39225,-0.76887 1.1374,-1.90209 1.65589,-2.51828 1.15473,-1.37232 0.92789,-1.70991 -0.62241,-0.92629 -1.07538,0.54356 -5.90604,1.3266 -6.2615,1.01497 -0.0847,-0.0743 2.15715,-2.47346 4.98199,-5.33146 4.69564,-4.75075 5.17593,-5.3552 5.60065,-7.04846 0.25551,-1.01864 0.6142,-1.85208 0.7971,-1.85208 0.60647,0 -0.0282,-2.7797 -0.87167,-3.8177 -2.08259,-2.56288 -12.49649,-13.48505 -13.01418,-13.64936 -0.31878,-0.10118 -0.92727,-0.55404 -1.352212,-1.00637 -0.6494,-0.69126 -0.881845,-1.71392 -1.457451,-6.4122 -0.376655,-3.07437 -0.610227,-5.66437 -0.519048,-5.75555 0.09118,-0.0912 6.037301,5.7736 13.213611,13.03284 13.86784,14.0281 14.05345,14.21818 16.92369,17.33077 l 1.85856,2.01548 -1.34926,1.27252 c -0.74209,0.69988 -1.51365,1.38041 -1.71458,1.51229 -0.20093,0.13188 -0.53681,0.55728 -0.7464,0.94534 -0.20959,0.38805 -1.08272,1.07195 -1.94028,1.51977 -0.96631,0.50461 -2.01184,1.47716 -2.74955,2.55764 -0.65468,0.95888 -1.41132,1.74342 -1.6814,1.74342 -0.27008,0 -0.75277,0.54883 -1.07265,1.21961 -0.31988,0.67079 -0.70443,1.1437 -0.85456,1.05092 -0.15012,-0.0928 -0.40178,0.0448 -0.55922,0.30578 -0.15745,0.26097 -0.79597,0.84518 -1.41894,1.29824 -0.62297,0.45307 -1.05855,0.94371 -0.96795,1.0903 0.0906,0.1466 -0.79964,1.12559 -1.97833,2.17553 l -2.14306,1.90898 -5.55268,0.0682 -5.552685,0.0682 -2.120554,-1.91899 c -1.560595,-1.41226 -2.253813,-1.83281 -2.6252,-1.59262 -0.399243,0.2582 -0.136232,0.65669 1.259243,1.90788 l 1.763888,1.58152 -5.997222,-0.0322 c -3.298472,-0.0177 -6.235347,0.0264 -6.526388,0.0981 -0.291042,0.0717 -3.188229,0.0843 -6.438195,0.0281 z m 38.893753,-5.15727 c 0,-0.0733 0.27781,-0.35107 0.61736,-0.61736 0.55952,-0.43881 0.572,-0.42633 0.13319,0.13319 -0.46086,0.58764 -0.75055,0.77452 -0.75055,0.48417 z m -34.153301,-2.00552 c 0.08489,-0.25466 0.362699,-0.53248 0.617361,-0.61736 0.264583,-0.0882 0.396875,0.0441 0.30868,0.30868 -0.08489,0.25466 -0.362699,0.53247 -0.617361,0.61736 -0.264583,0.0882 -0.396875,-0.0441 -0.30868,-0.30868 z m 17.751631,-1.38502 c -0.12359,-0.19998 0.018,-0.25912 0.33196,-0.13865 0.61115,0.23452 0.72297,0.48738 0.21553,0.48738 -0.18258,0 -0.42895,-0.15693 -0.54749,-0.34873 z m -11.3117,-7.85959 c -9.862208,-10.22093 -18.73087,-19.73903 -19.913945,-21.37222 -0.553395,-0.76394 -1.374576,-1.6406 -1.824846,-1.94815 -0.450271,-0.30754 -1.974173,-1.71346 -3.386449,-3.12426 l -2.567774,-2.5651 2.391385,-2.26635 c 1.315262,-1.2465 4.693261,-4.4499 7.506663,-7.11869 5.183182,-4.91674 9.689049,-8.78302 10.235977,-8.78302 0.164896,0 0.402227,-0.32268 0.527402,-0.71707 0.125175,-0.3944 1.032932,-1.46596 2.017237,-2.38125 0.984306,-0.9153 1.992352,-1.86247 2.240103,-2.10483 0.351118,-0.34347 5.605148,-0.43964 23.824977,-0.43608 12.85599,0.003 23.22591,0.13341 23.04427,0.29088 -0.73898,0.64068 -7.82517,5.66327 -8.9733,6.36016 -1.16094,0.70466 -1.70502,0.74952 -9.10485,0.75076 -5.82159,9.8e-4 -7.80514,-0.10382 -7.62048,-0.40261 0.17909,-0.28978 -0.0393,-0.32625 -0.77293,-0.12907 -0.56242,0.15118 -4.38574,0.37263 -8.496276,0.49213 l -7.473702,0.21726 -2.560969,2.74844 c -1.408534,1.51165 -2.614458,3.09893 -2.679833,3.52731 -0.147228,0.96473 -1.74391,3.42564 -2.222612,3.42564 -0.193709,0 0.05813,-0.65 0.559638,-1.44444 0.501509,-0.79445 0.83734,-1.51894 0.74629,-1.60999 -0.183623,-0.18362 -9.512457,9.23016 -9.712773,9.80122 -0.186613,0.532 1.363089,2.0496 1.887466,1.84838 0.248401,-0.0953 0.787318,0.29811 1.197594,0.87429 0.449497,0.63126 0.991139,0.9994 1.362891,0.92633 1.982122,-0.38962 3.399318,-0.46447 2.854606,-0.15077 -0.361893,0.20842 -0.514189,0.60572 -0.399827,1.04304 0.128541,0.49154 0.01126,0.70638 -0.385587,0.70638 -0.97447,0 -0.65239,0.6826 1.909127,4.04607 1.363691,1.79063 2.545158,3.59695 2.625482,4.01404 0.235365,1.22216 1.124819,2.28471 2.709081,3.23631 1.561188,0.93773 2.135262,1.75636 1.231675,1.75636 -0.730106,0 2.005005,5.49214 3.000411,6.02486 1.099155,0.58825 1.104829,0.21463 0.01966,-1.29422 -1.939924,-2.69732 -1.49716,-2.38485 2.629693,1.85586 4.079635,4.19219 4.221845,4.29905 6.030255,4.53102 2.10085,0.26949 2.39881,0.68715 0.70555,0.98899 -2.33403,0.41607 -9.619779,1.63744 -9.809888,1.64451 -0.108185,0.004 -1.61631,-1.46393 -3.351389,-3.26212 z m 3.024325,-13.16413 c 0.003,-1.148 0.169023,-2.32539 0.369006,-2.61644 0.256045,-0.37263 0.293775,0.20133 0.127546,1.94028 -0.296766,3.10453 -0.503566,3.38613 -0.496552,0.67616 z m 30.009305,0.46562 c -0.8321,-1.93111 -0.57352,-2.15863 0.71137,-0.62594 1.17508,1.40171 1.23034,1.62252 0.47827,1.91112 -0.39335,0.15094 -0.7254,-0.20778 -1.18964,-1.28518 z m -52.768873,-0.44896 c -0.582084,-0.26653 -0.820209,-0.48614 -0.529167,-0.48802 0.291042,-0.002 0.926042,0.22347 1.411111,0.50076 1.115344,0.63759 0.519523,0.62898 -0.881944,-0.0127 z m 33.344573,0.0213 c -0.27825,-0.33527 -0.30501,-0.53769 -0.0711,-0.53769 0.42635,0 0.92343,0.66929 0.67878,0.91394 -0.0888,0.0888 -0.36225,-0.0805 -0.60769,-0.37625 z m 11.05972,-8.44183 c 0.11878,-0.98849 0.21734,-2.15444 0.21903,-2.591 0.008,-2.04011 1.41199,0.0112 1.63249,2.38493 0.15349,1.65241 0.13948,1.67939 -0.95586,1.84016 l -1.11163,0.16315 z M 94.484875,130.4185 c -2.000959,-2.17803 -3.452495,-4.02192 -3.225636,-4.09754 0.463117,-0.15438 5.298095,0.54362 5.76515,0.83228 0.165488,0.10227 0.300887,0.58181 0.300887,1.06563 0,0.48383 0.253394,1.99377 0.563098,3.35544 0.309704,1.36166 0.489188,2.54966 0.398854,2.64 -0.09033,0.0903 -1.801394,-1.61778 -3.802353,-3.79581 z m -0.329197,-6.7161 c 0.003,-0.25398 0.15929,-0.69992 0.347376,-0.99096 0.278019,-0.4302 0.342984,-0.38461 0.347375,0.24377 0.003,0.42511 -0.153348,0.87104 -0.347375,0.99096 -0.194028,0.11991 -0.350347,0.0102 -0.347376,-0.24377 z m -0.35818,-6.65622 c 0,-0.20548 0.15875,-0.27549 0.352778,-0.15557 0.194028,0.11992 0.352778,0.28803 0.352778,0.3736 0,0.0856 -0.15875,0.15557 -0.352778,0.15557 -0.194028,0 -0.352778,-0.16812 -0.352778,-0.3736 z M 74.91994,113.9123 c 0.768741,-0.86696 1.916271,-1.577 2.158,-1.33527 0.08434,0.0843 -0.524569,0.59731 -1.353131,1.13994 -1.299608,0.85111 -1.410132,0.87793 -0.804869,0.19533 z m 2.792651,-2.79259 c 0.718992,-0.80832 0.915463,-0.91296 0.915463,-0.48761 0,0.14735 -0.357187,0.48734 -0.79375,0.75553 -0.790931,0.48587 -0.791363,0.48492 -0.121713,-0.26792 z m 10.793241,-6.75275 c 0,-0.40912 3.240395,-0.41815 5.820833,-0.0162 1.485202,0.23133 1.164712,0.28218 -2.028472,0.32187 -2.32448,0.0289 -3.792361,-0.0894 -3.792361,-0.30564 z m 8.8857,-0.88941 c 0.205348,-0.20534 5.808668,-1.23208 6.636518,-1.21605 1.33803,0.0259 -0.70381,0.65656 -3.29059,1.01634 -3.108287,0.43233 -3.608391,0.46218 -3.345928,0.19971 z"
           id="path4" /></g></svg>

        <a href="https://t.me/syntxaibot">Syntx.Ai Telegram Bot</a>
    </section>

    <textarea id="temp-textarea" style="position:absolute;left:-9999px;"></textarea>
</template>

<style scoped>
body {
    font-family: ui-sans-serif, -apple-system, system-ui, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, Helvetica, Apple Color Emoji, Arial, Segoe UI Emoji, Segoe UI Symbol;
    padding-top: 35px;
}
h1 {
    font-size: 24px !important;
}

h2 {
    font-size: 22px !important;
}

h3 {
    font-size: 20px !important;
}

h4 {
    font-size: 18px !important;
}

h5 {
    font-size: 16px !important;
}

h6 {
    font-size: 14px !important;
}

pre {
    position: relative;
    white-space: pre-wrap; /* Обрезка длинных строк на малых экранах */
}
.copy-button {
    position: absolute;
    top: 4px;
    right: 118px;
    padding: 3px 5px;
    cursor: pointer;
    color: var(--tg-theme-button-text-color, #ffffff);
    background: none;
    border: none;
    margin: 0;
}
.copy-button:focus {
    outline: none;
}
.toggle-button {
    position: absolute;
    top: 4px;
    right: 10px;
    padding: 3px 5px;
    cursor: pointer;
    color: var(--tg-theme-button-text-color, #ffffff);
    background: none;
    border: none;
    margin: 0;
}
.toggle-button:focus {
    outline: none;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    margin-bottom: 1em;
    color: var(--tg-theme-text-color, #222) !important;
}

th, td {
    border: 1px solid var(--tg-theme-secondary-bg-color, #393939) !important;
    padding: 8px;
    text-align: left;
    color: var(--tg-theme-text-color, #999) !important;
}

th {
    background-color: var(--tg-theme-secondary-bg-color, #393939);
}

caption {
    caption-side: bottom;
    padding: 10px;
    font-weight: bold;
}

button {
    width: unset !important;
}

#clipboard_success {
    display: none;
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 90%;
    background-color: green;
    color: white;
    padding: 10px;
    border-radius: 5px;
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.5s;
}
#clipboard_success.show {
    display: block;
    opacity: 1;
}
img {
    max-width: 30%;
    margin: 0 auto;
}

blockquote {
    border-left: 5px solid var(--tg-theme-secondary-bg-color, #393939);
    margin: 25px 10px;
    padding: 10px;
    quotes: "\201C""\201D""\2018""\2019";
    font-size: 20px !important;
}
blockquote:before {
    content: open-quote;
    font-size: 50px;
    line-height: 0.1em;
    margin-right: 0.25em;
    vertical-align: -0.4em;
}
blockquote p {
    display: inline;
}
blockquote blockquote {
    background: var(--tg-theme-secondary-bg-color, #393939);
    border-left: 10px solid #aaa;
    margin: 10px;
    padding: 10px;
}
blockquote blockquote:before {
    color: var(--tg-theme-bg-color, #000);
    font-size: 30px;
}
pre[class*=language-] {
    margin: 0 0 10px 0;
    border-radius: 0 0 10px 10px !important;
    margin-top: -10px;
}
:not(pre)>code[class*=language-], pre[class*=language-] {
    background: #393939;
    border: 1px solid #2f2f2f;
}
.header {
    background: var(--tg-theme-secondary-bg-color, #393939);
    padding: 10px;
    text-align: center;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1;
}
.header a {
    text-decoration: none;
    color: var(--tg-theme-text-color, #ccc);
    margin: 0 5px;
    opacity: 0.6;
    font-size: 11px;
}
.header a:hover {
    text-decoration: none;
}
.header .bi {
    margin-right: 5px;
}
#error_message {
    display: none;
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: green;
    color: white;
    padding: 10px;
    border-radius: 5px;
    z-index: 1000;
    white-space: nowrap; /* Prevents text from wrapping */
}
.footer {
    padding: 10px;
    margin-top: 10px;
    border-top: 1px solid var(--tg-theme-secondary-bg-color, #393939);
    font-size: 11px;
}
mjx-container[jax="CHTML"][display="true"] {
    font-size: 16px !important; /* Задайте нужный размер шрифта */
    font-family: ui-sans-serif, -apple-system, system-ui, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, Helvetica, Apple Color Emoji, Arial, Segoe UI Emoji, Segoe UI Symbol;
    background: var(--tg-theme-secondary-bg-color, #393939);
    padding: 10px;
    border-radius: 8px;
    display: inline-block;
    text-align: left !important;
}
mjx-container[jax="CHTML"][display="true"] mjx-math {
}
.message {
    display: flex;
    margin: 10px 0;
}
.message.userquestion {
    justify-content: flex-end;
}
.message.answer {
    justify-content: flex-start;
}
.message .content {
    max-width: 90%;
    padding: 0 10px;
    border-radius: 8px;
}
.message.userquestion .content {
    text-align: right;
    max-width: 75%;
    background: var(--tg-theme-secondary-bg-color, #393939);
    padding-top: 0;
}
.message.answer .content {
    text-align: left;
}

.message.answer .logo {
    width: 25px; /* Задаем фиксированную ширину 100px */
    margin-right: 5px; /* Отступ справа от логотипа */
    flex-shrink: 0; /* Предотвращаем сжатие контейнера логотипа */
    display: flex;
    align-items: flex-start; /* Выравниваем логотип по верхнему краю */
    justify-content: center;
}

.message.answer .logo img {
    max-width: 100%; /* Максимальная ширина изображения 100% */
    max-height: 100%; /* Максимальная высота изображения 100% */
    border-radius: 50%; /* Делаем изображение кругом */
    object-fit: cover; /* Обеспечиваем, что изображение полностью занимает контейнер, сохраняя пропорции */
}

.userquestion p {
    margin-bottom: 0;
}

.file_user {
    padding: 10px 0;
    display: flex;
    align-items: center;
}
.file_user i {
    padding-right: 10px;
    font-size: 25px;
}

dl, ol, ul {
    padding-left: 20px;
}

#copyAllButton {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    padding: 5px 8px;
    background-color: var(--tg-theme-bg-color, #50a8eb);
    border: 1px solid var(--tg-theme-secondary-bg-color, #ffffff);
    color: var(--tg-theme-text-color, #ccc);
    border-radius: 5px;
    cursor: pointer;
}

.copy-message-button {
    padding: 5px 10px;
    background-color: var(--tg-theme-secondary-bg-color, #393939);
    color: var(--tg-theme-text-color, #ffffff);
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
    margin-top: 5px;
}

#temp-textarea {
    position: fixed;
    top: 0;
    left: 0;
    opacity: 0;
    pointer-events: none;
}

.container {
    margin: 10px 0;
    width: 100%;
    box-sizing: border-box;
    margin-top: 35px;
}
.container.footer {
    margin-top: 0;
    -webkit-border-radius: 0;
    -moz-border-radius: 0;
    border-radius: 0 !important;
}
.markdown-content {
    width: 100%;
}
</style>